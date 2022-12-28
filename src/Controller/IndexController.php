<?php

namespace App\Controller;

use App\Document\Session;
use App\Form\SaveGameType;
use App\Form\SessionType;
use App\Service\FileUploader;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @throws MongoDBException
     */
    #[Route('/session', name: 'app_index_session')]
    public function session(Request $request): Response
    {
        $uuid = ['uuid' => null];
        $form = $this->createForm(SessionType::class, $uuid);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $generatedUUID = $form->get('uuid')->getData();
            return $this->redirectToRoute('app_index_session_new', ['uuid' => $generatedUUID]);
        }
        return $this->render('index/session.html.twig', [
            'controller_name' => 'IndexController',
            'form' => $form
        ]);
    }
    #[Route('/session/{uuid}', name: 'app_index_session_new', defaults: ['uuid' => ''])]
    public function newSession(Request $request, DocumentManager $dm,  FileUploader $fileUploader, string $uuid) : Response {
        $file = ['savegame' => null];
        $form = $this->createForm(SaveGameType::class, [$file, $uuid]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $saveFile = $form->get('savegame')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($saveFile) {
                $filename = $fileUploader->upload($saveFile);
                $filepath = $this->getParameter('savegame_directory') . '/' . $filename;
                $program = $this->getParameter('savereader_directory').'/PokemonSaveRead.exe';
                $process = new Process([$program, $filepath]);
                $process->run();
                if(!$process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }
                //echo $process->getOutput(); Gets the Commandline Output
            }
            $sessionName = $form->get('session_name')->getData();
            $newSession = new Session();
            $newSession->setUuid($uuid);
            $newSession->setName($sessionName);
            $newSession->setCreatedAt(new \DateTimeImmutable());
            $dm->persist($newSession);
            $dm->flush();
        }
        return $this->render('index/session_detail.html.twig', ['controller_name' => 'IndexController', 'uuid' => $uuid, 'form' => $form]);
    }

    #[Route('/', name: 'app')]
    public function index(): Response
    {
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
}

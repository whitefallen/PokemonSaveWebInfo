<?php

namespace App\Controller;

use App\Document\Pokemon;
use App\Document\SaveState;
use App\Document\Session;
use App\Form\SaveGameType;
use App\Form\SessionType;
use App\Service\FileUploader;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use JsonException;
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

    /**
     * @throws MongoDBException
     * @throws JsonException
     */
    #[Route('/session/{uuid}', name: 'app_index_session_new', defaults: ['uuid' => ''])]
    public function newSession(Request $request, DocumentManager $dm,  FileUploader $fileUploader, string $uuid) : Response {
        $file = ['savegame' => null];
        $form = $this->createForm(SaveGameType::class, [$file, $uuid]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $saveFile = $form->get('savegame')->getData();
            $sessionName = $form->get('session_name')->getData();
            $playerName = $form->get('player_name')->getData();
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
                $fileData = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
                $newSaveState = new SaveState();
                $newSaveState->setUploaderName($playerName);
                $newSaveState->setUploadedAt(new \DateTimeImmutable());
                $newSaveState->setPlaytime($fileData['PlayedTime']);
                $newSaveState->setFileIdentifier($filename);
                $newSaveState->setTrainerName($fileData['TrainerName']);
                foreach($fileData['Party'] as $Pokemon) {
                    $newPoke = new Pokemon();
                    $newPoke->setSpeciesId($Pokemon['SpeciesId']);
                    $newPoke->setNickname($Pokemon['Nickname']);
                    $newPoke->setLevel($Pokemon['Level']);
                    $newSaveState->getParty()->add($newPoke);
                }
            }
            $newSession = new Session();
            $newSession->setUuid($uuid);
            $newSession->setName($sessionName);
            $newSession->setCreatedAt(new \DateTimeImmutable());
            $newSession->getTimeline()->add($newSaveState);
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

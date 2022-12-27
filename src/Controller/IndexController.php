<?php

namespace App\Controller;

use App\Document\Session;
use App\Form\SessionType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function newSession(Request $request, DocumentManager $dm, string $uuid) : Response {
        //if ($form->isSubmitted() && $form->isValid()) {
        $newSession = new Session();
        $newSession->setUuid($uuid);
        $newSession->setName("Test Session");
        $newSession->setCreatedAt(new \DateTimeImmutable());
        $dm->persist($newSession);
        $dm->flush();
        return $this->render('index/session_detail.html.twig', ['controller_name' => 'IndexController', 'uuid' => $uuid]);
    }

    #[Route('/', name: 'app')]
    public function index(): Response
    {
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
}

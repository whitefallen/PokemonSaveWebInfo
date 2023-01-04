<?php

namespace App\Controller;

use App\Document\Pokemon;
use App\Document\SaveState;
use App\Document\Session;
use App\Form\SaveGameType;
use App\Form\SessionType;
use App\Service\FileUploader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

class SessionController extends AbstractController
{
    private DocumentManager $dm;
    private DocumentRepository $sessionRepository;
    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
        $this->sessionRepository = $dm->getRepository(Session::class);
    }

    /**
     * @throws MongoDBException
     */
    #[Route('/session', name: 'app_index_session')]
    public function session(Request $request): Response
    {
        $uuid = ['uuid' => null];
        $form = $this->createForm(SessionType::class, $uuid);
        $allSessions = $this->sessionRepository->findAll();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $generatedUUID = $form->get('uuid')->getData();
            $sessionName = $form->get('session_name')->getData();
            if($this->sessionRepository->findOneBy(['uuid' => $generatedUUID]) === null) {
                $session = new Session();
                $session->setUuid($generatedUUID);
                $session->setName($sessionName);
                $session->setCreatedAt(new \DateTimeImmutable());
                $this->dm->persist($session);
                $this->dm->flush();
            }
            return $this->redirectToRoute('app_index_session_detail', ['uuid' => $generatedUUID]);
        }
        return $this->render('session/session.html.twig', [
            'controller_name' => 'SessionController',
            'form' => $form,
            'sessions' => $allSessions,
        ]);
    }

    /**
     * @throws MongoDBException
     * @throws JsonException
     */
    #[Route('/session/{uuid}', name: 'app_index_session_detail', defaults: ['uuid' => ''])]
    public function newSession(Request $request, FileUploader $fileUploader, string $uuid) : Response {
        $file = ['savegame' => null];
        $form = $this->createForm(SaveGameType::class, [$file, $uuid]);
        $form->handleRequest($request);
        $session = $this->sessionRepository->findOneBy(['uuid' => $uuid]);
        if ($form->isSubmitted() && $form->isValid()) {
            $saveFile = $form->get('savegame')->getData();
            $playerName = $form->get('player_name')->getData();
            /* @var $sessionRepository DocumentRepository */
            if ($saveFile) {
                $fileName = $fileUploader->upload($saveFile);
                $fileData = $this->extractDataFromSaveFile($fileName);
                $session?->getTimeline()->add($this->createSaveStateFromFile($fileData, $fileName, $playerName));
            }
            $this->dm->persist($session);
            $this->dm->flush();
        }
        return $this->render('session/session_detail.html.twig', [
            'controller_name' => 'SessionController',
            'uuid' => $uuid,
            'form' => $form,
            'session' => $session
        ]);
    }

    #[Route('/session/delete/{uuid}', name: 'app_session_delete_session')]
    public function deleteSession(Request $request, string $uuid) : Response {
        $session = $this->sessionRepository->findOneBy(['uuid'=> $uuid]);
        try {
            $this->deleteHelper($session);
        } catch (MongoDBException $e) {
        }
        return $this->redirectToRoute('app_index_session');
    }

    /**
     * @throws MongoDBException
     */
    #[Route('/session/delete/timeslot/{identifier}', name: 'app_session_delete_timeslot')]
    public function deleteTimeslot(Request $request, string $identifier) : Response {
        $session = $this->sessionRepository->findOneBy(['timeline.fileIdentifier' => $identifier]);
        $timeline = $session->getTimeline()->toArray();
        $session->setTimeline(new ArrayCollection(array_filter($timeline, static fn(SaveState $timeslot) => ($timeslot->getFileIdentifier() !== $identifier))));
        $this->dm->flush();
        return $this->redirectToRoute('app_index_session_detail', ['uuid' => $session->getUuid()]);
    }

    /**
     * @throws MongoDBException
     */
    private function deleteHelper($session) : void {
        if($session !== null) {
            $this->dm->remove($session);
            $this->dm->flush();
        }
    }

    #[Route('/', name: 'app')]
    public function index(): Response
    {
        /*
        return $this->render('session/index.html.twig', [
            'controller_name' => 'SessionController',
        ]);
        */
        return $this->redirectToRoute('app_index_session');
    }

    private function createPokemonFromFile($fileData): Pokemon
    {
        $newPoke = new Pokemon();
        $newPoke->setSpeciesId($fileData['SpeciesId']);
        $newPoke->setNickname($fileData['Nickname']);
        $newPoke->setLevel($fileData['Level']);
        return $newPoke;
    }

    private function createSaveStateFromFile($fileData,$fileName,$playerName): SaveState
    {
        $newSaveState = new SaveState();
        $newSaveState->setUploaderName($playerName);
        $newSaveState->setUploadedAt(new \DateTimeImmutable());
        $newSaveState->setPlaytime($fileData['PlayedTime']);
        $newSaveState->setFileIdentifier($fileName);
        $newSaveState->setTrainerName($fileData['TrainerName']);
        foreach($fileData['Party'] as $Pokemon) {
            $newSaveState->getParty()->add($this->createPokemonFromFile($Pokemon));
        }
        return $newSaveState;
    }

    /**
     * @throws JsonException
     */
    private function extractDataFromSaveFile($fileName) {
        $filePath = $this->getParameter('savegame_directory') . '/' . $fileName;
        $program = $this->getParameter('savereader_directory').'/PokemonSaveRead.exe';
        $process = new Process([$program, $filePath]);
        $process->run();
        if(!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
    }

}

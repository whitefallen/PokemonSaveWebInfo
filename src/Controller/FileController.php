<?php

namespace App\Controller;

use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\SaveGameType;

class FileController extends AbstractController
{
    #[Route('/file', name: 'app_file')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/FileController.php',
        ]);
    }

    #[Route('/file/upload', name: 'app_file_upload')]
    public function new(Request $request, FileUploader $fileUploader)
    {
        $file = ['savegame' => null];
        $form = $this->createForm(SaveGameType::class, $file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $saveFile */
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

            return $this->redirectToRoute('app_file');
        }

        return $this->render('file/new.html.twig', [
            'form' => $form,
        ]);
    }
}

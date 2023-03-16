<?php

namespace UploadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use UploadBundle\Document\DocumentFile;
use UploadBundle\Form\Type\DocumentFileType;


#[AsController]
class UploadFileController extends AbstractController
{
    /**
     * @param Request          $request
     * @param SluggerInterface $slugger
     *
     * @return Response
     */
    #[Route(
        '/api/upload-bundle/1.0.0/file',
        name: 'upload_bundle_file_post',
        methods: ['POST']
    )]
    public function uploadFile(Request $request, SluggerInterface $slugger): Response
    {
        $documentFile = new DocumentFile();
        $form = $this->createForm(DocumentFileType::class, $documentFile);
        //$form->handleRequest($request);
        $form->submit([$request->files]);dump($request, $form);dump($request->files);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('file')->getData();dump($form->getData(), $brochureFile);

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename. '-' . uniqid('', true) . '.' . $brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('files_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $documentFile->setBrochureFilename($newFilename);

                return $this->json([], Response::HTTP_CREATED);
            }

            // ... persist the $product variable or any other work

            return $this->json([], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([], Response::HTTP_BAD_REQUEST);
    }
}

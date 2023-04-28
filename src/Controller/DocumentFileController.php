<?php

namespace UploadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use UploadBundle\Document\DocumentFile;
use UploadBundle\Form\Type\DocumentFileType;
use UploadBundle\Manager\AntivirusManager;
use UploadBundle\Manager\SafeDepositBoxManager;
use UploadBundle\Manager\UploadManager;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use UploadBundle\Repository\Document\DocumentFileRepository;

#[AsController]
class DocumentFileController extends AbstractController
{
    public function __construct(
        private UploadManager $uploadManager,
        private DocumentFileRepository $repository,
        private AntivirusManager $antivirusManager,
        private SafeDepositBoxManager $safeDepositBoxManager
    ) {}

    #[Route(
        '/upload/document_files',
        name: '_upload_/document_files_post',
        methods: ['POST'],
        defaults: [
            '_api_resource_class' => DocumentFile::class,
            '_api_operation_name' => '_upload_/document_files_post',
        ]
    )]
    public function Post(Request $request, SluggerInterface $slugger): Response
    {
        //check temporary folder
        if ($response = $this->uploadManager->checkTemporaryDirectory($this->getParameter('upload.temporary_folder'))) {
            return $response;
        }

        $data = \json_decode($request->getContent(), true);
        if ($request->getContentTypeFormat() === 'form') {
            $data = $request->request->all();
        }

        //check the fileName parameter in the request data
        if ($response = $this->uploadManager->checkFileName($data)) {
            return $response;
        }

        //check ticket
        if (!isset($data['ticketId'])) {
            $error = new HttpException(
                Response::HTTP_BAD_REQUEST,
                "The ticketId must be set, please check your request datas."
            );

            return $this->json([
                'code' => $error->getCode(),
                'message' => $error->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        //check file in temporary folder
        if ($content = $request->get('fileContent')) {
            // from scanner
            $content = base64_decode($content);
            $fhandle = fopen(sprintf(
                '%s/%s',
                $this->getParameter('upload.temporary_folder'),
                $data['fileName']
            ), 'w');
            fwrite($fhandle, $content);
            fclose($fhandle);
            /**var File $file*/
            $file = new File(
                sprintf('%s/%s', $this->getParameter('upload.temporary_folder'), $data['fileName'])
            );
        } elseif ($request->files->count()) {
            // from drag&drop
            /**@var File $file*/
            $file = $request->files->get('file');

        } else {
            $error = new BadRequestException('Empty data file found.');

            return $this->json([
                'code' => $error->getCode(),
                'message' => $error->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
        if (isset($data['fileExtension'])) {
            $temporaryFolder = sprintf('%s/%s', $this->getParameter('upload.temporary_folder'), $data['fileExtension']);
            if (!file_exists($temporaryFolder)) {
                if (!mkdir($temporaryFolder, 0775, true) && !is_dir($temporaryFolder)) {
                    $error = new \RuntimeException(sprintf('Directory "%s" was not created', $temporaryFolder));

                    return $this->json([
                        'code' => $error->getCode(),
                        'message' => $error->getMessage(),
                    ], Response::HTTP_BAD_REQUEST);
                }
            }
        }
        $file->move(sprintf(
            '%s/%s',
            $this->getParameter('upload.temporary_folder'),
            $data['fileExtension']
        ), $data['fileName']);

        $file = $this->uploadManager->checkFile($data['fileName'], $this->getParameter('upload.temporary_folder'));
        if (!$file instanceof UploadedFile) {
            return $file;
        }

        //scan antivirus
        if (($scanVirus = $this->antivirusManager->isFileSafe($file, true)) !== true) {
            return $scanVirus;
        }
dump($scanVirus);
        //Check pdf and covert to pdf if needed
        $isPdf = false;
        if ($scanVirus) {
            $isPdf = true;
        }

        if (!isset($data['esc'])) {
            $error = new HttpException("The safe-deposit box must be set, please check your request datas.");

            return $this->json([
                'code' => $error->getCode(),
                'message' => $error->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        //Send document to the safe-deposit box ESC
        if ($isPdf) {
            $this->safeDepositBoxManager->setUrlEsc($data['esc']);
            $this->safeDepositBoxManager->setCertificate($data['esc']['certificate']);
            $escResponse = $this->safeDepositBoxManager->sendDocument($data);
        }

        // this condition is needed because the 'brochure' field is not required
        // so the PDF file must be processed only when a file is uploaded
        if ($file) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = sprintf(
                '%s-%s.%s',
                $safeFilename,
                uniqid('', true),
                $file->guessExtension()
            );

            $data = $this->uploadManager->populateMedata($data, $file);

            // Move the file to the directory where brochures are stored
            try {
                $file->move(
                    $this->getParameter('files_directory'),
                    $newFilename
                );
                $data['file']['path'] = sprintf(
                    '%s/%s',
                    $this->getParameter('files_directory'),
                    $newFilename
                );
                $fileMoved = new UploadedFile(
                    sprintf('%s/%s', $this->getParameter('files_directory'), $newFilename),
                    $newFilename,
                    null,
                    null,
                    true
                );
            } catch (FileException $e) {
                return $this->json([
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return $this->json($this->repository->saveDocumentFile($fileMoved, $data), Response::HTTP_CREATED);
        }

        return $this->json([
            'code' => Response::HTTP_BAD_REQUEST,
            'message' => 'Your file is not uploaded.',
        ], Response::HTTP_BAD_REQUEST);
    }
}

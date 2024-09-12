<?php

namespace UploadBundle\Controller;

use Doctrine\Bundle\MongoDBBundle\ArgumentResolver\DocumentValueResolver;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use UploadBundle\Document\DocumentFile;
use UploadBundle\Manager\AntivirusManager;
use UploadBundle\Manager\SafeDepositBoxManager;
use UploadBundle\Manager\UploadManager;
use UploadBundle\Repository\Document\DocumentFileRepository;
use Uploadbundle\Trait\UploadTrait;

#[AsController]
class DocumentFileController extends AbstractController
{
    use \UploadBundle\Trait\UploadTrait;

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
    public function Post(Request $request, SluggerInterface $slugger): JsonResponse
    {
        //check temporary folder
        if ($response = $this->uploadManager->checkTemporaryDirectory($this->getParameter('upload.temporary_folder'))) {
            return $this->json($response, Response::HTTP_OK);
        }

        $data = \json_decode($request->getContent(), true);
        if ($request->getContentTypeFormat() === 'form') {
            $data = $request->request->all();
            foreach ($data as $k => $v) {
                if (($k == 'paperless' || $k == 'ticket' || $k == 'esc') && is_string($v)) {
                    $data[$k] = is_string($v) ? $this->jsonMinify($v) : $v;
                    $data[$k] = \json_decode($data[$k], true);
                }
            }
        }

        //check the fileName parameter in the request data
        if ($response = $this->uploadManager->checkFileName($data)) {
            return $this->json($response, Response::HTTP_OK);
        }

        //check ticket
        if (!isset($data['ticketId'])) {
            if (!isset($data['ticket']['id'])) {
                $error = new HttpException(
                    Response::HTTP_BAD_REQUEST,
                    "The ticketId must be set, please check your request datas."
                );

                return $this->json([
                    'code' => $error->getCode(),
                    'message' => $error->getMessage(),
                ], Response::HTTP_OK);
            }

            $data['ticketId'] = $data['ticket']['id'];
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
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => $error->getMessage(),
            ], Response::HTTP_OK);
        }

        if (!isset($data['fileExtension']) || empty($data['fileExtension'])) {
            $tabFileName = explode('.', $data['fileName']);
            $data['fileExtension'] = $tabFileName[count($tabFileName) - 1];
        }

        if (isset($data['fileExtension'])) {
            $temporaryFolder = sprintf('%s/%s', $this->getParameter('upload.temporary_folder'), $data['fileExtension']);
            if (!file_exists($temporaryFolder)) {
                if (!mkdir($temporaryFolder, 0775, true) && !is_dir($temporaryFolder)) {
                    $error = new \RuntimeException(sprintf('Directory "%s" was not created', $temporaryFolder));

                    return $this->json([
                        'code' => Response::HTTP_BAD_REQUEST,
                        'message' => $error->getMessage(),
                    ], Response::HTTP_OK);
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
            return $this->json($file, Response::HTTP_OK);
        }

        $pdfToolsCkeck = true;

        //Check pdf and covert to pdf if needed
        $isPdf = false;
        if ($pdfToolsCkeck) {
            $isPdf = true;
        }

        if (!isset($data['esc'])) {
            $error = new HttpException(
                Response::HTTP_BAD_REQUEST,
                "The safe-deposit box must be set, please check your request datas."
            );

            return $this->json([
                'code' => $error->getCode(),
                'message' => $error->getMessage(),
            ], Response::HTTP_OK);
        }

        //Send document to the safe-deposit box ESC
        if ($isPdf) {
            $this->safeDepositBoxManager->setUrlEsc($data['esc']);
            $this->safeDepositBoxManager->setCertificate($data['esc']['certificate']);
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
                //scan antivirus
                if (($scanVirus = $this->antivirusManager->isFileSafe($fileMoved, true)) !== true) {
                    return $this->json($scanVirus, Response::HTTP_OK);
                }
                $escResponse = $this->safeDepositBoxManager->sendDocument($data, $fileMoved);
                $associatedDocument = $this->repository->saveDocumentFile($fileMoved, $escResponse);
                $escResponse['associatedDocuments'] = str_replace(
                    '000000000000000', 
                    $associatedDocument->getId(), 
                    $escResponse['associatedDocuments']
                );
            } catch (FileException $e) {
                return $this->json([
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            if (isset($escResponse['code'])) {
                return $this->json($escResponse, Response::HTTP_OK);
            }

            return $this->json($escResponse, Response::HTTP_CREATED);
        }

        return $this->json([
            'code' => Response::HTTP_BAD_REQUEST,
            'message' => 'Your file is not uploaded.',
        ], Response::HTTP_BAD_REQUEST);
    }

    #[Route(
        '/upload/document_files/{id}',
        name: '_upload_/document_files/{id}_get',
        methods: ['GET'],
        defaults: [
            '_api_resource_class' => DocumentFile::class,
            '_api_operation_name' => '_upload_/document_files/{id}_get',
        ]
    )]
    public function Get(
        #[MapEntity(
            class: DocumentFile::class,
            resolver: DocumentValueResolver::class, id: 'id'
        )] DocumentFile $documentFile
    ) {
        return $this->json($documentFile, Response::HTTP_OK);
    }

    #[Route(
        '/upload/document_files/{id}/evidence',
        name: '_upload_/document_files/{id}/evidence_get',
        methods: ['GET'],
        defaults: [
            '_api_resource_class' => DocumentFile::class,
            '_api_operation_name' => '_upload_/document_files/{id}/evidence_get',
        ]
    )]
    public function GetEvidence(
        #[MapEntity(
            class: DocumentFile::class,
            resolver: DocumentValueResolver::class, id: 'id'
        )] DocumentFile $documentFile,
        Request $request
    ) {
        $headers = $request->headers->all();

        if (!array_key_exists("esc", $headers)) {
            $error = new BadRequestException("Missing 'esc' header");

            return $this->json([
                'code' => $error->getCode(),
                'message' => $error->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = array();
        $data["esc"] = json_decode($headers['esc'][0], true);
        $data["cc"] = $headers['cc'][0];
        $data["applicationCode"] = $headers['applicationcode'][0];

        $this->safeDepositBoxManager->setUrlEsc($data['esc']);
        $this->safeDepositBoxManager->setCertificate($data['esc']['certificate']);

        $data["archiveId"] = $documentFile->getArchiveId();
        $data["type"] = 'xml';
        $file = $this->safeDepositBoxManager->findDocument($data);
        if (is_array($file) && isset($file['code'])) {
            return $this->json($file, Response::HTTP_BAD_REQUEST);
        }

        return  new Response($file->getContent(), Response::HTTP_OK, [
            'Content-Type' => 'application/xml',
            'Content-Length' => $file->getHeaders()['content-length'][0],
        ]);
    }

    #[Route(
        '/upload/document_files',
        name: '_upload_/document_files_get_collection',
        methods: ['GET'],
        defaults: [
            '_api_resource_class' => DocumentFile::class,
            '_api_operation_name' => '_upload_/document_files_get_collection',
        ]
    )]
    public function GetCollection(Request $request)
    {
        $headers = $request->headers->all();

        if (!array_key_exists("esc", $headers)) {
            $error = new BadRequestException("Missing 'esc' header");

            return $this->json([
                'code' => $error->getCode(),
                'message' => $error->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = array();
        $data["esc"] = json_decode($headers['esc'][0], true);
        $data["cc"] = array_key_exists("cc", $headers) ? $headers['cc'][0] : $headers['customercode'][0];
        $data["applicationCode"] = $headers['applicationcode'][0];

        $this->safeDepositBoxManager->setUrlEsc($data['esc']);
        $this->safeDepositBoxManager->setCertificate($data['esc']['certificate']);

        $query = $request->query->all();
        $typeResponse = null;
        if (array_key_exists("type", $query)) {
            $typeResponse = $query["type"];
            unset($query["type"]);
        }

        $documentFiles = $this->repository->findBy($query);

        if (count($documentFiles) == 1 && ($typeResponse == "pdf" || $typeResponse == "xml")) {
            $data["archiveId"] = $documentFiles[0]->getArchiveId();
            $data["type"] = $typeResponse;

            $file = $this->safeDepositBoxManager->findDocument($data);
            if (is_array($file) && isset($file['code'])) {
                return $this->json($file, Response::HTTP_BAD_REQUEST);
            }

            return  new Response($file->getContent(), 200, array(
                // $response = new Response($visResponse->getBody()->getContents(), 200, array(
                "Content-Type" => $data["type"] == "pdf" ? "application/pdf" : "application/xml",
                'Content-Length' => $file->getHeaders()["content-length"][0]
            ));
        }

        return $this->json($documentFiles);
    }
}

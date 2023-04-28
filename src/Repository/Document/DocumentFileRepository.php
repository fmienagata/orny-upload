<?php

namespace UploadBundle\Repository\Document;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Symfony\Component\HttpFoundation\File\File;
use UploadBundle\Document\DocumentFile;
use UploadBundle\Document\DocumentMetaData;

#[\Attribute]
class DocumentFileRepository extends DocumentRepository
{
    private DocumentFile $documentFile;

    /**
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        $classMetadata = $dm->getClassMetadata(DocumentFile::class);

        parent::__construct($dm, $uow, $classMetadata);
    }

    /**
     * @param File  $file
     * @param array $data
     *
     * @return DocumentFile
     */
    public function saveDocumentFile(File $file, array $data = []) :  DocumentFile
    {
        $this->documentFile = new DocumentFile($file);
        $this->persistDocumentFile($data);

        return $this->documentFile;
    }

    /**
     * @param array $data
     *
     * @return void
     */
    private function persistDocumentFile(array $data = []) : void
    {
        if (isset($data['ticketId'])) {
            $this->documentFile->setTicket($data['ticketId']);
        }

        if (isset($data['metadata'])) {
            $this->documentFile->setChunkSize($data['metadata']['size']);
        }

        if (isset($data['file']['path'])) {
            $this->documentFile->getFile()->setFilePath($data['file']['path']);
        }

        $this->documentFile->setMetadata(new DocumentMetaData('application/pdf'));

        $this->dm->persist($this->documentFile);
        $this->dm->flush();
    }
}

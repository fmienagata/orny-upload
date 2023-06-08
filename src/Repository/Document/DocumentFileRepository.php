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
        $documentFile = new DocumentFile($file);
        if (isset($data['ticketId'])) {
            $documentFile->setTicket($data['ticketId']);
        }

        if (isset($data['metadata'])) {
            $documentFile->setChunkSize($data['metadata']['size']);
        }

        if (isset($data['file']['path'])) {
            $documentFile->getMedia()->setFilePath($data['file']['path']);
        }

        $documentFile->setMetadata(new DocumentMetaData('application/pdf'));

        foreach ($data as $key => $value) {
            $method = sprintf('set%s', ucfirst($key));
            if (method_exists(DocumentFile::class, $method) &&
                $key != 'file' &&
                !is_array($value) &&
                $key != 'metadata' &&
                $key != 'paperless' &&
                $key != 'esc'
            ) {
                $documentFile->$method($value);
            }

            if (is_array($value) && $key != 'file' && $key != 'metadata' && $key != 'paperless' && $key != 'esc') {
                foreach ($value as $v) {
                    $documentFile->$method($v);
                }
            }
        }
        
        $this->dm->persist($documentFile);
        $this->dm->flush();

        return $documentFile;
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
            $this->documentFile->getMedia()->setFilePath($data['file']['path']);
        }

        $this->documentFile->setMetadata(new DocumentMetaData('application/pdf'));

        foreach ($data as $key => $value) {
            $method = sprintf('set%s', ucfirst($key));dump($method);
            if (method_exists(DocumentFile::class, $method) && $key != 'file') {
                $this->documentFile->$method($value);
            }
        }dump($this->documentFile);

        $this->dm->persist($this->documentFile);
        $this->dm->flush();
    }
}

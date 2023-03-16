<?php

namespace UploadBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use UploadBundle\Document\DocumentFile;

/**
 * Class DocumentFileModel
 *
 * @package UploadBundle\Form\Model
 */
class DocumentFileModel
{
    /**
     * @var DocumentFile $documentFile
     */
    protected $documentFile;

    /**
     * @return DocumentFile
     */
    public function getDocumentFile(): DocumentFile
    {
        return $this->documentFile;
    }

    /**
     * @param DocumentFile $documentFile
     *
     * @return $this
     */
    public function setDocumentFile(DocumentFile $documentFile) : self
    {
        $this->documentFile = $documentFile;

        return $this;
    }
}

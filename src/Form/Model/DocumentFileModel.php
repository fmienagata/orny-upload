<?php

namespace UploadBundle\Form\Model;

use UploadBundle\Document\DocumentFile;

/**
 * Class DocumentFileModel.
 */
class DocumentFileModel
{
    /**
     * @var DocumentFile
     */
    protected $documentFile;

    public function getDocumentFile(): DocumentFile
    {
        return $this->documentFile;
    }

    /**
     * @return $this
     */
    public function setDocumentFile(DocumentFile $documentFile): self
    {
        $this->documentFile = $documentFile;

        return $this;
    }
}

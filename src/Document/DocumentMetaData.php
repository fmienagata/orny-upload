<?php


namespace UploadBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations As MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;

#[MongoDB\EmbeddedDocument]
class DocumentMetaData
{
    #[MongoDB\Field("string")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private $contentType;

    /**
     * DocumentMetaData constructor.
     *
     * @param string $contentType
     */
    public function __construct(string $contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @return string|null
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }
}

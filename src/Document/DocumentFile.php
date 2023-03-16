<?php


namespace UploadBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations\File;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;

/**
 * @File(bucketName="document-file")
 */
class DocumentFile
{
    /** @Id */
    private $id;

    /** @File\Filename */
    private $name;

    /** @File\UploadDate */
    private $uploadDate;

    /** @File\Length */
    private $length;

    /** @File\ChunkSize */
    private $chunkSize;

    /** @File\Metadata(targetDocument=DocumentMetadata::class) */
    private $metadata;

    /**
     * DocumentFile constructor.
     *
     * @param \Symfony\Component\HttpFoundation\File\File|null $file
     */
    public function __construct(\Symfony\Component\HttpFoundation\File\File $file = null)
    {
        if (!empty($file)) {
            $this->setName($file->getFilename());
        }
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return $this
     */
    public function setName(?string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    public function getChunkSize(): ?int
    {
        return $this->chunkSize;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function getUploadDate(): \DateTimeInterface
    {
        return $this->uploadDate;
    }

    public function getMetadata(): ?DocumentMetadata
    {
        return $this->metadata;
    }
}
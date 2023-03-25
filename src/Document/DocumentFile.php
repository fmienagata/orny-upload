<?php


namespace UploadBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations\File;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\{ApiResource, ApiProperty};
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use ApiPlatform\Metadata\{GetCollection, Get, Patch, Post, Delete, Put};
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use UploadBundle\Controller\DocumentFileController;

/**
 * @File(bucketName="document-file")
 */
#[MongoDB\Document(
    collection: "document_files",
    repositoryClass: "UploadBundle\Repository\Document\DocumentFileRepository"
)]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/document_files',
            name: '_upload_/document_files_post',
            controller: DocumentFileController::class
        )
    ]
)]
class DocumentFile
{
    use TimestampableDocument;

    #[ApiProperty(identifier:true)]
    #[Groups(["document_files:read"])]
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type:"string")]
    #[MongoDB\File\Filename]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private string $originalName;

    #[MongoDB\Field(type:"string")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private string $ticket;

    #[MongoDB\Field(type:"date")]
    #[MongoDB\File\UploadDate]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private \DateTime $uploadDate;

    #[MongoDB\Field(type:"int")]
    #[MongoDB\File\Length]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private int $length;

    #[MongoDB\Field(type:"int")]
    #[MongoDB\File\ChunkSize]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private int $chunkSize;

    #[MongoDB\Field(type:"raw")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    #[File\Metadata(targetDocument: DocumentMetaData::class)]
    private $metadata;

    #[ApiProperty(types: ['https://schema.org/file'])]
    public ?MediaObject $file = null;

    #[Gedmo\Timestampable(on: "create")]
    #[MongoDB\Field(type: 'date')]
    #[Groups(["tickets:read"])]
    protected $createdAt;

    #[Gedmo\Timestampable(on: "update", field: "updatedAt")]
    #[MongoDB\Field(type: 'date')]
    #[Groups(["tickets:read"])]
    protected $updatedAt;

    /**
     * DocumentFile constructor.
     *
     * @param \Symfony\Component\HttpFoundation\File\File|null $file
     */
    public function __construct(\Symfony\Component\HttpFoundation\File\File $file = null)
    {
        if ($file) {
            $this->setOriginalName($file->getFilename());
        }
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    /**
     * @param string $originalName
     *
     * @return $this
     */
    public function setOriginalName(string $originalName): self
    {
        $this->originalName = $originalName;

        return $this;
    }

    /**
     * @return string
     */
    public function getTicket(): string
    {
        return $this->ticket;
    }

    /**
     * @param string $ticket
     *
     * @return $this
     */
    public function setTicket(string $ticket): self
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUploadDate(): \DateTime
    {
        return $this->uploadDate;
    }

    /**
     * @param \DateTime $uploadDate
     *
     * @return $this
     */
    public function setUploadDate(\DateTime $uploadDate): self
    {
        $this->uploadDate = $uploadDate;

        return $this;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param int $length
     *
     * @return $this
     */
    public function setLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return int
     */
    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * @param int $chunkSize
     *
     * @return $this
     */
    public function setChunkSize(int $chunkSize): self
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param $metadata
     *
     * @return $this
     */
    public function setMetadata($metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }
}

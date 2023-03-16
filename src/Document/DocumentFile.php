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

/**
 * @File(bucketName="document-file")
 */
#[MongoDB(
    collection: "document_files",
    repositoryClass: "UploadBundle\Repository\Document\DocumentFileRepository"
)]
#[ApiResource]
class DocumentFile
{
    use TimestampableDocument;

    #[ApiProperty(identifier:true)]
    #[Groups(["document_files:read"])]
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type:"string")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private File\Filename $originalName;

    #[MongoDB\Field(type:"string")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private string $ticket;

    #[MongoDB\Field(type:"date")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private File\UploadDate $uploadDate;

    #[MongoDB\Field(type:"int")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private File\Length $length;

    #[MongoDB\Field(type:"int")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private File\ChunkSize $chunkSize;

    #[MongoDB\Field(type:"raw")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    #[File\Metadata(targetDocument: DocumentMetadata::class)]
    private $metadata;

    #[ApiProperty(types: ['https://schema.org/file'])]
    public ?MediaObject $file = null;

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
}

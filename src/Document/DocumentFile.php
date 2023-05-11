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
use ApiPlatform\Metadata\{GetCollection, Get, Patch, Post, Delete, Put};
use ApiPlatform\OpenApi\Model;
use ApiPlatform\Metadata\Operation;
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
    normalizationContext: ['groups' => ['document_files:read']],
    denormalizationContext: ['groups' => ['document_files:write']],
    operations: [
        new GetCollection(),
        new Post(
            uriTemplate: '/document_files',
            name: '_upload_/document_files_post',
            routeName: '_upload_/document_files_post',
            controller: DocumentFileController::class,
            inputFormats: [
                'multipart' => ['multipart/form-data'],
                'json' => ['application/json'],
            ],
            outputFormats: [
                'json' => ['application/json'],
                'html' => [ 'text/html' ],
            ],
            deserialize: false,
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary'
                                    ],
                                    'fileName' => ['type' => 'string', 'example' => 'file.pdf'],
                                    'paperless' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'reference' => ['type' => 'string', 'example' => 'FDP'],
                                            'pages' => ['type' => 'string', 'example' => 5],
                                            'indexBatch' => ['type' => 'string', 'example' => ''],
                                            'batchProduction' => ['type' => 'string', 'example' => 0],
                                            'name' => ['type' => 'string', 'example' => 'BULLETIN DE SALAIRE'],
                                            'weight' => ['type' => 'string', 'example' => 176879],
                                            'archiveTime' => ['type' => 'string', 'example' => 30],
                                            'documentSource' => ['type' => 'string', 'example' => 'BVRH UPLOAD'],
                                            'status' => ['type' => 'string', 'example' => '1'],
                                        ]
                                    ],
                                    'ticket' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'id' => ['type' => 'string', 'example' => '644aa1b61b9a4b56e405acd4']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'paperless' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'associatedDocuments' => ['type' => 'string'],
                                            'location' => ['type' => 'string'],
                                            'reference' => ['type' => 'string'],
                                            'pages' => ['type' => 'string'],
                                            'indexBatch' => ['type' => 'string'],
                                            'batchProduction' => ['type' => 'string'],
                                            'name' => ['type' => 'string'],
                                            'weight' => ['type' => 'string'],
                                            'archiveTime' => ['type' => 'string'],
                                            'documentSource' => ['type' => 'string'],
                                            'status' => ['type' => 'string'],
                                        ]
                                    ],
                                    'ticket' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'attributes' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'name' => ['type' => 'string'],
                                                ]
                                            ],
                                        ]
                                    ]
                                ]
                            ],
                            'example' => [
                                'paperless' => [
                                    'associatedDocuments' => 'E000000000000411/1/5',
                                    'location' => 'S/E//16427c0aaf864e9f8519519d33786fff',
                                    'reference' => 'FDP',
                                    'pages' => 5,
                                    'indexBatch' => '',
                                    'batchProduction' => 0,
                                    'name' => 'BULLETIN DE SALAIRE',
                                    'weight' => 176879,
                                    'archiveTime' => 30,
                                    'documentSource' => 'BVRH UPLOAD',
                                    'status' => '1',
                                ],
                                'ticket' => [
                                    'attributes' => [
                                        'name' => 'PaySlip',
                                    ]
                                ]
                            ]
                        ]
                    ])
                ),
                responses: [new Model\Response(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'reference' => ['type' => 'string'],
                                    'pages' => ['type' => 'string'],
                                    'indexBatch' => ['type' => 'string'],
                                    'batchProduction' => ['type' => 'string'],
                                    'name' => ['type' => 'string'],
                                    'weight' => ['type' => 'string'],
                                    'archiveTime' => ['type' => 'string'],
                                    'description' => ['type' => 'string'],
                                    'documentSource' => ['type' => 'string'],
                                    'status' => ['type' => 'string'],
                                    'specialization' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'name' => ['type' => 'string'],
                                        ]
                                    ],
                                    'createdAt' => ['type' => 'date'],
                                    'updatedAt' => ['type' => 'date'],
                                ]
                            ],
                            'example' => [
                                'id' => '6424462c03b25a3d220809c6',
                                'associatedDocuments' => 'E000000000000411/1/5'
                            ]
                        ]
                    ])
                )]
            )
        ),
        new Get(
            uriTemplate: '/document_files/{id}',
            name: '_upload_/document_files/{id}_get',
            routeName: '_upload_/document_files/{id}_get',
            controller: DocumentFileController::class,
            normalizationContext: ['groups' => ['document_files:read']],
            denormalizationContext: ['groups' => ['document_files:write']]
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
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private string $originalName;

    #[MongoDB\Field(type:"string")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private string $ticket;

    #[Gedmo\Timestampable(on: "create")]
    #[MongoDB\Field(type:"date")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private \DateTime $uploadDate;

    #[MongoDB\Field(type:"int")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private int $length;

    #[MongoDB\Field(type:"int")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private int $chunkSize;

    #[MongoDB\Field(type:"raw")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    #[File\Metadata(targetDocument: DocumentMetaData::class)]
    private $metadata;

    #[ApiProperty(types: ['https://schema.org/file'])]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    public \Symfony\Component\HttpFoundation\File\File|null $file = null;

    #[ApiProperty(types: ['https://schema.org/file'])]
    #[Groups(["document_files" =>"read"])]
    public ?MediaObject $media = null;

    #[MongoDB\Field(type:"string")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private string $archiveId;

    #[MongoDB\Field(type:"string")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private string $ucsId;

    #[MongoDB\Field(type:"date")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private string $timestamp;

    #[MongoDB\Field(type:"string")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private string $escName;

    #[MongoDB\Field(type:"string")]
    #[Groups(["document_files" =>"read", "document_files" => "write"])]
    private string $userIdCmd;

    #[MongoDB\Field(type: 'date')]
    #[Groups(["tickets:read"])]
    #[Gedmo\Timestampable(on: "create")]
    protected $createdAt;

    #[MongoDB\Field(type: 'date')]
    #[Groups(["tickets:read"])]
    #[Gedmo\Timestampable(on: "update", field: "updatedAt")]
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
            $this->setFile($file);
            $this->setMedia($file);
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
     * @return \Symfony\Component\HttpFoundation\File\File|null
     */
    public function getFile(): ?\Symfony\Component\HttpFoundation\File\File
    {
        return $this->file;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\File|null $file
     */
    public function setFile(?\Symfony\Component\HttpFoundation\File\File $file): self
    {
        $this->file = $file;

        return $this;
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

    /**
     * @return MediaObject|null
     */
    public function getMedia(): ?MediaObject
    {
        return $this->media;
    }

    /**
     * @param MediaObject|\Symfony\Component\HttpFoundation\File\File|null $media
     *
     * @return $this
     */
    public function setMedia(MediaObject|\Symfony\Component\HttpFoundation\File\File|null $media): self
    {
        $mediaObject = new MediaObject();
        if ($media instanceof \Symfony\Component\HttpFoundation\File\File) {
            $mediaObject->setFile($media);
            $mediaObject->setFilePath($media->getRealPath());
            $this->media = $mediaObject;
        } else {
            $this->media = $media;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getArchiveId(): string
    {
        return $this->archiveId;
    }

    /**
     * @param string $archiveId
     *
     * @return $this
     */
    public function setArchiveId(string $archiveId): self
    {
        $this->archiveId = $archiveId;

        return $this;
    }

    /**
     * @return string
     */
    public function getUcsId(): string
    {
        return $this->ucsId;
    }

    /**
     * @param string $ucsId
     *
     * @return $this
     */
    public function setUcsId(string $ucsId): self
    {
        $this->ucsId = $ucsId;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    /**
     * @param string $timestamp
     *
     * @return $this
     */
    public function setTimestamp(string $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @return string
     */
    public function getEscName(): string
    {
        return $this->escName;
    }

    /**
     * @param string $escName
     *
     * @return $this
     */
    public function setEscName(string $escName): self
    {
        $this->escName = $escName;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserIdCmd(): string
    {
        return $this->userIdCmd;
    }

    /**
     * @param string $userIdCmd
     *
     * @return $this
     */
    public function setUserIdCmd(string $userIdCmd): self
    {
        $this->userIdCmd = $userIdCmd;

        return $this;
    }
}

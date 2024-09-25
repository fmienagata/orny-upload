<?php

namespace UploadBundle\Document;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\File;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Symfony\Component\Serializer\Annotation\Groups;
use UploadBundle\Controller\DocumentFileController;

/**
 * @File(bucketName="document-file")
 */
#[MongoDB\Document(
    collection: 'document_files',
    repositoryClass: "UploadBundle\Repository\Document\DocumentFileRepository"
)]
#[ApiResource(
    normalizationContext: ['groups' => ['document_files:read']],
    denormalizationContext: ['groups' => ['document_files:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/document_files',
            name: '_upload_/document_files_get_collection',
            routeName: '_upload_/document_files_get_collection',
            controller: DocumentFileController::class
        ),
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
                                        'format' => 'binary',
                                    ],
                                    'fileName' => ['type' => 'string', 'example' => 'file.pdf'],
                                    'esc' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'scheme' => ['type' => 'string', 'example' => 'https'],
                                            'dns' => ['type' => 'string', 'example' => 'du0-aes-upload.demat.lan'],
                                            'port' => ['type' => 'string', 'example' => '10443'],
                                            'name' => ['type' => 'string', 'example' => 'ESCNODE_410'],
                                            'room' => ['type' => 'string', 'example' => '2f6f2d9fb46c4e558e8873a2ac7899ca'],
                                            'box' => ['type' => 'string', 'example' => 'c324f337acfd4fa4a93d124f58a6a260'],
                                        ],
                                    ],
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
                                        ],
                                    ],
                                    'ticket' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'id' => ['type' => 'string', 'example' => '644aa1b61b9a4b56e405acd4'],
                                        ],
                                    ],
                                ],
                            ],
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
                                        ],
                                    ],
                                    'ticket' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'attributes' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'name' => ['type' => 'string'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
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
                                    ],
                                ],
                            ],
                        ],
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
                                        ],
                                    ],
                                    'createdAt' => ['type' => 'date'],
                                    'updatedAt' => ['type' => 'date'],
                                ],
                            ],
                            'example' => [
                                'id' => '6424462c03b25a3d220809c6',
                                'associatedDocuments' => 'E000000000000411/1/5',
                            ],
                        ],
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
        ),
        new Get(
            uriTemplate: '/document_files/{id}/evidence',
            name: '_upload_/document_files/{id}/evidence_get',
            routeName: '_upload_/document_files/{id}/evidence_get',
            controller: DocumentFileController::class,
            normalizationContext: ['groups' => ['document_files:read']],
            denormalizationContext: ['groups' => ['document_files:write']],
            inputFormats: [
                'json' => ['application/json'],
            ],
            outputFormats: [
                'json' => ['application/json'],
                'xml' => ['application/xml'],
            ],
        ),
    ]
)]
class DocumentFile
{
    use TimestampableDocument;

    #[ApiProperty(identifier: true, readable: true)]
    #[Groups(['document_files:read'])]
    #[MongoDB\Field(type: 'string')]
    #[Id(type: 'string', strategy: 'auto')]
    private ?string $id;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['document_files:read', 'document_files:write'])]
    private string $originalName;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['document_files:read', 'document_files:write'])]
    private string $ticket;

    #[Gedmo\Timestampable(on: 'create')]
    #[MongoDB\Field(type: 'date')]
    #[Groups(['document_files:read', 'document_files:write'])]
    private \DateTime $uploadDate;

    #[MongoDB\Field(type: 'int')]
    #[Groups(['document_files:read', 'document_files:write'])]
    private int $length;

    #[MongoDB\Field(type: 'int')]
    #[Groups(['document_files:read', 'document_files:write'])]
    private int $chunkSize;

    #[MongoDB\Field(type: 'raw')]
    #[Groups(['document_files:read', 'document_files:write'])]
    #[File\Metadata(targetDocument: DocumentMetaData::class)]
    private $metadata;

    #[ApiProperty(types: ['https://schema.org/file'])]
    #[Groups(['document_files:read', 'document_files:write'])]
    public ?\Symfony\Component\HttpFoundation\File\File $file = null;

    #[ApiProperty(types: ['https://schema.org/file'])]
    #[Groups(['document_files:read'])]
    public ?MediaObject $media = null;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['document_files:read', 'document_files:write'])]
    private string $archiveId;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['document_files:read', 'document_files:write'])]
    private string $ucsId;

    #[MongoDB\Field(type: 'date')]
    #[Groups(['document_files:read', 'document_files:write'])]
    private string|\DateTime $timestamp;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['document_files:read', 'document_files:write'])]
    private string $escName;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['document_files:read', 'document_files:write'])]
    private string $userIdCmd;

    #[MongoDB\Field(type: 'date')]
    #[Groups(['tickets:read'])]
    #[Gedmo\Timestampable(on: 'create')]
    protected $createdAt;

    #[MongoDB\Field(type: 'date')]
    #[Groups(['tickets:read'])]
    #[Gedmo\Timestampable(on: 'update', field: 'updatedAt')]
    protected $updatedAt;

    /**
     * DocumentFile constructor.
     */
    public function __construct(?\Symfony\Component\HttpFoundation\File\File $file = null)
    {
        if ($file) {
            $this->setOriginalName($file->getFilename());
            $this->setFile($file);
            $this->setMedia($file);
        }
    }

    public function getId(): ?string
    {
        return $this->id ?? null;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    /**
     * @return $this
     */
    public function setOriginalName(string $originalName): self
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function getTicket(): string
    {
        return $this->ticket;
    }

    /**
     * @return $this
     */
    public function setTicket(string $ticket): self
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function getUploadDate(): \DateTime
    {
        return $this->uploadDate;
    }

    /**
     * @return $this
     */
    public function setUploadDate(\DateTime $uploadDate): self
    {
        $this->uploadDate = $uploadDate;

        return $this;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return $this
     */
    public function setLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * @return $this
     */
    public function setChunkSize(int $chunkSize): self
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function getFile(): ?\Symfony\Component\HttpFoundation\File\File
    {
        return $this->file;
    }

    public function setFile(?\Symfony\Component\HttpFoundation\File\File $file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return $this
     */
    public function setMetadata($metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getMedia(): ?MediaObject
    {
        return $this->media;
    }

    /**
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

    public function getArchiveId(): string
    {
        return $this->archiveId;
    }

    /**
     * @return $this
     */
    public function setArchiveId(string $archiveId): self
    {
        $this->archiveId = $archiveId;

        return $this;
    }

    public function getUcsId(): string
    {
        return $this->ucsId;
    }

    /**
     * @return $this
     */
    public function setUcsId(string $ucsId): self
    {
        $this->ucsId = $ucsId;

        return $this;
    }

    public function getTimestamp(): string|\DateTime
    {
        return $this->timestamp instanceof \DateTime ? $this->timestamp->format('Y-m-d H:i:s') : $this->timestamp;
    }

    /**
     * @return $this
     */
    public function setTimestamp(\DateTime|string $timestamp): self
    {
        if (is_string($timestamp)) {
            $timestamp = new \DateTime($timestamp);
        }
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getEscName(): string
    {
        return $this->escName;
    }

    /**
     * @return $this
     */
    public function setEscName(string $escName): self
    {
        $this->escName = $escName;

        return $this;
    }

    public function getUserIdCmd(): string
    {
        return $this->userIdCmd;
    }

    /**
     * @return $this
     */
    public function setUserIdCmd(string $userIdCmd): self
    {
        $this->userIdCmd = $userIdCmd;

        return $this;
    }
}

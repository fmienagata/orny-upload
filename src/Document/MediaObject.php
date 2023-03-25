<?php

namespace UploadBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations\File;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\{ApiResource, ApiProperty};
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use ApiPlatform\Metadata\{GetCollection, Get, Patch, Post, Delete, Put};
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use ApiPlatform\OpenApi\Model;
use UploadBundle\Controller\MediaObjectController;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[MongoDB\Document(
    collection: "media_objects",
    repositoryClass: "UploadBundle\Repository\Document\MediaObjectRepository"
)]
#[ApiResource(
    normalizationContext: ['groups' => ['media_object:read']],
    types: ['https://schema.org/MediaObject'],
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            controller: MediaObjectController::class,
            deserialize: false,
            validationContext: ['groups' => ['Default', 'media_object_create']],
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
                                    ]
                                ]
                            ]
                        ]
                    ])
                )
            )
        )
    ]
)]
class MediaObject
{
    use TimestampableEntity;

    #[ApiProperty(identifier:true)]
    #[Groups(["media_objects:read"])]
    #[MongoDB\Id]
    private string $id;

    #[ApiProperty(types: ['https://schema.org/contentUrl'])]
    #[Groups(['media_objects:read'])]
    public ?string $contentUrl = null;

    #[Vich\UploadableField(mapping: "media_object", fileNameProperty: "filePath")]
    #[Assert\NotNull(groups: ['media_object_create'])]
    public ?\Symfony\Component\HttpFoundation\File\File $file = null;

    #[MongoDB\Field(type:"string")]
    #[Groups(["media_objects" =>"read", "media_object_create"])]
    public ?string $filePath = null;

    #[Assert\NotBlank()]
    #[Gedmo\Timestampable(on: "create")]
    #[MongoDB\Field(type: 'date')]
    #[Groups(["media_objects:read"])]
    protected $createdAt;

    #[Assert\NotBlank()]
    #[Gedmo\Timestampable(on: "update", field: "updatedAt")]
    #[MongoDB\Field(type: 'date')]
    #[Groups(["media_objects:read"])]
    protected $updatedAt;

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getContentUrl(): ?string
    {
        return $this->contentUrl;
    }

    /**
     * @param string|null $contentUrl
     *
     * @return $this
     */
    public function setContentUrl(?string $contentUrl): self
    {
        $this->contentUrl = $contentUrl;

        return $this;
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
     *
     * @return $this
     */
    public function setFile(?\Symfony\Component\HttpFoundation\File\File $file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    /**
     * @param string|null $filePath
     *
     * @return $this
     */
    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }
}

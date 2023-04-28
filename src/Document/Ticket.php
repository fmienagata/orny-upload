<?php

namespace UploadBundle\Document;

use ApiPlatform\Doctrine\Odm\State\CollectionProvider;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\{ApiResource, ApiProperty};
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;
use ApiPlatform\Metadata\{GetCollection, Get, Post, Put};
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use UploadBundle\Controller\TicketController;

#[MongoDB\Document(
    collection: "tickets",
    repositoryClass: "UploadBundle\Repository\Document\TicketRepository"
)]
#[ApiResource(
    uriTemplate: '/tickets',
    types: ['https://schema.org/Tickets'],
    order: ['createdAt' => 'DESC'],
    paginationEnabled: true,
    schemes: ['http','https'],
    normalizationContext: ["group" => ["tickets:read"]],
    denormalizationContext: ["group" => ["tickets:write"]],
    operations: [
        new GetCollection(
            uriTemplate: '/tickets'
        ),
        new Get(
            uriTemplate: '/tickets/{id}',
            controller: TicketController::class
        ),
        new Post(
            uriTemplate: '/tickets'
        ),
        new Put(
            uriTemplate: '/tickets/{id}',
            controller: TicketController::class
        )
    ]
)]
#[MongoDB\UniqueIndex(
    keys: ["ticketHash" => "asc"],
    name: "unique_ticket_ticketHash"
)]
class Ticket
{
    use TimestampableDocument;

    #[ApiProperty(identifier:true)]
    #[Groups(["tickets:read"])]
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type:"string")]
    #[Groups(["tickets" =>"read", "tickets" => "write"])]
    protected ?string $associatedDocument;

    #[MongoDB\Field(type:"string")]
    #[Groups(["tickets" =>"read", "tickets" => "write"])]
    protected string $location;

    #[MongoDB\Field(type:"string")]
    #[Groups(["tickets" =>"read", "tickets" => "write"])]
    protected string $tempLocation;

    #[Mongodb\Field(type: "string")]
    #[Groups(["tickets:read", "tickets:write"])]
    protected string $reference;

    #[Mongodb\Field(type: "int")]
    #[Groups(["tickets:read", "tickets:write"])]
    protected ?int $pages;

    #[Mongodb\Field(type: "bool")]
    #[Groups(["tickets:read", "tickets:write"])]
    protected bool $safetyPurged = false;

    #[Mongodb\Field(type: "string")]
    #[Groups(["tickets:read", "tickets:write"])]
    protected string $cdSafetyArchive;

    #[Mongodb\Field(type: "string")]
    #[Groups(["tickets:read", "tickets:write"])]
    protected string $indexBatch;

    #[Mongodb\Field(type: "int")]
    #[Groups(["tickets:read", "tickets:write"])]
    protected int $batchProduction;

    #[Mongodb\Field(type: "bool")]
    #[Groups(["tickets:read", "tickets:write"])]
    protected bool $interBox;

    #[Mongodb\Field(type: "string")]
    #[Groups(["tickets:read", "tickets:write"])]
    #[Assert\NotBlank()]
    protected string $customerCode;

    #[Mongodb\Field(type: "string")]
    #[Groups(["tickets:read", "tickets:write"])]
    #[Assert\NotBlank()]
    protected string $applicationCode;

    #[Mongodb\Field(type: "string")]
    #[Groups(["tickets:read", "tickets:write"])]
    protected string $subCustomerCode;

    #[Mongodb\Field(type: "string")]
    #[Groups(["tickets:read", "tickets:write"])]
    #[Assert\NotBlank()]
    protected string $applicationVersion;

    #[Mongodb\Field(type: "string")]
    #[Groups(["tickets:read", "tickets:write"])]
    protected string $ticketHash;

    #[Mongodb\Field(type: "string")]
    #[Groups(["tickets:read", "tickets:write"])]
    protected string $status;

    #[Mongodb\Field(type: "string")]
    #[Groups(["tickets:read", "tickets:write"])]
    protected string $drawer;

    #[Mongodb\Field(type: "raw")]
    #[MongoDB\EmbedOne(targetDocument: DocumentFile::class, discriminatorField: "id")]
    #[Groups(["tickets:read", "tickets:write"])]
    protected ?DocumentFile $file = null;

    #[Gedmo\Timestampable(on: "create")]
    #[MongoDB\Field(type: 'date')]
    #[Groups(["tickets:read"])]
    protected $createdAt;

    #[Gedmo\Timestampable(on: "update", field: "updatedAt")]
    #[MongoDB\Field(type: 'date')]
    #[Groups(["tickets:read"])]
    protected $updatedAt;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getAssociatedDocument(): ?string
    {
        return $this->associatedDocument;
    }

    /**
     * @param string|null $associatedDocument
     *
     * @return $this
     */
    public function setAssociatedDocument(?string $associatedDocument): self
    {
        $this->associatedDocument = $associatedDocument;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location
     *
     * @return $this
     */
    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return string
     */
    public function getTempLocation(): string
    {
        return $this->tempLocation;
    }

    /**
     * @param string $tempLocation
     *
     * @return $this
     */
    public function setTempLocation(string $tempLocation): self
    {
        $this->tempLocation = $tempLocation;

        return $this;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     *
     * @return $this
     */
    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return  $this;
    }

    /**
     * @return int|null
     */
    public function getPages(): ?int
    {
        return $this->pages;
    }

    /**
     * @param int|null $pages
     *
     * @return $this
     */
    public function setPages(?int $pages): self
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSafetyPurged(): bool
    {
        return $this->safetyPurged;
    }

    /**
     * @param bool $safetyPurged
     *
     * @return $this
     */
    public function setSafetyPurged(bool $safetyPurged): self
    {
        $this->safetyPurged = $safetyPurged;

        return $this;
    }

    /**
     * @return string
     */
    public function getCdSafetyArchive(): string
    {
        return $this->cdSafetyArchive;
    }

    /**
     * @param string $cdSafetyArchive
     *
     * @return $this
     */
    public function setCdSafetyArchive(string $cdSafetyArchive): self
    {
        $this->cdSafetyArchive = $cdSafetyArchive;

        return $this;
    }

    /**
     * @return string
     */
    public function getIndexBatch(): string
    {
        return $this->indexBatch;
    }

    /**
     * @param string $indexBatch
     *
     * @return $this
     */
    public function setIndexBatch(string $indexBatch): self
    {
        $this->indexBatch = $indexBatch;

        return $this;
    }

    /**
     * @return int
     */
    public function getBatchProduction(): int
    {
        return $this->batchProduction;
    }

    /**
     * @param int $batchProduction
     *
     * @return $this
     */
    public function setBatchProduction(int $batchProduction): self
    {
        $this->batchProduction = $batchProduction;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInterBox(): bool
    {
        return $this->interBox;
    }

    /**
     * @param bool $interBox
     *
     * @return $this
     */
    public function setInterBox(bool $interBox): self
    {
        $this->interBox = $interBox;

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerCode(): string
    {
        return $this->customerCode;
    }

    /**
     * @param string $customerCode
     *
     * @return $this
     */
    public function setCustomerCode(string $customerCode): self
    {
        $this->customerCode = $customerCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getApplicationCode(): string
    {
        return $this->applicationCode;
    }

    /**
     * @param string $applicationCode
     *
     * @return $this
     */
    public function setApplicationCode(string $applicationCode): self
    {
        $this->applicationCode = $applicationCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubCustomerCode(): string
    {
        return $this->subCustomerCode;
    }

    /**
     * @param string $subCustomerCode
     *
     * @return $this
     */
    public function setSubCustomerCode(string $subCustomerCode): self
    {
        $this->subCustomerCode = $subCustomerCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getApplicationVersion(): string
    {
        return $this->applicationVersion;
    }

    /**
     * @param string $applicationVersion
     *
     * @return $this
     */
    public function setApplicationVersion(string $applicationVersion): self
    {
        $this->applicationVersion = $applicationVersion;

        return $this;
    }

    /**
     * @return string
     */
    public function getTicketHash(): string
    {
        return $this->ticketHash;
    }

    /**
     * @param string $ticketHash
     *
     * @return $this
     */
    public function setTicketHash(string $ticketHash): self
    {
        $this->ticketHash = $ticketHash;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getDrawer(): string
    {
        return $this->drawer;
    }

    /**
     * @param string $drawer
     *
     * @return $this
     */
    public function setDrawer(string $drawer): self
    {
        $this->drawer = $drawer;

        return $this;
    }

    /**
     * @return DocumentFile|null
     */
    public function getFile(): ?DocumentFile
    {
        return $this->file;
    }

    /**
     * @param DocumentFile|null $file
     *
     * @return $this
     */
    public function setFile(?DocumentFile $file): self
    {
        $this->file = $file;

        return $this;
    }
}

<?php

namespace UploadBundle\Document;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use UploadBundle\Controller\TicketController;

#[MongoDB\Document(
    collection: 'tickets',
    repositoryClass: "UploadBundle\Repository\Document\TicketRepository"
)]
#[ApiResource(
    uriTemplate: '/tickets',
    types: ['https://schema.org/Tickets'],
    order: ['createdAt' => 'DESC'],
    paginationEnabled: true,
    schemes: ['http', 'https'],
    normalizationContext: ['group' => ['tickets:read']],
    denormalizationContext: ['group' => ['tickets:write']],
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
        ),
    ]
)]
#[MongoDB\UniqueIndex(
    keys: ['ticketHash' => 'asc'],
    name: 'unique_ticket_ticketHash'
)]
class Ticket
{
    use TimestampableDocument;

    #[ApiProperty(identifier: true)]
    #[Groups(['tickets:read'])]
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['tickets' => 'read', 'tickets' => 'write'])]
    protected ?string $associatedDocument;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['tickets' => 'read', 'tickets' => 'write'])]
    protected string $location;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['tickets' => 'read', 'tickets' => 'write'])]
    protected string $tempLocation;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['tickets:read', 'tickets:write'])]
    protected string $reference;

    #[MongoDB\Field(type: 'int')]
    #[Groups(['tickets:read', 'tickets:write'])]
    protected ?int $pages;

    #[MongoDB\Field(type: 'bool')]
    #[Groups(['tickets:read', 'tickets:write'])]
    protected bool $safetyPurged = false;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['tickets:read', 'tickets:write'])]
    protected string $cdSafetyArchive;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['tickets:read', 'tickets:write'])]
    protected string $indexBatch;

    #[MongoDB\Field(type: 'int')]
    #[Groups(['tickets:read', 'tickets:write'])]
    protected int $batchProduction;

    #[MongoDB\Field(type: 'bool')]
    #[Groups(['tickets:read', 'tickets:write'])]
    protected bool $interBox;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['tickets:read', 'tickets:write'])]
    #[Assert\NotBlank()]
    protected string $customerCode;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['tickets:read', 'tickets:write'])]
    #[Assert\NotBlank()]
    protected string $applicationCode;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['tickets:read', 'tickets:write'])]
    protected string $subCustomerCode;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['tickets:read', 'tickets:write'])]
    #[Assert\NotBlank()]
    protected string $applicationVersion;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['tickets:read', 'tickets:write'])]
    protected string $ticketHash;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['tickets:read', 'tickets:write'])]
    protected string $status;

    #[MongoDB\Field(type: 'string')]
    #[Groups(['tickets:read', 'tickets:write'])]
    protected string $drawer;

    #[MongoDB\Field(type: 'raw')]
    #[MongoDB\EmbedOne(targetDocument: DocumentFile::class, discriminatorField: 'id')]
    #[Groups(['tickets:read', 'tickets:write'])]
    protected ?DocumentFile $file = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[MongoDB\Field(type: 'date')]
    #[Groups(['tickets:read'])]
    protected $createdAt;

    #[Gedmo\Timestampable(on: 'update', field: 'updatedAt')]
    #[MongoDB\Field(type: 'date')]
    #[Groups(['tickets:read'])]
    protected $updatedAt;

    public function getId(): string
    {
        return $this->id;
    }

    public function getAssociatedDocument(): ?string
    {
        return $this->associatedDocument;
    }

    /**
     * @return $this
     */
    public function setAssociatedDocument(?string $associatedDocument): self
    {
        $this->associatedDocument = $associatedDocument;

        return $this;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @return $this
     */
    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getTempLocation(): string
    {
        return $this->tempLocation;
    }

    /**
     * @return $this
     */
    public function setTempLocation(string $tempLocation): self
    {
        $this->tempLocation = $tempLocation;

        return $this;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @return $this
     */
    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getPages(): ?int
    {
        return $this->pages;
    }

    /**
     * @return $this
     */
    public function setPages(?int $pages): self
    {
        $this->pages = $pages;

        return $this;
    }

    public function isSafetyPurged(): bool
    {
        return $this->safetyPurged;
    }

    /**
     * @return $this
     */
    public function setSafetyPurged(bool $safetyPurged): self
    {
        $this->safetyPurged = $safetyPurged;

        return $this;
    }

    public function getCdSafetyArchive(): string
    {
        return $this->cdSafetyArchive;
    }

    /**
     * @return $this
     */
    public function setCdSafetyArchive(string $cdSafetyArchive): self
    {
        $this->cdSafetyArchive = $cdSafetyArchive;

        return $this;
    }

    public function getIndexBatch(): string
    {
        return $this->indexBatch;
    }

    /**
     * @return $this
     */
    public function setIndexBatch(string $indexBatch): self
    {
        $this->indexBatch = $indexBatch;

        return $this;
    }

    public function getBatchProduction(): int
    {
        return $this->batchProduction;
    }

    /**
     * @return $this
     */
    public function setBatchProduction(int $batchProduction): self
    {
        $this->batchProduction = $batchProduction;

        return $this;
    }

    public function isInterBox(): bool
    {
        return $this->interBox;
    }

    /**
     * @return $this
     */
    public function setInterBox(bool $interBox): self
    {
        $this->interBox = $interBox;

        return $this;
    }

    public function getCustomerCode(): string
    {
        return $this->customerCode;
    }

    /**
     * @return $this
     */
    public function setCustomerCode(string $customerCode): self
    {
        $this->customerCode = $customerCode;

        return $this;
    }

    public function getApplicationCode(): string
    {
        return $this->applicationCode;
    }

    /**
     * @return $this
     */
    public function setApplicationCode(string $applicationCode): self
    {
        $this->applicationCode = $applicationCode;

        return $this;
    }

    public function getSubCustomerCode(): string
    {
        return $this->subCustomerCode;
    }

    /**
     * @return $this
     */
    public function setSubCustomerCode(string $subCustomerCode): self
    {
        $this->subCustomerCode = $subCustomerCode;

        return $this;
    }

    public function getApplicationVersion(): string
    {
        return $this->applicationVersion;
    }

    /**
     * @return $this
     */
    public function setApplicationVersion(string $applicationVersion): self
    {
        $this->applicationVersion = $applicationVersion;

        return $this;
    }

    public function getTicketHash(): string
    {
        return $this->ticketHash;
    }

    /**
     * @return $this
     */
    public function setTicketHash(string $ticketHash): self
    {
        $this->ticketHash = $ticketHash;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDrawer(): string
    {
        return $this->drawer;
    }

    /**
     * @return $this
     */
    public function setDrawer(string $drawer): self
    {
        $this->drawer = $drawer;

        return $this;
    }

    public function getFile(): ?DocumentFile
    {
        return $this->file;
    }

    /**
     * @return $this
     */
    public function setFile(?DocumentFile $file): self
    {
        $this->file = $file;

        return $this;
    }
}

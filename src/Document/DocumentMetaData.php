<?php

namespace UploadBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;

#[MongoDB\EmbeddedDocument]
class DocumentMetaData
{
    #[MongoDB\Field(type: 'string')]
    #[Groups(['document_files:read', 'document_files:write'])]
    private $contentType;

    #[MongoDB\Field(type: 'int')]
    #[Groups(['document_files:read', 'document_files:write'])]
    private int $aTime;

    #[MongoDB\Field(type: 'int')]
    #[Groups(['document_files:read', 'document_files:write'])]
    private int $mTime;

    #[MongoDB\Field(type: 'int')]
    #[Groups(['document_files:read', 'document_files:write'])]
    private int $cTime;

    #[MongoDB\Field(type: 'int')]
    #[Groups(['document_files:read', 'document_files:write'])]
    private int $inode;

    #[MongoDB\Field(type: 'int')]
    #[Groups(['document_files:read', 'document_files:write'])]
    private int $size;

    #[MongoDB\Field(type: 'int')]
    #[Groups(['document_files:read', 'document_files:write'])]
    private int $perms;

    /**
     * DocumentMetaData constructor.
     */
    public function __construct(string $contentType, array $data = [])
    {
        $this->contentType = $contentType;

        foreach ($data as $key => $value) {
            $method = sprintf('set%s', ucfirst($key));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function getATime(): int
    {
        return $this->aTime;
    }

    /**
     * @return $this
     */
    public function setATime(int $aTime): self
    {
        $this->aTime = $aTime;

        return $this;
    }

    public function getMTime(): int
    {
        return $this->mTime;
    }

    /**
     * @return $this
     */
    public function setMTime(int $mTime): self
    {
        $this->mTime = $mTime;

        return $this;
    }

    public function getCTime(): int
    {
        return $this->cTime;
    }

    /**
     * @return $this
     */
    public function setCTime(int $cTime): self
    {
        $this->cTime = $cTime;

        return $this;
    }

    public function getInode(): int
    {
        return $this->inode;
    }

    public function setInode(int $inode): self
    {
        $this->inode = $inode;

        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return $this
     */
    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getPerms(): int
    {
        return $this->perms;
    }

    /**
     * @return $this
     */
    public function setPerms(int $perms): self
    {
        $this->perms = $perms;

        return $this;
    }
}

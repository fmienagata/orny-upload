<?php


namespace UploadBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations As MongoDB;
use Symfony\Component\Serializer\Annotation\Groups;

#[MongoDB\EmbeddedDocument]
class DocumentMetaData
{
    #[MongoDB\Field(type:"string")]
    #[Groups(["document_files:read", "document_files:write"])]
    private $contentType;

    #[MongoDB\Field(type:"int")]
    #[Groups(["document_files:read", "document_files:write"])]
    private int $aTime;

    #[MongoDB\Field(type:"int")]
    #[Groups(["document_files:read", "document_files:write"])]
    private int $mTime;

    #[MongoDB\Field(type:"int")]
    #[Groups(["document_files:read", "document_files:write"])]
    private int $cTime;

    #[MongoDB\Field(type:"int")]
    #[Groups(["document_files:read", "document_files:write"])]
    private int $inode;

    #[MongoDB\Field(type:"int")]
    #[Groups(["document_files:read", "document_files:write"])]
    private int $size;

    #[MongoDB\Field(type:"int")]
    #[Groups(["document_files:read", "document_files:write"])]
    private int $perms;

    /**
     * DocumentMetaData constructor.
     *
     * @param string $contentType
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

    /**
     * @return string|null
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    /**
     * @return int
     */
    public function getATime(): int
    {
        return $this->aTime;
    }

    /**
     * @param int $aTime
     *
     * @return $this
     */
    public function setATime(int $aTime): self
    {
        $this->aTime = $aTime;

        return $this;
    }

    /**
     * @return int
     */
    public function getMTime(): int
    {
        return $this->mTime;
    }

    /**
     * @param int $mTime
     *
     * @return $this
     */
    public function setMTime(int $mTime): self
    {
        $this->mTime = $mTime;

        return $this;
    }

    /**
     * @return int
     */
    public function getCTime(): int
    {
        return $this->cTime;
    }

    /**
     * @param int $cTime
     *
     * @return $this
     */
    public function setCTime(int $cTime): self
    {
        $this->cTime = $cTime;

        return $this;
    }

    /**
     * @return int
     */
    public function getInode(): int
    {
        return $this->inode;
    }

    /**
     * @param int $inode
     *
     * @return self
     */
    public function setInode(int $inode): self
    {
        $this->inode = $inode;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     *
     * @return $this
     */
    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return int
     */
    public function getPerms(): int
    {
        return $this->perms;
    }

    /**
     * @param int $perms
     *
     * @return $this
     */
    public function setPerms(int $perms): self
    {
        $this->perms = $perms;

        return  $this;
    }
}

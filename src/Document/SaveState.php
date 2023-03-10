<?php

namespace App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbeddedDocument;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedMany;

/**
 * @EmbeddedDocument
 */
class SaveState
{
    /**
     * @MongoDB\Id()
     */
    private ?string $id = null;

    /**
     * @MongoDB\Field(type="string")
     */
    private ?string $fileIdentifier = null;

    /**
     * @EmbedMany(targetDocument=Pokemon::class)
     */
    private ArrayCollection $party;

    public function __construct() {
        $this->party = new ArrayCollection();
    }

    /**
     * @MongoDB\Field(type="string")
     */
    private ?string $trainerName = null;

    /**
     * @MongoDB\Field(type="string")
     */
    private ?string $playtime = null;

    /**
     * @MongoDB\Field(type="string")
     */
    private ?string $uploaderName = null;

    /**
     * @MongoDB\Field(type="date_immutable")
     */
    private ?\DateTimeImmutable $uploadedAt = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTrainerName(): ?string
    {
        return $this->trainerName;
    }

    /**
     * @param string|null $trainer_name
     */
    public function setTrainerName(?string $trainer_name): void
    {
        $this->trainerName = $trainer_name;
    }

    /**
     * @return string|null
     */
    public function getFileIdentifier(): ?string
    {
        return $this->fileIdentifier;
    }

    /**
     * @param string|null $file_identifier
     */
    public function setFileIdentifier(?string $file_identifier): void
    {
        $this->fileIdentifier = $file_identifier;
    }

    /**
     * @return string|null
     */
    public function getPlaytime(): ?string
    {
        return $this->playtime;
    }

    /**
     * @param string|null $playtime
     */
    public function setPlaytime(?string $playtime): void
    {
        $this->playtime = $playtime;
    }


    /**
     * @return string|null
     */
    public function getUploaderName(): ?string
    {
        return $this->uploaderName;
    }

    /**
     * @param string|null $uploader_name
     */
    public function setUploaderName(?string $uploader_name): void
    {
        $this->uploaderName = $uploader_name;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    /**
     * @param \DateTimeImmutable|null $uploaded_at
     */
    public function setUploadedAt(?\DateTimeImmutable $uploaded_at): void
    {
        $this->uploadedAt = $uploaded_at;
    }

    /**
     * @return ArrayCollection
     */
    public function getParty(): ArrayCollection
    {
        return $this->party;
    }

    /**
     * @param ArrayCollection $party
     */
    public function setParty(ArrayCollection $party): void
    {
        $this->party = $party;
    }

}

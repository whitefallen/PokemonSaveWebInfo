<?php

namespace App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedMany;

/**
 * @MongoDB\Document
 */
class Session
{
    /**
     * @MongoDB\Id()
     */
    private ?string $id = null;

    /**
     * @MongoDB\Field(type="string")
     */
    private ?string $uuid = null;

    /**
     * @MongoDB\Field(type="string")
     */
    private ?string $name = null;

    /**
     * @MongoDB\Field(type="date_immutable")
     */
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @EmbedMany(targetDocument=SaveState::class)
     */
    private ArrayCollection $timeline;

    public function __construct() {
        $this->timeline = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->createdAt = $created_at;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getTimeline(): ArrayCollection
    {
        return $this->timeline;
    }

    /**
     * @param ArrayCollection|null $timeline
     */
    public function setTimeline(?ArrayCollection $timeline): void
    {
        $this->timeline = $timeline;
    }
}

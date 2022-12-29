<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbeddedDocument;

/**
 * @EmbeddedDocument()
 */
class Pokemon
{
    /**
     * @MongoDB\Id()
     */
    private ?string $id = null;
    /**
     * @MongoDB\Field(type="int")
     */
    private ?int $species_id = null;
    /**
     * @MongoDB\Field(type="string")
     */
    private ?string $nickname = null;
    /**
     * @MongoDB\Field(type="int")
     */
    private ?int $level = null;

    /**
     * @return int|null
     */
    public function getSpeciesId(): ?int
    {
        return $this->species_id;
    }

    /**
     * @param int|null $species_id
     */
    public function setSpeciesId(?int $species_id): void
    {
        $this->species_id = $species_id;
    }

    /**
     * @return string|null
     */
    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    /**
     * @param string|null $nickname
     */
    public function setNickname(?string $nickname): void
    {
        $this->nickname = $nickname;
    }

    /**
     * @return int|null
     */
    public function getLevel(): ?int
    {
        return $this->level;
    }

    /**
     * @param int|null $level
     */
    public function setLevel(?int $level): void
    {
        $this->level = $level;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }
}
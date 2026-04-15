<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'filelinked')]
class FileLinked
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $type = '';

    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $name = '';

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private mixed $filedata = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = trim($type);

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = trim($name);

        return $this;
    }

    public function getFiledata(): mixed
    {
        return $this->filedata;
    }

    public function setFiledata(mixed $filedata): self
    {
        $this->filedata = $filedata;

        return $this;
    }
}

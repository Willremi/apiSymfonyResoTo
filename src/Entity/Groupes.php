<?php

namespace App\Entity;

use App\Repository\GroupesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GroupesRepository::class)]
class Groupes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getGroupes', 'getRegions'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getGroupes', 'getRegions'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['getGroupes', 'getRegions'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getGroupes', 'getRegions'])]
    private ?string $contact = null;

    #[ORM\ManyToOne(inversedBy: 'groupes')]
    #[Groups(['getGroupes', 'getRegions'])]
    private ?Regions $regions = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(string $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getRegions(): ?Regions
    {
        return $this->regions;
    }

    public function setRegions(?Regions $regions): self
    {
        $this->regions = $regions;

        return $this;
    }
}

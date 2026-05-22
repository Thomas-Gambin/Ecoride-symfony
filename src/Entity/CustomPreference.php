<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CustomPreferenceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomPreferenceRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_CUSTOM_PREFERENCE_LABEL_PER_DRIVER', columns: ['driver_preference_id', 'label'])]
class CustomPreference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'customPreferences')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?DriverPreference $driverPreference = null;

    #[ORM\Column(length: 120)]
    private ?string $label = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDriverPreference(): ?DriverPreference
    {
        return $this->driverPreference;
    }

    public function setDriverPreference(?DriverPreference $driverPreference): static
    {
        $this->driverPreference = $driverPreference;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = trim($label);

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}

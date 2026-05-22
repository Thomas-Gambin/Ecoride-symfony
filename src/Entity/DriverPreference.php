<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DriverPreferenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DriverPreferenceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class DriverPreference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'driverPreference')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column]
    private bool $allowSmoking = false;

    #[ORM\Column]
    private bool $allowAnimals = false;

    /**
     * @var Collection<int, CustomPreference>
     */
    #[ORM\OneToMany(targetEntity: CustomPreference::class, mappedBy: 'driverPreference', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $customPreferences;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->customPreferences = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function allowsSmoking(): bool
    {
        return $this->allowSmoking;
    }

    public function setAllowSmoking(bool $allowSmoking): static
    {
        $this->allowSmoking = $allowSmoking;

        return $this;
    }

    public function allowsAnimals(): bool
    {
        return $this->allowAnimals;
    }

    public function setAllowAnimals(bool $allowAnimals): static
    {
        $this->allowAnimals = $allowAnimals;

        return $this;
    }

    /**
     * @return Collection<int, CustomPreference>
     */
    public function getCustomPreferences(): Collection
    {
        return $this->customPreferences;
    }

    public function addCustomPreference(CustomPreference $customPreference): static
    {
        if (!$this->customPreferences->contains($customPreference)) {
            $this->customPreferences->add($customPreference);
            $customPreference->setDriverPreference($this);
        }

        return $this;
    }

    public function removeCustomPreference(CustomPreference $customPreference): static
    {
        if ($this->customPreferences->removeElement($customPreference)) {
            if ($customPreference->getDriverPreference() === $this) {
                $customPreference->setDriverPreference(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function touchUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}

<?php

namespace App\Entity;

use App\Enum\CarpoolStatus;
use App\Repository\CarpoolRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarpoolRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Carpool
{
    public const PLATFORM_FEE_CREDITS = 2;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $departureDate = null;

    #[ORM\Column]
    private ?\DateTime $departureTime = null;

    #[ORM\Column(length: 50)]
    private ?string $departureLocation = null;

    #[ORM\Column(length: 5)]
    private ?string $departureCityCode = null;

    #[ORM\Column(length: 10)]
    private ?string $departurePostalCode = null;

    #[ORM\Column]
    private ?\DateTime $arrivalDate = null;

    #[ORM\Column]
    private ?\DateTime $arrivalTime = null;

    #[ORM\Column(length: 50)]
    private ?string $arrivalLocation = null;

    #[ORM\Column(length: 5)]
    private ?string $arrivalCityCode = null;

    #[ORM\Column(length: 10)]
    private ?string $arrivalPostalCode = null;

    #[ORM\Column(length: 50, enumType: CarpoolStatus::class)]
    private CarpoolStatus $status = CarpoolStatus::Open;

    #[ORM\Column]
    private ?int $seatCount = null;

    #[ORM\Column]
    private ?float $pricePerPerson = null;

    #[ORM\Column(options: ['default' => self::PLATFORM_FEE_CREDITS])]
    private int $platformFeeCredits = self::PLATFORM_FEE_CREDITS;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'carpools')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Car $car = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'carpools')]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->platformFeeCredits = self::PLATFORM_FEE_CREDITS;
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function touchUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepartureDate(): ?\DateTime
    {
        return $this->departureDate;
    }

    public function setDepartureDate(\DateTime $departureDate): static
    {
        $this->departureDate = $departureDate;

        return $this;
    }

    public function getDepartureTime(): ?\DateTime
    {
        return $this->departureTime;
    }

    public function setDepartureTime(\DateTime $departureTime): static
    {
        $this->departureTime = $departureTime;

        return $this;
    }

    public function getDepartureLocation(): ?string
    {
        return $this->departureLocation;
    }

    public function setDepartureLocation(string $departureLocation): static
    {
        $this->departureLocation = $departureLocation;

        return $this;
    }

    public function getDepartureCityCode(): ?string
    {
        return $this->departureCityCode;
    }

    public function setDepartureCityCode(string $departureCityCode): static
    {
        $this->departureCityCode = $departureCityCode;

        return $this;
    }

    public function getDeparturePostalCode(): ?string
    {
        return $this->departurePostalCode;
    }

    public function setDeparturePostalCode(string $departurePostalCode): static
    {
        $this->departurePostalCode = $departurePostalCode;

        return $this;
    }

    public function getArrivalDate(): ?\DateTime
    {
        return $this->arrivalDate;
    }

    public function setArrivalDate(\DateTime $arrivalDate): static
    {
        $this->arrivalDate = $arrivalDate;

        return $this;
    }

    public function getArrivalTime(): ?\DateTime
    {
        return $this->arrivalTime;
    }

    public function setArrivalTime(\DateTime $arrivalTime): static
    {
        $this->arrivalTime = $arrivalTime;

        return $this;
    }

    public function getArrivalLocation(): ?string
    {
        return $this->arrivalLocation;
    }

    public function setArrivalLocation(string $arrivalLocation): static
    {
        $this->arrivalLocation = $arrivalLocation;

        return $this;
    }

    public function getArrivalCityCode(): ?string
    {
        return $this->arrivalCityCode;
    }

    public function setArrivalCityCode(string $arrivalCityCode): static
    {
        $this->arrivalCityCode = $arrivalCityCode;

        return $this;
    }

    public function getArrivalPostalCode(): ?string
    {
        return $this->arrivalPostalCode;
    }

    public function setArrivalPostalCode(string $arrivalPostalCode): static
    {
        $this->arrivalPostalCode = $arrivalPostalCode;

        return $this;
    }

    public function getStatus(): CarpoolStatus
    {
        return $this->status;
    }

    public function setStatus(CarpoolStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSeatCount(): ?int
    {
        return $this->seatCount;
    }

    public function setSeatCount(int $seatCount): static
    {
        $this->seatCount = $seatCount;

        return $this;
    }

    public function getPricePerPerson(): ?float
    {
        return $this->pricePerPerson;
    }

    public function setPricePerPerson(float $pricePerPerson): static
    {
        $this->pricePerPerson = $pricePerPerson;

        return $this;
    }

    public function getPlatformFeeCredits(): int
    {
        return $this->platformFeeCredits;
    }

    public function setPlatformFeeCredits(int $platformFeeCredits): static
    {
        $this->platformFeeCredits = $platformFeeCredits;

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

    public function getCar(): ?Car
    {
        return $this->car;
    }

    public function setCar(?Car $car): static
    {
        $this->car = $car;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addCarpool($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeCarpool($this);
        }

        return $this;
    }
}

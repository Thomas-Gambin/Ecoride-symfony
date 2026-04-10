<?php

declare(strict_types=1);

namespace App\Document;

use App\Repository\Mongo\TripIncidentRepository;
use DateTimeImmutable;
use Doctrine\ODM\MongoDB\Mapping\Attribute as ODM;

#[ODM\Document(collection: 'trip_incidents', repositoryClass: TripIncidentRepository::class)]
class TripIncident
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'int')]
    private int $carpoolId = 0;

    #[ODM\Field(type: 'int')]
    private int $driverUserId = 0;

    #[ODM\Field(type: 'int')]
    private int $passengerUserId = 0;

    #[ODM\Field(type: 'int')]
    private int $declaredByUserId = 0;

    #[ODM\Field(type: 'string')]
    private string $status = 'open';

    #[ODM\Field(type: 'string', nullable: true)]
    private ?string $reason = null;

    #[ODM\Field(type: 'string', nullable: true)]
    private ?string $comment = null;

    #[ODM\EmbedOne(targetDocument: TripSnapshot::class)]
    private ?TripSnapshot $tripSnapshot = null;

    #[ODM\Field(type: 'date_immutable')]
    private ?DateTimeImmutable $createdAt = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCarpoolId(): int
    {
        return $this->carpoolId;
    }

    public function setCarpoolId(int $carpoolId): self
    {
        $this->carpoolId = $carpoolId;

        return $this;
    }

    public function getDriverUserId(): int
    {
        return $this->driverUserId;
    }

    public function setDriverUserId(int $driverUserId): self
    {
        $this->driverUserId = $driverUserId;

        return $this;
    }

    public function getPassengerUserId(): int
    {
        return $this->passengerUserId;
    }

    public function setPassengerUserId(int $passengerUserId): self
    {
        $this->passengerUserId = $passengerUserId;

        return $this;
    }

    public function getDeclaredByUserId(): int
    {
        return $this->declaredByUserId;
    }

    public function setDeclaredByUserId(int $declaredByUserId): self
    {
        $this->declaredByUserId = $declaredByUserId;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getTripSnapshot(): ?TripSnapshot
    {
        return $this->tripSnapshot;
    }

    public function setTripSnapshot(?TripSnapshot $tripSnapshot): self
    {
        $this->tripSnapshot = $tripSnapshot;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}

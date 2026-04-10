<?php

declare(strict_types=1);

namespace App\Document;

use App\Repository\Mongo\CarpoolReviewRepository;
use DateTimeImmutable;
use Doctrine\ODM\MongoDB\Mapping\Attribute as ODM;

#[ODM\Document(collection: 'carpool_reviews', repositoryClass: CarpoolReviewRepository::class)]
#[ODM\Index(keys: ['carpoolId' => 'asc', 'createdAt' => 'desc'])]
class CarpoolReview
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
    private int $rating = 0;

    #[ODM\Field(type: 'string', nullable: true)]
    private ?string $comment = null;

    #[ODM\Field(type: 'string')]
    private string $status = 'pending';

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

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;

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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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

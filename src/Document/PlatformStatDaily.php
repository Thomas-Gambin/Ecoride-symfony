<?php

declare(strict_types=1);

namespace App\Document;

use App\Repository\Mongo\PlatformStatDailyRepository;
use DateTimeImmutable;
use Doctrine\ODM\MongoDB\Mapping\Attribute as ODM;

#[ODM\Document(collection: 'platform_stats_daily', repositoryClass: PlatformStatDailyRepository::class)]
#[ODM\Index(keys: ['statDate' => 'asc'], options: ['unique' => true])]
class PlatformStatDaily
{
    #[ODM\Id]
    private ?string $id = null;

    /** Date au format Y-m-d (clé métier pour l’upsert). */
    #[ODM\Field(type: 'string')]
    private string $statDate = '';

    #[ODM\Field(type: 'int')]
    private int $carpoolCount = 0;

    #[ODM\Field(type: 'float')]
    private float $platformCreditsEarned = 0.0;

    #[ODM\Field(type: 'date_immutable')]
    private ?DateTimeImmutable $recordedAt = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getStatDate(): string
    {
        return $this->statDate;
    }

    public function setStatDate(string $statDate): self
    {
        $this->statDate = $statDate;

        return $this;
    }

    public function getCarpoolCount(): int
    {
        return $this->carpoolCount;
    }

    public function setCarpoolCount(int $carpoolCount): self
    {
        $this->carpoolCount = $carpoolCount;

        return $this;
    }

    public function getPlatformCreditsEarned(): float
    {
        return $this->platformCreditsEarned;
    }

    public function setPlatformCreditsEarned(float $platformCreditsEarned): self
    {
        $this->platformCreditsEarned = $platformCreditsEarned;

        return $this;
    }

    public function getRecordedAt(): ?DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function setRecordedAt(DateTimeImmutable $recordedAt): self
    {
        $this->recordedAt = $recordedAt;

        return $this;
    }
}

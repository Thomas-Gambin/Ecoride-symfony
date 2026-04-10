<?php

declare(strict_types=1);

namespace App\Document;

use App\Repository\Mongo\UserPreferenceRepository;
use Doctrine\ODM\MongoDB\Mapping\Attribute as ODM;

#[ODM\Document(collection: 'user_preferences', repositoryClass: UserPreferenceRepository::class)]
#[ODM\Index(keys: ['userId' => 'asc'], options: ['unique' => true])]
class UserPreference
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'int')]
    private int $userId = 0;

    /**
     * Préférences clé / valeur (ex. thème, langue).
     *
     * @var array<string, mixed>
     */
    #[ODM\Field(type: 'hash')]
    private array $data = [];

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}

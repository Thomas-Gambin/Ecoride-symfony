<?php

declare(strict_types=1);

namespace App\Dto\Auth;

use App\Entity\User;

final readonly class UserProfileDto
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        public int $id,
        public string $email,
        public string $username,
        public array $roles,
        public int $credits,
        public bool $isVerified,
    ) {
    }

    public static function fromUser(User $user): self
    {
        return new self(
            id: (int) $user->getId(),
            email: (string) $user->getEmail(),
            username: (string) $user->getUsername(),
            roles: $user->getRoles(),
            credits: $user->getCredits(),
            isVerified: $user->isVerified(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'username' => $this->username,
            'roles' => $this->roles,
            'credits' => $this->credits,
            'isVerified' => $this->isVerified,
        ];
    }
}

<?php

namespace App\Infrastructure\User\Repositories;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Entities\UserEntity;
use App\Infrastructure\User\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function save(UserEntity $userEntity): void
    {
        User::create(
            [
                'provider' => $userEntity->provider,
                'provider_id' => $userEntity->providerId,
                'email' => $userEntity->email,
                'name' => $userEntity->name,
            ]
        );
    }

    public function findByClerkUserId(string $clerkUserId): ?UserEntity
    {
        $user = User::where('provider_id', $clerkUserId)->first();
        if (!$user) {
            return null;
        }
        return new UserEntity(
            $user->id,
            $user->name,
            $user->email,
            $user->provider,
            $user->provider_id,
            $user->bio,
        );
    }
}

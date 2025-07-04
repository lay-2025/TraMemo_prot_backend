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
}

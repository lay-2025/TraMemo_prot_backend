<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\UserEntity;

interface UserRepositoryInterface
{
    public function save(UserEntity $userEntity): void;
}

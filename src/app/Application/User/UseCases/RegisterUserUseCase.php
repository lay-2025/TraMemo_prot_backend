<?php

namespace App\Application\User\UseCases;

use App\Domain\User\Entities\UserEntity;
use App\Domain\User\Repositories\UserRepositoryInterface;

class RegisterUserUseCase
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * ClerkのWebhookから渡されたユーザーデータでユーザーを登録
     */
    public function handle(array $userData): void
    {
        $userEntity = UserEntity::fromClerkWebhook($userData);
        $this->userRepository->save($userEntity);
    }
}

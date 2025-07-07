<?php

namespace App\Application\User\Services;

use Illuminate\Http\Request;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Entities\UserEntity;

class AuthenticatedUserService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * リクエストから認証済みユーザー（UserEntity）を取得
     */
    public function getUserFromRequest(Request $request): ?UserEntity
    {
        $clerkUserId = $request->attributes->get('clerk_user_id');
        if (!$clerkUserId) {
            return null;
        }
        return $this->userRepository->findByClerkUserId($clerkUserId);
    }
}

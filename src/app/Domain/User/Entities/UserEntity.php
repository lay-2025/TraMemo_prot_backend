<?php

namespace App\Domain\User\Entities;

class UserEntity
{
    public string $provider = 'clerk';
    public string $providerId;
    public ?string $email;
    public string $name;

    public function __construct(
        string $providerId,
        ?string $email,
        string $name
    ) {
        $this->providerId = $providerId;
        $this->email = $email;
        $this->name = $name;
    }

    /**
     * Clerk WebhookのデータからUserエンティティを生成
     */
    public static function fromClerkWebhook(array $userData): self
    {
        // ユーザ名をusernameがある場合はそれを使用し、なければfirst_nameとlast_nameを結合
        if (!empty($userData['username'])) {
            $name = $userData['username'];
        } else {
            $name = trim(($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? ''));
        }
        return new self(
            $userData['id'],
            $userData['email_addresses'][0]['email_address'] ?? null,
            $name
        );
    }
}

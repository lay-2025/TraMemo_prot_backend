<?php

namespace App\Domain\User\Entities;

class UserEntity
{
    public int $id;
    public string $name;
    public ?string $email;
    public string $provider;
    public string $providerId;
    public string $bio;

    public function __construct(
        int $id,
        string $name,
        ?string $email,
        string $provider,
        string $providerId,
        string $bio
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->provider = $provider;
        $this->providerId = $providerId;
        $this->bio = $bio;
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
            0, // IDは0で初期化（後でDBに保存時に設定される）
            $name,
            $userData['email_addresses'][0]['email_address'] ?? null,
            'clerk',
            $userData['id'],
            ''
        );
    }
}

<?php

namespace App\Application\Travel\UseCases;

use App\Domain\Travel\Repositories\TravelRepositoryInterface;

class GetTravelListUseCase
{
    public function __construct(
        private TravelRepositoryInterface $travelRepository
    ) {}

    public function handle(array $filters, int $page = 1, int $limit = 20): array
    {
        // バリデーション
        $this->validateFilters($filters);
        $this->validatePagination($page, $limit);

        return $this->travelRepository->getTravelList($filters, $page, $limit);
    }

    private function validateFilters(array $filters): void
    {
        // ページ番号の検証
        if (isset($filters['page']) && (!is_numeric($filters['page']) || $filters['page'] < 1)) {
            throw new \InvalidArgumentException('Page must be greater than 0');
        }

        // リミットの検証
        if (isset($filters['limit']) && (!is_numeric($filters['limit']) || $filters['limit'] < 1 || $filters['limit'] > 100)) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }

        // 場所カテゴリの検証
        if (isset($filters['locationCategory']) && !in_array($filters['locationCategory'], [0, 1])) {
            throw new \InvalidArgumentException('Location category must be 0 or 1');
        }

        // 公開設定の検証
        if (isset($filters['visibility']) && !in_array($filters['visibility'], [0, 1])) {
            throw new \InvalidArgumentException('Visibility must be 0 or 1');
        }

        // ソート項目の検証
        if (isset($filters['sortBy']) && !in_array($filters['sortBy'], ['created_at', 'likes', 'title', 'date'])) {
            throw new \InvalidArgumentException('Invalid sort by parameter');
        }

        // ソート順序の検証
        if (isset($filters['sortOrder']) && !in_array($filters['sortOrder'], ['asc', 'desc'])) {
            throw new \InvalidArgumentException('Sort order must be asc or desc');
        }

        // 日付フォーマットの検証
        if (isset($filters['dateFrom']) && !$this->isValidDate($filters['dateFrom'])) {
            throw new \InvalidArgumentException('Invalid date format for dateFrom');
        }

        if (isset($filters['dateTo']) && !$this->isValidDate($filters['dateTo'])) {
            throw new \InvalidArgumentException('Invalid date format for dateTo');
        }
    }

    private function validatePagination(int $page, int $limit): void
    {
        if ($page < 1) {
            throw new \InvalidArgumentException('Page must be greater than 0');
        }

        if ($limit < 1 || $limit > 100) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}

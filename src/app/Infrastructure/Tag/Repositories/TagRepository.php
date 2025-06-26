<?php

namespace App\Infrastructure\Tag\Repositories;

use App\Domain\Tag\Repositories\TagRepositoryInterface;
use App\Infrastructure\Tag\Models\Tag;

class TagRepository implements TagRepositoryInterface
{
    public function firstOrCreate(array $data)
    {
        return Tag::firstOrCreate($data);
    }
}

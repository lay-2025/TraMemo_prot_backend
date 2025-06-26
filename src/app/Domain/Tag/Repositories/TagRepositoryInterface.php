<?php

namespace App\Domain\Tag\Repositories;

interface TagRepositoryInterface
{
    public function firstOrCreate(array $data);
}

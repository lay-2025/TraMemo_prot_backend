<?php

namespace App\Infrastructure\Photo\Repositories;

use App\Domain\Photo\Repositories\PhotoRepositoryInterface;
use App\Infrastructure\Photo\Models\Photo;

class PhotoRepository implements PhotoRepositoryInterface
{
    public function create(array $data)
    {
        return Photo::create($data);
    }
}

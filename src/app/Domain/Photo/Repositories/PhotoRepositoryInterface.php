<?php

namespace App\Domain\Photo\Repositories;

interface PhotoRepositoryInterface
{
    public function create(array $data);
}

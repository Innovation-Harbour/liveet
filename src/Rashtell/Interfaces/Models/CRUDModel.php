<?php

namespace Rashtell\Interfaces\Models;

interface CRUDModel
{
    public function createSelf(array $inputs): array;

    public function getALL(): array;

    public function getOne(int $id): array;

    public function update(array $inputs): array;

    public function delete(int $id): array;
}

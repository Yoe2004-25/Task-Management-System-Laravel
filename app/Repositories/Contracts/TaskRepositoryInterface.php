<?php

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    public function getAll(array $filters = [], array $relations = [], int $perPage = 15): LengthAwarePaginator;
    
    public function findById(int $id, array $relations = []): ?object;
    
    public function create(array $data): object;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
    
    public function getStatistics(int $userId = null): array;
    
    public function getOverdueTasks(int $userId = null): Collection;
    
    public function clearCache(): void;
}
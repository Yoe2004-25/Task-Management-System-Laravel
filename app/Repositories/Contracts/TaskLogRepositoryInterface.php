<?php


namespace App\Repositories\Contracts;

interface TaskLogRepositoryInterface
{
    public function logAction(int $taskId, int $userId, string $action, array $oldValue = null, array $newValue = null): object;
    
    public function getTaskLogs(int $taskId): array;
}
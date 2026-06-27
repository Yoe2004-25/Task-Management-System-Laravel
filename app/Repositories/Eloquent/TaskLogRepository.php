<?php

namespace App\Repositories\Eloquent;

use App\Models\TaskLog;
use App\Repositories\Contracts\TaskLogRepositoryInterface;

class TaskLogRepository implements TaskLogRepositoryInterface
{
    protected TaskLog $model;
    
    public function __construct(TaskLog $model)
    {
        $this->model = $model;
    }
    
    public function logAction(int $taskId, int $userId, string $action, array $oldValue = null, array $newValue = null): object
    {
        return $this->model->create([
            'task_id' => $taskId,
            'user_id' => $userId,
            'action' => $action,
            'old_value' => $oldValue ? json_encode($oldValue) : null,
            'new_value' => $newValue ? json_encode($newValue) : null,
        ]);
    }
    
    public function getTaskLogs(int $taskId): array
    {
        return $this->model->where('task_id', $taskId)
                         ->with('user')
                         ->orderBy('created_at', 'desc')
                         ->get()
                         ->toArray();
    }
}
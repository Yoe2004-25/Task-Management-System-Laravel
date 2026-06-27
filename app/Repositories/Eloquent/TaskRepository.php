<?php

namespace App\Repositories\Eloquent;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TaskRepository implements TaskRepositoryInterface
{
    protected Task $model;
    
    public function __construct(Task $model)
    {
        $this->model = $model;
    }
    
    public function getAll(array $filters = [], array $relations = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->query();
        
        if (!empty($relations)) {
            $query->with($relations);
        }
        
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        
        if (!empty($filters['overdue']) && $filters['overdue'] === 'true') {
            $query->where('due_date', '<', now())->where('status', '!=', 'completed');
        }
        
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);
        
        return $query->paginate($perPage);
    }
    
    public function findById(int $id, array $relations = []): ?object
    {
        $query = $this->model->query();
        
        if (!empty($relations)) {
            $query->with($relations);
        }
        
        return $query->find($id);
    }
    
    public function create(array $data): object
    {
        return DB::transaction(function () use ($data) {
            $task = $this->model->create($data);
            $this->clearCache();
            return $task;
        });
    }
    
    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data) {
            $task = $this->model->findOrFail($id);
            $updated = $task->update($data);
            $this->clearCache();
            return $updated;
        });
    }
    
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $task = $this->model->findOrFail($id);
            $deleted = $task->delete();
            $this->clearCache();
            return $deleted;
        });
    }
    
    public function getStatistics(int $userId = null): array
    {
        $query = $this->model->query();
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $total = $query->count();
        $completed = (clone $query)->where('status', 'completed')->count();
        
        return [
            'total' => $total,
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'completed' => $completed,
            'overdue' => (clone $query)->overdue()->count(),
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'by_priority' => [
                'high' => (clone $query)->where('priority', 'high')->count(),
                'medium' => (clone $query)->where('priority', 'medium')->count(),
                'low' => (clone $query)->where('priority', 'low')->count(),
            ]
        ];
    }
    
    public function getOverdueTasks(int $userId = null): Collection
    {
        $query = $this->model->overdue();
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->get();
    }
    
    public function clearCache(): void
    {
        Cache::forget('all_tasks');
        Cache::forget('tasks_statistics');
        Cache::forget('user_tasks_*');
        Cache::forget('user_tasks_statistics_*');
    }
}
<?php
// app/Services/TaskService.php

namespace App\Services;

use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Contracts\TaskLogRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TaskService
{
    protected TaskRepositoryInterface $taskRepository;
    protected TaskLogRepositoryInterface $taskLogRepository;
    
   
    protected const CACHE_TTL = 300; 
    
    public function __construct(
        TaskRepositoryInterface $taskRepository,
        TaskLogRepositoryInterface $taskLogRepository
    ) {
        $this->taskRepository = $taskRepository;
        $this->taskLogRepository = $taskLogRepository;
    }
    
    /**
     * Get tasks with caching.
     */
    public function getTasks(int $userId, bool $isAdmin, array $filters = [], array $relations = ['user']): LengthAwarePaginator
    {
        if (!$isAdmin) {
            $filters['user_id'] = $userId;
        }
        
       
        $cacheKey = $this->generateCacheKey('tasks', $userId, $filters);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters, $relations) {
            return $this->taskRepository->getAll($filters, $relations);
        });
    }
    
    /**
     * Get task by ID with caching.
     */
    public function getTaskById(int $taskId, array $relations = ['user', 'logs.user']): ?object
    {
        $cacheKey = "task_{$taskId}_" . md5(json_encode($relations));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($taskId, $relations) {
            return $this->taskRepository->findById($taskId, $relations);
        });
    }
    
    /**
     * Create task and clear cache.
     */
    public function createTask(int $userId, array $data): object
    {
        $data['user_id'] = $userId;
        
        $task = $this->taskRepository->create($data);
        
        
        $this->taskLogRepository->logAction(
            $task->id,
            $userId,
            'created',
            null,
            $task->toArray()
        );
        
       
        $this->clearRelatedCache($userId);
        
        return $task;
    }
    
    /**
     * Update task and clear cache.
     */
    public function updateTask(int $taskId, int $userId, array $data, bool $isAdmin = false): bool
    {
        $task = $this->taskRepository->findById($taskId);
        
        if (!$task) {
            return false;
        }
        
        if (!$isAdmin && $task->user_id !== $userId) {
            return false;
        }
        
        $oldValues = $task->toArray();
        
        $updated = $this->taskRepository->update($taskId, $data);
        
        if ($updated && isset($data['status']) && $data['status'] === 'completed') {
            $task = $this->taskRepository->findById($taskId);
            if ($task) {
                $task->markAsCompleted();
            }
        }
        
        
        $task = $this->taskRepository->findById($taskId);
        $this->taskLogRepository->logAction(
            $taskId,
            $userId,
            'updated',
            $oldValues,
            $task ? $task->toArray() : []
        );
        
       
        $this->clearRelatedCache($userId, $taskId);
        
        return $updated;
    }
    
    /**
     * Delete task and clear cache.
     */
    public function deleteTask(int $taskId, int $userId, bool $isAdmin): bool
    {
        if (!$isAdmin) {
            return false;
        }
        
        $deleted = $this->taskRepository->delete($taskId);
        
        if ($deleted) {
            $this->clearRelatedCache($userId, $taskId);
        }
        
        return $deleted;
    }
    
    /**
     * Get statistics with caching.
     */
    public function getStatistics(int $userId, bool $isAdmin): array
    {
        $cacheKey = $isAdmin 
            ? 'statistics_all' 
            : "statistics_user_{$userId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $isAdmin) {
            return $this->taskRepository->getStatistics($isAdmin ? null : $userId);
        });
    }
    
    /**
     * Check if user can access task.
     */
    public function canAccessTask(int $taskId, int $userId, bool $isAdmin): bool
    {
        $task = $this->taskRepository->findById($taskId);
        
        if (!$task) {
            return false;
        }
        
        return $isAdmin || $task->user_id === $userId;
    }
    
    /**
     * Generate cache key.
     */
    private function generateCacheKey(string $prefix, int $userId, array $params = []): string
    {
        $key = "{$prefix}_user_{$userId}";
        
        if (!empty($params)) {
            ksort($params);
            $key .= '_' . md5(json_encode($params));
        }
        
        return $key;
    }
    
    /**
     * Clear related cache.
     */
    private function clearRelatedCache(int $userId, ?int $taskId = null): void
    {
        try {
          
            Cache::forget('statistics_all');
            Cache::forget("statistics_user_{$userId}");
            
           
            Cache::forget("tasks_user_{$userId}");
            
            
            if ($taskId) {
                Cache::forget("task_{$taskId}");
                Cache::forget("task_{$taskId}_" . md5(json_encode(['user', 'logs.user'])));
            }
            
            Log::info('Cache cleared successfully', ['user_id' => $userId, 'task_id' => $taskId]);
            
        } catch (\Exception $e) {
            Log::warning('Failed to clear cache: ' . $e->getMessage());
        }
    }
}
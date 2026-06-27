<?php
// app/Http/Controllers/TaskApiController.php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TaskApiController extends Controller
{
    protected TaskService $taskService;
    
   
    protected const CACHE_TTL_SHORT = 300;   
    protected const CACHE_TTL_MEDIUM = 600;  
    protected const CACHE_TTL_LONG = 3600;   
    
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
        
        
        $this->middleware('admin')->only(['destroy']);
    }
    
    /**
     * Display a listing of tasks with caching.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $isAdmin = Auth::user()?->isAdmin() ?? false;
            
            $filters = $request->only(['status', 'priority', 'overdue', 'sort_by', 'sort_order']);
            
            // إنشاء مفتاح Cache فريد بناءً على المستخدم والفلتر
            $cacheKey = $this->generateCacheKey('tasks_list', $userId, $filters);
            
            // محاولة جلب البيانات من Cache
            $tasks = Cache::remember($cacheKey, self::CACHE_TTL_SHORT, function () use ($userId, $isAdmin, $filters) {
                return $this->taskService->getTasks($userId, $isAdmin, $filters);
            });
            
            return TaskResource::collection($tasks)->response();
            
        } catch (\Exception $e) {
            Log::error('Error fetching tasks: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to fetch tasks',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Store a newly created task with cache clearing.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            // إنشاء المهمة
            $task = $this->taskService->createTask($userId, $request->validated());
            
            // مسح Cache المتعلق بالمهام
            $this->clearTaskCache($userId);
            
            return response()->json([
                'message' => 'Task created successfully',
                'task' => new TaskResource($task->load('user'))
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error creating task: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to create task',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the specified task with caching.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $isAdmin = Auth::user()?->isAdmin() ?? false;
            
            // التحقق من الصلاحية أولاً
            if (!$this->taskService->canAccessTask($id, $userId, $isAdmin)) {
                return response()->json([
                    'message' => 'Unauthorized to view this task'
                ], 403);
            }
            
            // إنشاء مفتاح Cache للمهمة الفردية
            $cacheKey = $this->generateCacheKey('task_details', $id, ['user_id' => $userId]);
            
            // محاولة جلب المهمة من Cache
            $task = Cache::remember($cacheKey, self::CACHE_TTL_MEDIUM, function () use ($id) {
                return $this->taskService->getTaskById($id, ['user', 'logs.user']);
            });
            
            if (!$task) {
                return response()->json([
                    'message' => 'Task not found'
                ], 404);
            }
            
            return response()->json(new TaskResource($task));
            
        } catch (\Exception $e) {
            Log::error('Error fetching task: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to fetch task',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update the specified task with cache clearing.
     */
    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $isAdmin = Auth::user()?->isAdmin() ?? false;
            
           
            $updated = $this->taskService->updateTask($id, $userId, $request->validated(), $isAdmin);
            
            if (!$updated) {
                return response()->json([
                    'message' => 'Unauthorized to update this task'
                ], 403);
            }
            
            
            $this->clearTaskCache($userId, $id);
            
            // جلب المهمة المحدثة
            $task = $this->taskService->getTaskById($id, ['user']);
            
            return response()->json([
                'message' => 'Task updated successfully',
                'task' => new TaskResource($task)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating task: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to update task',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove the specified task with cache clearing.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $isAdmin = Auth::user()?->isAdmin() ?? false;
            
           
            $deleted = $this->taskService->deleteTask($id, $userId, $isAdmin);
            
            if (!$deleted) {
                return response()->json([
                    'message' => 'Only admins can delete tasks'
                ], 403);
            }
            
           
            $this->clearTaskCache($userId, $id);
            
            return response()->json([
                'message' => 'Task deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting task: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to delete task',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get task statistics with caching.
     */
    public function statistics(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $isAdmin = Auth::user()?->isAdmin() ?? false;
            
            
            $cacheKey = $this->generateCacheKey('statistics', $userId, ['is_admin' => $isAdmin]);
            
           
            $stats = Cache::remember($cacheKey, self::CACHE_TTL_MEDIUM, function () use ($userId, $isAdmin) {
                return $this->taskService->getStatistics($userId, $isAdmin);
            });
            
            return response()->json($stats);
            
        } catch (\Exception $e) {
            Log::error('Error fetching statistics: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get overdue tasks with caching.
     */
    public function overdue(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $isAdmin = Auth::user()?->isAdmin() ?? false;
            
            $cacheKey = $this->generateCacheKey('overdue_tasks', $userId, ['is_admin' => $isAdmin]);
            
            $tasks = Cache::remember($cacheKey, self::CACHE_TTL_SHORT, function () use ($userId, $isAdmin) {
                return $this->taskService->getTasks($userId, $isAdmin, ['overdue' => 'true']);
            });
            
            return TaskResource::collection($tasks)->response();
            
        } catch (\Exception $e) {
            Log::error('Error fetching overdue tasks: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to fetch overdue tasks',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Clear all task-related cache.
     *
     * @param int $userId
     * @param int|null $taskId
     */
    private function clearTaskCache(int $userId, ?int $taskId = null): void
    {
        try {
           
            Cache::forget('all_tasks');
            Cache::forget('tasks_statistics');
            
            
            Cache::forget("user_{$userId}_tasks");
            Cache::forget("user_{$userId}_tasks_statistics");
            Cache::forget("user_{$userId}_overdue_tasks");
            
           
            if ($taskId) {
                Cache::forget("task_details_{$taskId}");
                Cache::forget("task_{$taskId}_user_{$userId}");
            }
            
           
            $this->clearCacheByPattern('tasks_list_*');
            $this->clearCacheByPattern('statistics_*');
            
            Log::info('Task cache cleared successfully', ['user_id' => $userId, 'task_id' => $taskId]);
            
        } catch (\Exception $e) {
            Log::warning('Failed to clear task cache: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate a unique cache key.
     *
     * @param string $prefix
     * @param int $userId
     * @param array $params
     * @return string
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
     * Clear cache by pattern using Redis or cache tags.
     *
     * @param string $pattern
     */
    private function clearCacheByPattern(string $pattern): void
    {
        try {
            
            if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                $redis = Cache::getStore()->connection();
                $keys = $redis->keys($pattern);
                
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            }
            
           
            if (method_exists(Cache::getStore(), 'tags')) {
                Cache::tags(['tasks'])->flush();
            }
            
        } catch (\Exception $e) {
            Log::warning('Failed to clear cache by pattern: ' . $e->getMessage());
        }
    }
}
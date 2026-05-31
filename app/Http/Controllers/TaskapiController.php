<?php
// app/Http/Controllers/TaskController.php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskLog;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TaskapiController extends Controller
{
    
   
    public function index(Request $request)
    {
        $userId = auth()->id;          
        $isAdmin = auth()->user->isAdmin();  
        
      
        $cacheKey = $isAdmin ? 'all_tasks_' . md5($request->fullUrl()) 
                             : "user_{$userId}_tasks_" . md5($request->fullUrl());
        
       
        $tasks = Cache::remember($cacheKey, 300, function () use ($isAdmin, $userId, $request) {
           
            $query = $isAdmin 
                ? Task::with('user') 
                : Task::where('user_id', $userId)->with('user');  
            
          
            if ($request->has('status') && in_array($request->status, ['pending', 'in_progress', 'completed'])) {
                $query->where('status', $request->status);
            }
            
         
            if ($request->has('priority') && in_array($request->priority, ['low', 'medium', 'high'])) {
                $query->where('priority', $request->priority);
            }
            
         
            if ($request->has('overdue') && $request->overdue == 'true') {
                $query->where('due_date', '<', now())->where('status', '!=', 'completed');
            }
            
           
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
        
            return $query->paginate(15);
        });
        
      
        return TaskResource::collection($tasks);
    }

    
    public function store(Request $request)
    {
       
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'in:low,medium,high',
            'due_date' => 'nullable|date|after:today', 
        ]);

    
        DB::beginTransaction();
        
        try {
          
            $task = Task::create([
                'user_id' => auth()->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'priority' => $validated['priority'] ?? 'medium',
                'due_date' => $validated['due_date'] ?? null,
            ]);
            
          
            TaskLog::create([
                'task_id' => $task->id,
                'user_id' => auth()->id,
                'action' => 'created',
                'new_value' => json_encode($task->toArray())
            ]);
            
           
            $this->clearTaskCache();
           
            DB::commit();
            
            return response()->json([
                'message' => 'Task created successfully',
                'task' => new TaskResource($task->load('user'))
            ], 201);
            
        } catch (\Exception $e) {
          
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to create task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   
    public function show($id)
    {
        
        $task = Task::with(['user', 'logs.user'])->findOrFail($id);
        
       
        if (!auth()->user->isAdmin() && $task->user_id !== auth()->id) {
            return response()->json([
                'message' => 'Unauthorized to view this task'
            ], 403);
        }
        
        return new TaskResource($task);
    }

   
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        
       
        if (!auth()->user->isAdmin() && $task->user_id !== auth()->id) {
            return response()->json([
                'message' => 'Unauthorized to update this task'
            ], 403);
        }
        
       
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'priority' => 'sometimes|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);
        
        DB::beginTransaction();
        
        try {
         
            $oldValues = $task->toArray();
            
           
            $task->update($validated);
            
           
            if (isset($validated['status']) && $validated['status'] === 'completed' && !$task->completed_at) {
                $task->markAsCompleted(); 
            }
            
           
            TaskLog::create([
                'task_id' => $task->id,
                'user_id' => auth()->id,
                'action' => 'updated',
                'old_value' => json_encode($oldValues),
                'new_value' => json_encode($task->toArray())
            ]);
            
            $this->clearTaskCache();
            DB::commit();
            
            return response()->json([
                'message' => 'Task updated successfully',
                'task' => new TaskResource($task->load('user'))
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

  
    public function destroy($id)
    {
        
        if (!auth()->user->isAdmin()) {
            return response()->json([
                'message' => 'Only admins can delete tasks'
            ], 403);
        }
        
        $task = Task::findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            $task->delete();
            $this->clearTaskCache();
            DB::commit();
            
            return response()->json([
                'message' => 'Task deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete task',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
  
    public function statistics()
    {
        $userId = auth()->id;
        $isAdmin = auth()->user->isAdmin();
        
        $cacheKey = $isAdmin ? 'tasks_statistics' : "user_{$userId}_tasks_statistics";
        
        $stats = Cache::remember($cacheKey, 600, function () use ($isAdmin, $userId) {
            $query = $isAdmin ? Task::query() : Task::where('user_id', $userId);
            
            return [
                'total' => $query->count(),
                'pending' => (clone $query)->where('status', 'pending')->count(),
                'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
                'completed' => (clone $query)->where('status', 'completed')->count(),
                'overdue' => (clone $query)->overdue()->count(),
                'completion_rate' => $this->calculateCompletionRate($query),
                'by_priority' => [
                    'high' => (clone $query)->where('priority', 'high')->count(),
                    'medium' => (clone $query)->where('priority', 'medium')->count(),
                    'low' => (clone $query)->where('priority', 'low')->count(),
                ]
            ];
        });
        
        return response()->json($stats);
    }
    
   
    private function calculateCompletionRate($query)
    {
        $total = $query->count();
        if ($total === 0) return 0;
        
        $completed = (clone $query)->where('status', 'completed')->count();
        return round(($completed / $total) * 100, 2);
    }
    
   
    private function clearTaskCache()
    {
        Cache::forget('all_tasks');
        Cache::forget('tasks_statistics');
        Cache::forget("user_" . auth()->id . "_tasks");
        Cache::forget("user_" . auth()->id . "_tasks_statistics");
    }
}
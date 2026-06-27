<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    protected TaskService $taskService;
    
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }
    
    public function index(Request $request)
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()?->isAdmin() ?? false;
        
        $filters = $request->only(['status', 'priority', 'overdue', 'sort_by', 'sort_order']);
        
        $tasks = $this->taskService->getTasks($userId, $isAdmin, $filters);
        
        return view('tasks.index', compact('tasks'));
    }
    
    public function create()
    {
        return view('tasks.create');
    }
    
    public function store(StoreTaskRequest $request)
    {
        try {
            $task = $this->taskService->createTask(Auth::id(), $request->validated());
            
            return redirect()
                ->route('tasks.show', $task)
                ->with('success', 'Task created successfully');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to create task: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function show(int $id)
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()?->isAdmin() ?? false;
        
        if (!$this->taskService->canAccessTask($id, $userId, $isAdmin)) {
            abort(403, 'Unauthorized to view this task');
        }
        
        $task = $this->taskService->getTaskById($id);
        
        return view('tasks.show', compact('task'));
    }
    
    public function edit(int $id)
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()?->isAdmin() ?? false;
        
        if (!$this->taskService->canAccessTask($id, $userId, $isAdmin)) {
            abort(403, 'Unauthorized to edit this task');
        }
        
        $task = $this->taskService->getTaskById($id);
        
        return view('tasks.edit', compact('task'));
    }
    
    public function update(UpdateTaskRequest $request, int $id)
    {
        try {
            $userId = Auth::id();
            $isAdmin = Auth::user()?->isAdmin() ?? false;
            
            $updated = $this->taskService->updateTask($id, $userId, $request->validated(), $isAdmin);
            
            if (!$updated) {
                abort(403, 'Unauthorized to update this task');
            }
            
            return redirect()
                ->route('tasks.show', $id)
                ->with('success', 'Task updated successfully');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to update task: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function destroy(int $id)
    {
        try {
            $userId = Auth::id();
            $isAdmin = Auth::user()?->isAdmin() ?? false;
            
            $deleted = $this->taskService->deleteTask($id, $userId, $isAdmin);
            
            if (!$deleted) {
                abort(403, 'Only admins can delete tasks');
            }
            
            return redirect()
                ->route('tasks.index')
                ->with('success', 'Task deleted successfully');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to delete task: ' . $e->getMessage());
        }
    }
    
    public function statistics()
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()?->isAdmin() ?? false;
        
        $stats = $this->taskService->getStatistics($userId, $isAdmin);
        
        return view('tasks.statistics', compact('stats'));
    }
}
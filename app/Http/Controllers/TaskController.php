<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;  

class TaskController extends Controller
{
    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        return view('tasks.create');
    }

    /**
     * Display the specified task.
     */
    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user; 
        
        $task = Task::with(['user', 'logs.user'])->findOrFail($id);
        
       
        if (!Auth::user()?->isAdmin() && $task->user_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بعرض هذه المهمة');
        }
        
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit($id)
    {
        $task = Task::with('logs.user')->findOrFail($id);
        
        if (!Auth::user()?->isAdmin() && $task->user_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بتعديل هذه المهمة');
        }
        
        return view('tasks.edit', compact('task'));
    }

    
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        
        if (!Auth::user()?->isAdmin() && $task->user_id !== Auth::id()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
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
                'user_id' => Auth::id(),
                'action' => 'updated',
                'old_value' => json_encode($oldValues),
                'new_value' => json_encode($task->toArray())
            ]);
            
            $this->clearTaskCache();
            DB::commit();
            
            return redirect()->route('tasks.show', $task)->with('success', 'تم تحديث المهمة بنجاح');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'فشل في تحديث المهمة: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created task in storage (for web).
     */
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
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'priority' => $validated['priority'] ?? 'medium',
                'due_date' => $validated['due_date'] ?? null,
            ]);
            
            TaskLog::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'action' => 'created',
                'new_value' => json_encode($task->toArray())
            ]);
            
            $this->clearTaskCache();
            DB::commit();
            
            return redirect()->route('tasks.show', $task)->with('success', 'تم إنشاء المهمة بنجاح');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'فشل في إنشاء المهمة: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified task from storage (for web).
     */
    public function destroy($id)
    {
        if (!Auth::user()?->isAdmin()) {
            abort(403, 'المدراء فقط يمكنهم حذف المهام');
        }
        
        $task = Task::findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            $task->delete();
            $this->clearTaskCache();
            DB::commit();
            
            return redirect()->route('tasks.index')->with('success', 'تم حذف المهمة بنجاح');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'فشل في حذف المهمة');
        }
    }

    /**
     * Display a listing of tasks (for web).
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()?->isAdmin() ?? false;
        
        $query = $isAdmin ? Task::with('user') : Task::where('user_id', $userId)->with('user');
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $tasks = $query->paginate(15);
        
        return view('tasks.index', compact('tasks'));
    }
    
    /**
     * Clear task cache helper method
     */
    private function clearTaskCache()
    {
        Cache::forget('all_tasks');
        Cache::forget('tasks_statistics');
        Cache::forget("user_" . Auth::id() . "_tasks");
        Cache::forget("user_" . Auth::id() . "_tasks_statistics");
    }
}
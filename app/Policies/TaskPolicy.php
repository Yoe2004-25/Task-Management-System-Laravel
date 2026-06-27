<?php
// app/Policies/TaskPolicy.php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine if the user can view any tasks.
     */
    public function viewAny(User $user): bool
    {
        
        return $user !== null;
    }
    
    /**
     * Determine if the user can view a specific task.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->isAdmin() || $task->user_id === $user->id;
    }
    
    /**
     * Determine if the user can create tasks.
     */
    public function create(User $user): bool
    {
       
        return $user !== null;
    }
    
    /**
     * Determine if the user can update a task.
     */
    public function update(User $user, Task $task): bool
    {
       
        return $user->isAdmin() || $task->user_id === $user->id;
    }
    
    /**
     * Determine if the user can delete a task.
     */
    public function delete(User $user, Task $task): bool
    {
        
        return $user->isAdmin();
    }
    
    /**
     * Determine if the user can restore a soft-deleted task.
     */
    public function restore(User $user, Task $task): bool
    {
        
        return $user->isAdmin();
    }
    
    /**
     * Determine if the user can permanently delete a task.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        
        return $user->isAdmin();
    }
    
   
    public function viewStatistics(User $user): bool
    {
       
        return $user !== null;
    }
    
    /**
     * Determine if the user can change task status.
     */
    public function changeStatus(User $user, Task $task): bool
    {
       
        return $user->isAdmin() || $task->user_id === $user->id;
    }
    
   
    public function assign(User $user, Task $task): bool
    {
        
        return $user->isAdmin();
    }
}
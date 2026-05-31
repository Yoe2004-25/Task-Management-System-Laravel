<?php
// database/seeders/TaskSeeder.php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder; 
use Illuminate\Support\Facades\Hash;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
       
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin'
        ]);
        
       
        $user1 = User::create([
            'name' => 'Ahmed Mohamed',
            'email' => 'ahmed@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user'
        ]);
        
        $user2 = User::create([
            'name' => 'Sara Ahmed',
            'email' => 'sara@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user'
        ]);
        
      
        $tasks = [
          
            ['title' => 'Complete Laravel project', 'priority' => 'high', 'status' => 'in_progress', 'due_date' => now()->addDays(5)],
            ['title' => 'Write documentation', 'priority' => 'medium', 'status' => 'pending', 'due_date' => now()->addDays(10)],
            ['title' => 'Fix bugs in API', 'priority' => 'high', 'status' => 'pending', 'due_date' => now()->addDays(2)],
            ['title' => 'Update database schema', 'priority' => 'medium', 'status' => 'completed', 'due_date' => now()->subDays(3)],
            ['title' => 'Deploy to production', 'priority' => 'high', 'status' => 'pending', 'due_date' => now()->addDays(7)],
            
           
            ['title' => 'Design new UI', 'priority' => 'low', 'status' => 'pending', 'due_date' => now()->addDays(15)],
            ['title' => 'Write unit tests', 'priority' => 'medium', 'status' => 'in_progress', 'due_date' => now()->addDays(4)],
            ['title' => 'Optimize database queries', 'priority' => 'high', 'status' => 'pending', 'due_date' => now()->addDays(3)],
            ['title' => 'Create API documentation', 'priority' => 'low', 'status' => 'pending', 'due_date' => now()->addDays(20)],
            ['title' => 'Review pull requests', 'priority' => 'medium', 'status' => 'completed', 'due_date' => now()->subDays(1)],
        ];
        
     
        foreach ($tasks as $taskData) {
            Task::create(array_merge($taskData, ['user_id' => $user1->id]));
        }
        
       
        foreach ($tasks as $taskData) {
            Task::create(array_merge($taskData, ['user_id' => $user2->id]));
        }
        
       
        Task::create([
            'user_id' => $admin->id,
            'title' => 'Monitor system performance',
            'priority' => 'high',
            'status' => 'in_progress',
            'due_date' => now()->addDays(2)
        ]);
    }
}
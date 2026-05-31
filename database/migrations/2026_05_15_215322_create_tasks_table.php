<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();                    
            $table->foreignId('user_id')    
                  ->constrained()           
                  ->onDelete('cascade');     
            $table->string('title');         
            $table->text('description')      
                  ->nullable();             
            $table->enum('status', [       
                'pending',     
                'in_progress',  
                'completed'    
            ])->default('pending');         
            $table->enum('priority', [      
                'low',        
                'medium',     
                'high'          
            ])->default('medium');          
            $table->date('due_date')       
                  ->nullable();
            $table->timestamp('completed_at')
                  ->nullable();
            $table->timestamps();           

            $table->index(['user_id', 'status']); 
            $table->index('due_date');              
        });


         Schema::create('task_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->string('action');       
            $table->text('old_value')->nullable(); 
            $table->text('new_value')->nullable();  
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::dropIfExists('task_logs');  // حذف جدول السجل أولاً
        Schema::dropIfExists('tasks');
    }
};
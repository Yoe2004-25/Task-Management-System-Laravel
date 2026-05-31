@extends('layouts.app')

@section('title', 'تعديل المهمة')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
            <h1 class="text-xl font-bold text-white">
                <i class="fas fa-edit ml-2"></i> تعديل المهمة
            </h1>
        </div>
        
        <form action="{{ route('tasks.update', $task) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">
                    <i class="fas fa-tag ml-2 text-amber-500"></i> عنوان المهمة <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title', $task->title) }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">
                    <i class="fas fa-align-left ml-2 text-amber-500"></i> وصف المهمة
                </label>
                <textarea name="description" rows="5"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">{{ old('description', $task->description) }}</textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-chart-simple ml-2 text-amber-500"></i> الحالة
                    </label>
                    <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        <option value="pending" {{ old('status', $task->status) == 'pending' ? 'selected' : '' }}>⏳ قيد الانتظار</option>
                        <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>⚙️ قيد التنفيذ</option>
                        <option value="completed" {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>✅ مكتملة</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-flag ml-2 text-amber-500"></i> الأولوية
                    </label>
                    <select name="priority" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>🟢 منخفضة</option>
                        <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>🟡 متوسطة</option>
                        <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>🔴 عالية</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-calendar-alt ml-2 text-amber-500"></i> تاريخ الاستحقاق
                    </label>
                    <input type="date" name="due_date" value="{{ old('due_date', $task->due_date ? $task->due_date->format('Y-m-d') : '') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                </div>
            </div>
            
            <!-- Completion info if task is completed -->
            @if($task->completed_at)
                <div class="mb-6 p-3 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 text-green-700">
                        <i class="fas fa-check-circle"></i>
                        <span>هذه المهمة اكتملت في {{ $task->completed_at->format('Y-m-d H:i:s') }}</span>
                    </div>
                </div>
            @endif
            
            <div class="flex gap-3 pt-4 border-t">
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-6 rounded-lg transition transform hover:scale-105">
                    <i class="fas fa-save ml-2"></i> تحديث المهمة
                </button>
                <a href="{{ route('tasks.show', $task) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-lg transition">
                    <i class="fas fa-eye ml-2"></i> عرض المهمة
                </a>
                <a href="{{ route('tasks.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-lg transition">
                    <i class="fas fa-arrow-left ml-2"></i> العودة للقائمة
                </a>
            </div>
        </form>
    </div>
    
    <!-- Logs Card (for admins) -->
    @if(auth()->user()->isAdmin() && $task->logs->count() > 0)
        <div class="mt-6 bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
                <i class="fas fa-history text-gray-500 ml-2"></i> سجل التعديلات
            </h3>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($task->logs as $log)
                    <div class="text-sm p-2 border-b border-gray-100">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-indigo-600">{{ $log->user->name }}</span>
                            <span class="text-gray-400 text-xs">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-gray-600 mt-1">
                            @switch($log->action)
                                @case('created')
                                    <i class="fas fa-plus-circle text-green-500 ml-1"></i> قام بإنشاء المهمة
                                    @break
                                @case('updated')
                                    <i class="fas fa-edit text-blue-500 ml-1"></i> قام بتعديل المهمة
                                    @break
                            @endswitch
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
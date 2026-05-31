@extends('layouts.app')

@section('title', $task->title)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header with status -->
        <div class="px-6 py-4 border-b flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-bold text-gray-800">{{ $task->title }}</h1>
                <span class="text-sm px-3 py-1 rounded-full status-{{ $task->status }}">
                    @switch($task->status)
                        @case('pending') ⏳ قيد الانتظار @break
                        @case('in_progress') ⚙️ قيد التنفيذ @break
                        @case('completed') ✅ مكتملة @break
                    @endswitch
                </span>
                <span class="text-sm px-3 py-1 rounded-full priority-{{ $task->priority }}">
                    @switch($task->priority)
                        @case('low') 🟢 أولوية منخفضة @break
                        @case('medium') 🟡 أولوية متوسطة @break
                        @case('high') 🔴 أولوية عالية @break
                    @endswitch
                </span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('tasks.edit', $task) }}" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-edit ml-1"></i> تعديل
                </a>
                @if(auth()->user()->isAdmin())
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline"
                          onsubmit="return confirm('هل أنت متأكد من حذف هذه المهمة؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-100 hover:bg-red-200 text-red-700 font-medium py-2 px-4 rounded-lg transition">
                            <i class="fas fa-trash ml-1"></i> حذف
                        </button>
                    </form>
                @endif
            </div>
        </div>
        
        <!-- Task details -->
        <div class="p-6">
            <!-- Description -->
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-700 mb-3 border-b pb-2">
                    <i class="fas fa-align-left text-indigo-500 ml-2"></i> الوصف
                </h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    @if($task->description)
                        <p class="text-gray-700 leading-relaxed">{{ $task->description }}</p>
                    @else
                        <p class="text-gray-400 italic">لا يوجد وصف لهذه المهمة</p>
                    @endif
                </div>
            </div>
            
            <!-- Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-blue-700 mb-2">
                        <i class="fas fa-calendar-alt"></i>
                        <span class="font-medium">تاريخ الاستحقاق</span>
                    </div>
                    <p class="text-gray-700">{{ $task->due_date ? $task->due_date->format('Y-m-d') : 'غير محدد' }}</p>
                    @if($task->due_date && $task->due_date < now() && $task->status != 'completed')
                        <p class="text-red-500 text-sm mt-1">⚠️ هذه المهمة متأخرة!</p>
                    @endif
                </div>
                
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-green-700 mb-2">
                        <i class="fas fa-clock"></i>
                        <span class="font-medium">تاريخ الإنشاء</span>
                    </div>
                    <p class="text-gray-700">{{ $task->created_at->format('Y-m-d H:i:s') }}</p>
                    <p class="text-gray-500 text-sm">{{ $task->created_at->diffForHumans() }}</p>
                </div>
                
                @if($task->completed_at)
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="flex items-center gap-2 text-purple-700 mb-2">
                            <i class="fas fa-check-circle"></i>
                            <span class="font-medium">تاريخ الإكمال</span>
                        </div>
                        <p class="text-gray-700">{{ $task->completed_at->format('Y-m-d H:i:s') }}</p>
                        <p class="text-gray-500 text-sm">{{ $task->completed_at->diffForHumans() }}</p>
                    </div>
                @endif
                
                @if(auth()->user()->isAdmin() && $task->user)
                    <div class="bg-gray-100 rounded-lg p-4">
                        <div class="flex items-center gap-2 text-gray-700 mb-2">
                            <i class="fas fa-user"></i>
                            <span class="font-medium">المستخدم المالك</span>
                        </div>
                        <p class="text-gray-700">{{ $task->user->name }}</p>
                        <p class="text-gray-500 text-sm">{{ $task->user->email }}</p>
                    </div>
                @endif
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3 pt-4 border-t">
                @if($task->status != 'completed')
                    <form action="{{ route('tasks.update', $task) }}" method="POST" class="inline">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="completed">
                        <input type="hidden" name="title" value="{{ $task->title }}">
                        <input type="hidden" name="description" value="{{ $task->description }}">
                        <input type="hidden" name="priority" value="{{ $task->priority }}">
                        <input type="hidden" name="due_date" value="{{ $task->due_date }}">
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-lg transition">
                            <i class="fas fa-check-circle ml-1"></i> وضع كمكتملة
                        </button>
                    </form>
                @endif
                
                <a href="{{ route('tasks.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-arrow-left ml-1"></i> العودة إلى القائمة
                </a>
            </div>
        </div>
    </div>
    
    <!-- Logs for admins -->
    @if(auth()->user()->isAdmin() && $task->logs->count() > 0)
        <div class="mt-6 bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
                <i class="fas fa-history text-gray-500 ml-2"></i> سجل التعديلات ({{ $task->logs->count() }})
            </h3>
            <div class="space-y-3">
                @foreach($task->logs->reverse() as $log)
                    <div class="text-sm p-3 border border-gray-100 rounded-lg hover:bg-gray-50 transition">
                        <div class="flex justify-between items-center flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <i class="fas fa-user text-indigo-600 text-sm"></i>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-800">{{ $log->user->name }}</span>
                                    <span class="text-gray-500 text-xs">({{ $log->user->email }})</span>
                                </div>
                            </div>
                            <span class="text-gray-400 text-xs">{{ $log->created_at->format('Y-m-d H:i:s') }}</span>
                        </div>
                        <div class="mt-2 mr-10">
                            @switch($log->action)
                                @case('created')
                                    <div class="text-green-600">
                                        <i class="fas fa-plus-circle ml-1"></i> قام بإنشاء هذه المهمة
                                    </div>
                                    @break
                                @case('updated')
                                    <div class="text-blue-600">
                                        <i class="fas fa-edit ml-1"></i> قام بتعديل المهمة
                                    </div>
                                    @break
                            @endswitch
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
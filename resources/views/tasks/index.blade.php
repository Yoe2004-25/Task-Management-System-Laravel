@extends('layouts.app')

@section('title', 'جميع المهام')

@section('content')
<div class="bg-white rounded-xl shadow-lg p-6">
    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-list-check text-indigo-600 ml-2"></i> جميع المهام
        </h1>
        <a href="{{ route('tasks.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition flex items-center gap-2">
            <i class="fas fa-plus"></i>
            <span>مهمة جديدة</span>
        </a>
    </div>
    
    <!-- Filters -->
    <div class="bg-gray-50 rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('tasks.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">الكل</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ قيد الانتظار</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>⚙️ قيد التنفيذ</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>✅ مكتملة</option>
                </select>
            </div>
            
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">الأولوية</label>
                <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">الكل</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>🟢 منخفضة</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>🟡 متوسطة</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>🔴 عالية</option>
                </select>
            </div>
            
            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">ترتيب حسب</label>
                <select name="sort_by" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>تاريخ الإنشاء</option>
                    <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>تاريخ الاستحقاق</option>
                    <option value="priority" {{ request('sort_by') == 'priority' ? 'selected' : '' }}>الأولوية</option>
                    <option value="title" {{ request('sort_by') == 'title' ? 'selected' : '' }}>العنوان</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الاتجاه</label>
                <select name="sort_order" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>تنازلي</option>
                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>تصاعدي</option>
                </select>
            </div>
            
            <div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-filter ml-1"></i> تصفية
                </button>
                <a href="{{ route('tasks.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition inline-block">
                    <i class="fas fa-undo ml-1"></i> إعادة تعيين
                </a>
            </div>
        </form>
    </div>
    
    <!-- Tasks List -->
    @if($tasks->count() > 0)
        <div class="space-y-3">
            @foreach($tasks as $task)
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition bg-white">
                    <div class="flex flex-wrap justify-between items-start gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <h3 class="font-bold text-lg text-gray-800">{{ $task->title }}</h3>
                                <span class="text-xs px-2 py-1 rounded-full status-{{ $task->status }}">
                                    @switch($task->status)
                                        @case('pending') ⏳ قيد الانتظار @break
                                        @case('in_progress') ⚙️ قيد التنفيذ @break
                                        @case('completed') ✅ مكتملة @break
                                    @endswitch
                                </span>
                                <span class="text-xs px-2 py-1 rounded-full priority-{{ $task->priority }}">
                                    @switch($task->priority)
                                        @case('low') 🟢 منخفضة @break
                                        @case('medium') 🟡 متوسطة @break
                                        @case('high') 🔴 عالية @break
                                    @endswitch
                                </span>
                                @if($task->due_date && $task->due_date < now() && $task->status != 'completed')
                                    <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-600">
                                        ⚠️ متأخرة
                                    </span>
                                @endif
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-2">{{ Str::limit($task->description, 150) }}</p>
                            
                            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
                                <span><i class="fas fa-calendar ml-1"></i> تاريخ الاستحقاق: {{ $task->due_date ?? 'غير محدد' }}</span>
                                <span><i class="fas fa-clock ml-1"></i> تاريخ الإنشاء: {{ $task->created_at->format('Y-m-d') }}</span>
                                @if(auth()->user()->isAdmin() && $task->user)
                                    <span><i class="fas fa-user ml-1"></i> المستخدم: {{ $task->user->name }}</span>
                                @endif
                                @if($task->completed_at)
                                    <span><i class="fas fa-check-circle ml-1 text-green-500"></i> اكتملت في: {{ $task->completed_at->format('Y-m-d H:i') }}</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex gap-2">
                            <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 hover:text-blue-800 p-2">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('tasks.edit', $task) }}" class="text-indigo-600 hover:text-indigo-800 p-2">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if(auth()->user()->isAdmin())
                                <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذه المهمة؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 p-2">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="mt-6">
            {{ $tasks->appends(request()->query())->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
            <p class="text-gray-500 text-lg">لا توجد مهام لعرضها</p>
            <a href="{{ route('tasks.create') }}" class="inline-block mt-4 text-indigo-600 hover:text-indigo-800">
                <i class="fas fa-plus ml-1"></i> أضف مهمة جديدة
            </a>
        </div>
    @endif
</div>
@endsection
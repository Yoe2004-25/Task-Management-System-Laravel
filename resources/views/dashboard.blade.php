@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Sidebar -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Stats Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
                <i class="fas fa-chart-line text-indigo-600 ml-2"></i> الإحصائيات
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg">
                    <span><i class="fas fa-tasks text-gray-500 ml-2"></i> إجمالي المهام</span>
                    <span class="font-bold text-xl text-indigo-600">{{ $statistics['total'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center p-2 bg-yellow-50 rounded-lg">
                    <span><i class="fas fa-clock text-yellow-500 ml-2"></i> قيد الانتظار</span>
                    <span class="font-bold text-xl text-yellow-600">{{ $statistics['pending'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center p-2 bg-blue-50 rounded-lg">
                    <span><i class="fas fa-spinner text-blue-500 ml-2"></i> قيد التنفيذ</span>
                    <span class="font-bold text-xl text-blue-600">{{ $statistics['in_progress'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center p-2 bg-green-50 rounded-lg">
                    <span><i class="fas fa-check-circle text-green-500 ml-2"></i> مكتملة</span>
                    <span class="font-bold text-xl text-green-600">{{ $statistics['completed'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center p-2 bg-red-50 rounded-lg">
                    <span><i class="fas fa-exclamation-triangle text-red-500 ml-2"></i> متأخرة</span>
                    <span class="font-bold text-xl text-red-600">{{ $statistics['overdue'] ?? 0 }}</span>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mt-4 pt-4 border-t">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                    <span>نسبة الإنجاز</span>
                    <span>{{ $statistics['completion_rate'] ?? 0 }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-500 to-green-500 h-3 rounded-full transition-all duration-500"
                         style="width: {{ $statistics['completion_rate'] ?? 0 }}%"></div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
                <i class="fas fa-bolt text-yellow-500 ml-2"></i> إجراءات سريعة
            </h3>
            <div class="space-y-3">
                <a href="{{ route('tasks.create') }}" class="flex items-center justify-center w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition">
                    <i class="fas fa-plus ml-2"></i> مهمة جديدة
                </a>
                <a href="{{ route('tasks.index') }}" class="flex items-center justify-center w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded-lg transition">
                    <i class="fas fa-list ml-2"></i> عرض جميع المهام
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Content - Recent Tasks -->
    <div class="lg:col-span-3 space-y-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-6 border-b pb-3">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-history text-indigo-600 ml-2"></i> أحدث المهام
                </h3>
                <a href="{{ route('tasks.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">عرض الكل <i class="fas fa-arrow-left mr-1"></i></a>
            </div>
            
            @if(isset($recentTasks) && count($recentTasks) > 0)
                <div class="space-y-3">
                    @foreach($recentTasks as $task)
                        <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                                        <h4 class="font-bold text-gray-800">{{ $task->title }}</h4>
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
                                    </div>
                                    <p class="text-gray-600 text-sm line-clamp-2">{{ Str::limit($task->description, 100) }}</p>
                                    <div class="flex items-center gap-4 mt-3 text-xs text-gray-500">
                                        <span><i class="fas fa-calendar ml-1"></i> {{ $task->due_date ?? 'غير محدد' }}</span>
                                        @if($task->user && auth()->user()->isAdmin())
                                            <span><i class="fas fa-user ml-1"></i> {{ $task->user->name }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('tasks.edit', $task) }}" class="text-indigo-600 hover:text-indigo-800">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(auth()->user()->isAdmin())
                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline"
                                              onsubmit="return confirm('هل أنت متأكد من حذف هذه المهمة؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-tasks text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500">لا توجد مهام حالياً</p>
                    <a href="{{ route('tasks.create') }}" class="inline-block mt-4 text-indigo-600 hover:text-indigo-800">
                        <i class="fas fa-plus ml-1"></i> أضف أول مهمة لك
                    </a>
                </div>
            @endif
        </div>
        
        <!-- Priority Distribution -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">
                <i class="fas fa-chart-pie text-indigo-600 ml-2"></i> توزيع المهام حسب الأولوية
            </h3>
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-3 bg-red-50 rounded-xl">
                    <div class="text-2xl font-bold text-red-600">{{ $statistics['by_priority']['high'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">عالية 🔴</div>
                </div>
                <div class="text-center p-3 bg-orange-50 rounded-xl">
                    <div class="text-2xl font-bold text-orange-600">{{ $statistics['by_priority']['medium'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">متوسطة 🟡</div>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-xl">
                    <div class="text-2xl font-bold text-green-600">{{ $statistics['by_priority']['low'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">منخفضة 🟢</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
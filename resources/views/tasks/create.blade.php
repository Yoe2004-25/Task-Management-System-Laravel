@extends('layouts.app')

@section('title', 'إنشاء مهمة جديدة')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
            <h1 class="text-xl font-bold text-white">
                <i class="fas fa-plus-circle ml-2"></i> إنشاء مهمة جديدة
            </h1>
        </div>
        
        <form action="{{ route('tasks.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">
                    <i class="fas fa-tag ml-2 text-indigo-500"></i> عنوان المهمة <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title') }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="أدخل عنوان المهمة">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">
                    <i class="fas fa-align-left ml-2 text-indigo-500"></i> وصف المهمة
                </label>
                <textarea name="description" rows="5"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="أدخل وصفاً مفصلاً للمهمة...">{{ old('description') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">يمكنك إضافة تفاصيل إضافية عن المهمة هنا</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-flag ml-2 text-indigo-500"></i> الأولوية
                    </label>
                    <select name="priority" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>🟢 منخفضة</option>
                        <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>🟡 متوسطة</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>🔴 عالية</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-calendar-alt ml-2 text-indigo-500"></i> تاريخ الاستحقاق
                    </label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">اختر التاريخ الذي تريد إكمال المهمة فيه</p>
                </div>
            </div>
            
            <div class="flex gap-3 pt-4 border-t">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition transform hover:scale-105">
                    <i class="fas fa-save ml-2"></i> حفظ المهمة
                </button>
                <a href="{{ route('tasks.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-lg transition">
                    <i class="fas fa-times ml-2"></i> إلغاء
                </a>
            </div>
        </form>
    </div>
    
    <!-- Tips Card -->
    <div class="mt-6 bg-blue-50 rounded-xl p-4 border border-blue-200">
        <div class="flex items-start gap-3">
            <i class="fas fa-lightbulb text-blue-500 text-xl mt-1"></i>
            <div>
                <h4 class="font-bold text-blue-800">نصائح لكتابة مهام فعالة</h4>
                <ul class="text-sm text-blue-700 mt-2 space-y-1">
                    <li>• استخدم عنواناً واضحاً ومحدداً للمهمة</li>
                    <li>• قسم المهمة الكبيرة إلى مهام فرعية أصغر</li>
                    <li>• حدد أولوياتك بشكل صحيح لتنظيم وقتك</li>
                    <li>• لا تنسى تحديد تاريخ استحقاق واقعي</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'تسجيل الدخول')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full card-hover">
        <div class="text-center mb-8">
            <div class="bg-indigo-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-tasks text-indigo-600 text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">مرحباً بعودتك</h1>
            <p class="text-gray-500 mt-2">سجل دخولك لإدارة مهامك</p>
        </div>
        
        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">
                    <i class="fas fa-envelope ml-2 text-gray-400"></i> البريد الإلكتروني
                </label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="example@email.com">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">
                    <i class="fas fa-lock ml-2 text-gray-400"></i> كلمة المرور
                </label>
                <input type="password" name="password" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="********">
            </div>
            
            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="mr-2 text-gray-600">تذكرني</span>
                </label>
            </div>
            
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-105">
                <i class="fas fa-sign-in-alt ml-2"></i> تسجيل الدخول
            </button>
        </form>
        
        <p class="text-center mt-6 text-gray-600">
            ليس لديك حساب؟ 
            <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">إنشاء حساب جديد</a>
        </p>
        
        <!-- بيانات تجريبية للمطور -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-400 text-center mb-2">بيانات تجريبية</p>
            <div class="text-xs text-gray-500 text-center space-y-1">
                <p>مدير: admin@example.com / password123</p>
                <p>مستخدم: ahmed@example.com / password123</p>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'إنشاء حساب جديد')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full card-hover">
        <div class="text-center mb-8">
            <div class="bg-indigo-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-plus text-indigo-600 text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">إنشاء حساب جديد</h1>
            <p class="text-gray-500 mt-2">قم بإنشاء حساب للبدء في إدارة مهامك</p>
        </div>
        
        <form method="POST" action="{{ route('register') }}">
            @csrf
            
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">
                    <i class="fas fa-user ml-2 text-gray-400"></i> الاسم الكامل
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="أحمد محمد">
            </div>
            
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
                    placeholder="******** (8 أحرف على الأقل)">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">
                    <i class="fas fa-lock ml-2 text-gray-400"></i> تأكيد كلمة المرور
                </label>
                <input type="password" name="password_confirmation" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="تأكيد كلمة المرور">
            </div>
            
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-105">
                <i class="fas fa-user-plus ml-2"></i> إنشاء حساب
            </button>
        </form>
        
        <p class="text-center mt-6 text-gray-600">
            لديك حساب بالفعل؟ 
            <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">تسجيل الدخول</a>
        </p>
    </div>
</div>
@endsection
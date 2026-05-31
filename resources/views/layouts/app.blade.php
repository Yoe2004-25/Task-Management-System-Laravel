<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نظام إدارة المهام')</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        [dir="rtl"] {
            text-align: right;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .status-pending { background-color: #fef3c7; color: #d97706; }
        .status-in_progress { background-color: #dbeafe; color: #2563eb; }
        .status-completed { background-color: #d1fae5; color: #059669; }
        .priority-low { background-color: #d1fae5; color: #059669; }
        .priority-medium { background-color: #fed7aa; color: #ea580c; }
        .priority-high { background-color: #fee2e2; color: #dc2626; }
        .sidebar-item:hover {
            background-color: #f3f4f6;
            transform: translateX(-5px);
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    
    @stack('styles')
</head>
<body class="font-sans antialiased">
    
    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3 space-x-reverse">
                    <i class="fas fa-tasks text-indigo-600 text-2xl"></i>
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-800 hover:text-indigo-600 transition">
                        مدير المهام
                    </a>
                </div>
                
                <div class="flex items-center space-x-4 space-x-reverse">
                    <div class="relative group">
                        <button class="flex items-center space-x-2 space-x-reverse text-gray-700 hover:text-indigo-600">
                            <span class="font-medium">{{ auth()->user()->name }}</span>
                            <i class="fas fa-user-circle text-2xl"></i>
                        </button>
                        <div class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-xl hidden group-hover:block z-50">
                            <div class="px-4 py-3 border-b">
                                <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                                @if(auth()->user()->isAdmin())
                                    <span class="inline-block mt-1 text-xs bg-red-100 text-red-600 px-2 py-1 rounded-full">مدير النظام</span>
                                @endif
                            </div>
                            <div class="py-2">
                                <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-tachometer-alt ml-2"></i> لوحة التحكم
                                </a>
                                <a href="{{ route('tasks.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-list-check ml-2"></i> جميع المهام
                                </a>
                                <form method="POST" action="{{ route('logout') }}" class="block">
                                    @csrf
                                    <button type="submit" class="w-full text-right px-4 py-2 text-red-600 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt ml-2"></i> تسجيل خروج
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-8 fade-in">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center justify-between">
                    <span><i class="fas fa-check-circle ml-2"></i> {{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-green-700">&times;</button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between">
                    <span><i class="fas fa-exclamation-circle ml-2"></i> {{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-red-700">&times;</button>
                </div>
            @endif
            
            @if($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @yield('content')
        </div>
    </main>

    <script>
        // إغلاق الرسائل بعد 5 ثواني
        setTimeout(() => {
            document.querySelectorAll('.bg-green-100, .bg-red-100').forEach(el => {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            });
        }, 5000);
    </script>
    
    @stack('scripts')
</body>
</html>
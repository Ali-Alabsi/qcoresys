<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إنشاء حساب جديد — QCoreSys</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cairo:500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative flex min-h-screen items-center justify-center overflow-x-hidden bg-slate-950 font-sans selection:bg-cyan-500 selection:text-white p-4 sm:p-6 lg:p-8">

    <!-- خلفية وتأثيرات الإضاءة المحيطة -->
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -start-40 h-96 w-96 rounded-full bg-cyan-500/20 blur-[128px]"></div>
        <div class="absolute -bottom-40 -end-40 h-96 w-96 rounded-full bg-blue-600/20 blur-[128px]"></div>
        <div class="absolute top-1/2 start-1/2 -translate-x-1/2 -translate-y-1/2 h-[550px] w-[550px] rounded-full bg-indigo-500/10 blur-[140px]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#1e293b0a_1px,transparent_1px),linear-gradient(to_bottom,#1e293b0a_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_50%,#000_70%,transparent_100%)]"></div>
    </div>

    <!-- بطاقة التسجيل الرئيسية -->
    <div class="relative w-full max-w-xl my-6">

        <div class="absolute -inset-0.5 rounded-3xl bg-gradient-to-b from-cyan-500/30 via-slate-700/20 to-transparent blur-sm"></div>

        <div class="relative rounded-3xl border border-slate-800/80 bg-slate-900/85 p-8 shadow-2xl backdrop-blur-xl sm:p-10">

            <!-- الترويسة والشعار -->
            <div class="mb-8 text-center">
                <a href="/" class="inline-flex items-center gap-2 group mb-3 focus:outline-none">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25 transition-transform duration-300 group-hover:scale-105">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span class="text-2xl font-bold tracking-tight text-white">QCore<span class="text-cyan-400">Sys</span></span>
                </a>
                <h2 class="text-xl font-semibold text-slate-100">تسجيل حساب عميل جديد</h2>
                <p class="mt-1 text-sm text-slate-400">أدخل بياناتك للانضمام إلى منصة الخدمات السحابية والأنظمة</p>
            </div>

            <!-- نموذج التسجيل عبر Alpine.js -->
            <form method="POST" action="{{ route('portal.register.store') }}" class="space-y-4" x-data="{ type: '{{ old('customer_type', 'INDIVIDUAL') }}' }">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">

                    <!-- نوع الحساب -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            نوع الحساب
                        </label>
                        <select
                            name="customer_type"
                            x-model="type"
                            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 shadow-inner transition duration-200 focus:border-cyan-500 focus:bg-slate-950 focus:outline-none focus:ring-4 focus:ring-cyan-500/15"
                        >
                            <option value="INDIVIDUAL" class="bg-slate-900 text-slate-100">فرد / عميل مستقل</option>
                            <option value="COMPANY" class="bg-slate-900 text-slate-100">مؤسسة / شركة تجارية</option>
                        </select>
                    </div>

                    <!-- الاسم الكامل -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            الاسم الكامل
                        </label>
                        <input
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            required
                            placeholder="محمد علي"
                            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 placeholder-slate-500 shadow-inner transition duration-200 focus:border-cyan-500 focus:bg-slate-950 focus:outline-none focus:ring-4 focus:ring-cyan-500/15 @error('name') border-rose-500/80 @enderror"
                        >
                        @error('name')
                            <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- اسم الشركة (يظهر فقط عند اختيار شركة) -->
                    <div class="sm:col-span-2" x-show="type === 'COMPANY'" x-transition x-cloak>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            اسم الشركة أو المؤسسة
                        </label>
                        <input
                            name="company_name"
                            type="text"
                            value="{{ old('company_name') }}"
                            placeholder="شركة الحلول المتكاملة"
                            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 placeholder-slate-500 shadow-inner transition duration-200 focus:border-cyan-500 focus:bg-slate-950 focus:outline-none focus:ring-4 focus:ring-cyan-500/15"
                        >
                        @error('company_name')
                            <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- البريد الإلكتروني -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            البريد الإلكتروني
                        </label>
                        <input
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            placeholder="name@company.com"
                            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 placeholder-slate-500 shadow-inner transition duration-200 focus:border-cyan-500 focus:bg-slate-950 focus:outline-none focus:ring-4 focus:ring-cyan-500/15 @error('email') border-rose-500/80 @enderror"
                        >
                        @error('email')
                            <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- رقم الهاتف -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            رقم الهاتف / الواتساب
                        </label>
                        <input
                            name="phone"
                            type="tel"
                            value="{{ old('phone') }}"
                            placeholder="770000000"
                            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 placeholder-slate-500 shadow-inner transition duration-200 focus:border-cyan-500 focus:bg-slate-950 focus:outline-none focus:ring-4 focus:ring-cyan-500/15 @error('phone') border-rose-500/80 @enderror"
                        >
                        @error('phone')
                            <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- كلمة المرور -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            كلمة المرور
                        </label>
                        <input
                            name="password"
                            type="password"
                            required
                            placeholder="••••••••"
                            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 placeholder-slate-500 shadow-inner transition duration-200 focus:border-cyan-500 focus:bg-slate-950 focus:outline-none focus:ring-4 focus:ring-cyan-500/15 @error('password') border-rose-500/80 @enderror"
                        >
                        @error('password')
                            <p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- تأكيد كلمة المرور -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                            تأكيد كلمة المرور
                        </label>
                        <input
                            name="password_confirmation"
                            type="password"
                            required
                            placeholder="••••••••"
                            class="w-full rounded-xl border border-slate-700/80 bg-slate-950/60 px-4 py-3 text-sm text-slate-100 placeholder-slate-500 shadow-inner transition duration-200 focus:border-cyan-500 focus:bg-slate-950 focus:outline-none focus:ring-4 focus:ring-cyan-500/15"
                        >
                    </div>

                </div>

                <!-- زر التسجيل الجديد مع أيقونة الزائد (+) -->
                <button
                    type="submit"
                    class="group relative mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition-all duration-300 hover:from-cyan-400 hover:to-blue-500 hover:shadow-cyan-500/35 focus:outline-none focus:ring-4 focus:ring-cyan-500/25 active:scale-[0.99]"
                >
                    <svg class="h-5 w-5 transition-transform duration-200 group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>إنشاء الحساب</span>
                </button>
            </form>

            <!-- رابط تسجيل الدخول -->
            <div class="mt-8 border-t border-slate-800/80 pt-6 text-center">
                <p class="text-sm text-slate-400">
                    لديك حساب بالفعل؟
                    <a href="{{ route('portal.login') }}" class="font-semibold text-cyan-400 hover:text-cyan-300 transition-colors hover:underline focus:outline-none">
                        تسجيل الدخول
                    </a>
                </p>
            </div>

        </div>

        <div class="mt-6 flex items-center justify-center gap-2 text-xs text-slate-500">
            <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>اتصال مشفر وآمن بمعيار 256-bit</span>
        </div>

    </div>

    @include('partials.app-flash')
</body>
</html>

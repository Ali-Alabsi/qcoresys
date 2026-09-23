<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول — QCoreSys</title>

    <!-- خط Cairo المعتمد لكامل الواجهة -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            font-family: 'Cairo', system-ui, -apple-system, sans-serif !important;
        }
    </style>
</head>
<body class="min-h-screen bg-[#060a12] text-slate-100 selection:bg-cyan-500 selection:text-black">

    <div class="flex min-h-screen">

        <!-- الجانب الأيمن: لوحة هوية النظام والأنشطة التقنية (يظهر في الشاشات المتوسطة والكبيرة) -->
        <div class="relative hidden lg:flex lg:w-1/2 flex-col justify-between overflow-hidden border-l border-slate-800/80 bg-gradient-to-br from-[#0a1222] via-[#060d1a] to-[#040810] p-12 xl:p-16">

            <!-- خلفيات توهج وشبكة رقمية تفاعلية -->
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-cyan-500/15 blur-[120px]"></div>
                <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-blue-600/15 blur-[120px]"></div>
                <div class="absolute inset-0 bg-[radial-gradient(#1e293b_1px,transparent_1px)] [background-size:24px_24px] opacity-30"></div>
            </div>

            <!-- الترويسة والشعار -->
            <div class="relative z-10 flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 shadow-xl shadow-cyan-500/20 ring-1 ring-white/20">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <span class="text-2xl font-black tracking-wider text-white">QCore<span class="text-cyan-400">Sys</span></span>
                    <p class="text-xs font-semibold tracking-widest text-slate-400">ENTERPRISE PLATFORM</p>
                </div>
            </div>

            <!-- محتوى تسويقي وتقني مدمج -->
            <div class="relative z-10 space-y-6 my-auto max-w-lg">
                <div class="inline-flex items-center gap-2 rounded-full border border-cyan-500/30 bg-cyan-500/10 px-3.5 py-1.5 text-xs font-semibold text-cyan-300">
                    <span class="h-2 w-2 rounded-full bg-cyan-400 animate-pulse"></span>
                    نظام آمن ومحدث على مدار الساعة
                </div>
                <h1 class="text-3xl font-extrabold leading-snug tracking-tight text-white xl:text-4xl">
                    بوابة إدارة الأعمال والأنظمة السحابية المركزية
                </h1>
                <p class="text-sm font-medium leading-relaxed text-slate-400">
                    وصول مشفر وموثق للخدمات المؤسسية، إدارة العقود، متابعة الفواتير والدعم الفني المباشر في بيئة عمل سريعة وعالية الموثوقية.
                </p>

                <!-- بطاقة إحصائيات سريعة للثقة -->
                <div class="grid grid-cols-2 gap-4 pt-4">
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4 backdrop-blur-md">
                        <div class="text-xl font-bold text-cyan-400 font-mono">99.9%</div>
                        <div class="text-xs text-slate-400 mt-1">نسبة استقرار وجاهزية الخدمة</div>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4 backdrop-blur-md">
                        <div class="text-xl font-bold text-blue-400 font-mono">256-bit</div>
                        <div class="text-xs text-slate-400 mt-1">تشفير القنوات والبيانات</div>
                    </div>
                </div>
            </div>

            <!-- حقوق التذييل في الجانب البصري -->
            <div class="relative z-10 text-xs text-slate-500 flex items-center justify-between">
                <span>© 2026 QCoreSys. كافة الحقوق محفوظة.</span>
                <span class="font-mono text-slate-600">v2.4.0-Core</span>
            </div>

        </div>

        <!-- الجانب الأيسر: نموذج تسجيل الدخول -->
        <div class="flex w-full lg:w-1/2 flex-col justify-center px-6 py-12 sm:px-12 xl:px-20 relative">

            <!-- خلفية ناعمة للشاشات الصغيرة -->
            <div class="lg:hidden absolute top-0 right-0 h-72 w-72 rounded-full bg-cyan-500/10 blur-[100px] pointer-events-none"></div>

            <div class="mx-auto w-full max-w-md">

                <!-- شعار الهيدر في الشاشات الصغيرة فقط -->
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/20">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span class="text-xl font-black text-white">QCore<span class="text-cyan-400">Sys</span></span>
                </div>

                <div class="mb-8">
                    <h2 class="text-2xl sm:text-3xl font-bold text-white">تسجيل الدخول</h2>
                    <p class="mt-2 text-sm text-slate-400">أدخل بيانات الاعتماد المعتمدة الخاصة بك للدخول للبوابة</p>
                </div>

                <form method="POST" action="{{ route('portal.login.store') }}" class="space-y-5">
                    @csrf

                    <!-- حقل البريد الإلكتروني -->
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                            البريد الإلكتروني
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-slate-500">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                placeholder="name@company.com"
                                class="w-full rounded-2xl border border-slate-800 bg-[#0c1322] py-3.5 ps-12 pe-4 text-sm text-slate-100 placeholder-slate-500 transition duration-200 hover:border-slate-700 focus:border-cyan-500 focus:bg-[#0c1322] focus:outline-none focus:ring-4 focus:ring-cyan-500/10 @error('email') border-rose-500/80 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                            >
                        </div>
                        @error('email')
                            <p class="mt-2 text-xs font-semibold text-rose-400 flex items-center gap-1.5">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- حقل كلمة المرور -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                كلمة المرور
                            </label>
                            @if (Route::has('portal.password.request'))
                                <a href="{{ route('portal.password.request') }}" class="text-xs font-semibold text-cyan-400 hover:text-cyan-300 transition-colors focus:outline-none">
                                    نسيت كلمة المرور؟
                                </a>
                            @endif
                        </div>

                        <div class="relative" x-data="{ show: false }">
                            <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-slate-500">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>

                            <input
                                id="password"
                                :type="show ? 'text' : 'password'"
                                name="password"
                                required
                                placeholder="••••••••"
                                class="w-full rounded-2xl border border-slate-800 bg-[#0c1322] py-3.5 ps-12 pe-12 text-sm text-slate-100 placeholder-slate-500 transition duration-200 hover:border-slate-700 focus:border-cyan-500 focus:bg-[#0c1322] focus:outline-none focus:ring-4 focus:ring-cyan-500/10 @error('password') border-rose-500/80 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                            >

                            <!-- زر التبديل بين إظهار وإخفاء كلمة المرور -->
                            <button
                                type="button"
                                @click="show = !show"
                                class="absolute inset-y-0 end-0 flex items-center pe-4 text-slate-500 hover:text-slate-300 focus:outline-none"
                                tabindex="-1"
                            >
                                <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-xs font-semibold text-rose-400 flex items-center gap-1.5">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- خيار تذكرني -->
                    <div class="flex items-center">
                        <label class="flex items-center gap-2.5 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                name="remember"
                                id="remember"
                                class="h-4 w-4 rounded-lg border-slate-700 bg-slate-900 text-cyan-500 focus:ring-cyan-500/20 focus:ring-offset-slate-950"
                            >
                            <span class="text-xs font-medium text-slate-400 hover:text-slate-300 transition-colors">تذكر بيانات الجلسة على هذا المتصفح</span>
                        </label>
                    </div>

                    <!-- زر تسجيل الدخول مع أيقونة القفل -->
                    <button
                        type="submit"
                        class="group relative flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-cyan-500 to-blue-600 px-5 py-4 text-sm font-bold text-white shadow-lg shadow-cyan-500/25 transition-all duration-300 hover:from-cyan-400 hover:to-blue-500 hover:shadow-cyan-500/40 focus:outline-none focus:ring-4 focus:ring-cyan-500/20 active:scale-[0.99]"
                    >
                        <svg class="h-5 w-5 text-cyan-100 transition-transform duration-200 group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>تسجيل الدخول للنظام</span>
                    </button>
                </form>

                <!-- قسم الدعم المؤسسي بدلاً من إنشاء حساب جديد -->
                <div class="mt-10 border-t border-slate-800/80 pt-6 text-center">
                    <p class="text-xs text-slate-500 leading-relaxed">
                        يتم إنشاء وإدارة الحسابات بواسطة مسؤول النظام المركزي.<br>
                        تواجه صعوبة في الدخول؟ تواصل مع
                        <a href="mailto:support@qcoresys.com" class="font-semibold text-cyan-400 hover:text-cyan-300 transition-colors hover:underline">فريق الدعم الفني</a>
                    </p>
                </div>

            </div>

        </div>

    </div>

    <!-- رسائل النظام والتنبيهات (Flash Messages) -->
    @include('partials.app-flash')

    <!-- كود إظهار/إخفاء كلمة المرور في حال لم يكن Alpine مفعلاً -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Alpine) {
                const pwdInput = document.getElementById('password');
                const toggleBtn = pwdInput?.parentElement.querySelector('button');
                if (pwdInput && toggleBtn) {
                    toggleBtn.addEventListener('click', () => {
                        const isPassword = pwdInput.getAttribute('type') === 'password';
                        pwdInput.setAttribute('type', isPassword ? 'text' : 'password');
                    });
                }
            }
        });
    </script>
</body>
</html>

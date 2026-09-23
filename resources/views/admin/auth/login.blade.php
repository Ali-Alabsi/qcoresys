<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول — QCoreSys</title>

    <!-- خط Cairo المعتمد -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Cairo', system-ui, -apple-system, sans-serif !important;
        }

        body {
            background-color: #f8fafc !important;
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        /* الحاوية الرئيسية للبطاقة */
        .login-wrapper {
            width: 100%;
            max-width: 960px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            display: flex;
            overflow: hidden;
            box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.08);
        }

        /* الجانب الأيمن البصري */
        .side-branding {
            flex: 1.1;
            background: linear-gradient(145deg, #f0fdfa 0%, #f8fafc 100%);
            border-left: 1px solid #e2e8f0;
            padding: 44px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        /* الجانب الأيسر لنموذج الدخول */
        .side-form {
            flex: 1;
            padding: 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        /* الشعار */
        .brand-header {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25);
        }
        .brand-name {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }
        .brand-name span {
            color: #0284c7;
        }
        .brand-sub {
            font-size: 9px;
            letter-spacing: 1.5px;
            color: #64748b;
            font-weight: 700;
        }

        /* إحصائيات ونصوص الهوية */
        .badge-live {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 4px 12px;
            background: #e0f2fe;
            border: 1px solid #bae6fd;
            border-radius: 9999px;
            font-size: 12px;
            color: #0369a1;
            font-weight: 700;
            width: fit-content;
            margin-bottom: 16px;
        }
        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #0284c7;
        }
        .branding-title {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.4;
            margin-bottom: 12px;
        }
        .branding-desc {
            font-size: 13px;
            line-height: 1.7;
            color: #64748b;
            margin-bottom: 24px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }
        .stat-val {
            font-size: 18px;
            font-weight: 800;
        }
        .stat-lbl {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        /* حقول الإدخال */
        .form-group {
            margin-bottom: 16px;
        }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 8px;
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-icon {
            position: absolute;
            right: 14px;
            width: 18px;
            height: 18px;
            color: #94a3b8;
            pointer-events: none;
        }
        .form-input {
            width: 100%;
            height: 46px;
            background: #f8fafc !important;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 0 42px 0 14px;
            color: #0f172a;
            font-size: 13px;
            outline: none;
            transition: all 0.2s ease;
        }
        .form-input:focus {
            border-color: #0284c7;
            background: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }
        .form-input::placeholder {
            color: #94a3b8;
        }

        .toggle-btn {
            position: absolute;
            left: 14px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .toggle-btn:hover {
            color: #475569;
        }

        /* زر التسجيل */
        .btn-submit {
            width: 100%;
            height: 46px;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            margin-top: 10px;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25);
        }
        .btn-submit:hover {
            opacity: 0.95;
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.35);
        }

        @media (max-width: 820px) {
            .side-branding {
                display: none;
            }
            .login-wrapper {
                max-width: 440px;
            }
        }

        /* 1. زيادة ظل البطاقة الرئيسية لتبدو مرتفعة بوضوح عن الخلفية */
.login-wrapper {
    width: 100%;
    max-width: 960px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 24px;
    display: flex;
    overflow: hidden;
    /* ظل أعمق وأوضح متعدد الطبقات */
    box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.08),
                0 25px 35px -5px rgba(15, 23, 42, 0.12),
                0 0 0 1px rgba(15, 23, 42, 0.04);
}

/* 2. زيادة وضوح ظل حقول الإدخال */
.form-input {
    width: 100%;
    height: 46px;
    background: #ffffff !important;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    padding: 0 42px 0 14px;
    color: #0f172a;
    font-size: 13px;
    outline: none;
    transition: all 0.2s ease;
    /* ظل بارز قليلاً */
    box-shadow: 0 2px 4px rgba(15, 23, 42, 0.06);
}

.form-input:focus {
    border-color: #0284c7;
    background: #ffffff !important;
    /* هالة تركيز أوضح */
    box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.2),
                0 2px 6px rgba(15, 23, 42, 0.08);
}

/* 3. زيادة ظل بطاقات الإحصائيات في الجانب الأيمن */
.stat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 14px;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.07);
}

/* 4. زيادة بروز ظل زر تسجيل الدخول */
.btn-submit {
    width: 100%;
    height: 46px;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    border: none;
    border-radius: 12px;
    color: #ffffff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
    margin-top: 10px;
    /* ظل أزرق واضح وبارز للزر */
    box-shadow: 0 6px 18px rgba(2, 132, 199, 0.35);
}

.btn-submit:hover {
    opacity: 0.95;
    box-shadow: 0 8px 24px rgba(2, 132, 199, 0.45);
    transform: translateY(-1px);
}
    </style>
</head>
<body>

    <div class="login-wrapper">

        <!-- الجانب الأيمن: بطاقة الهوية بالخلفية الفاتحة -->
        <div class="side-branding">
            <div>
                <div class="brand-header">
                    <div class="brand-icon">
    <!-- أيقونة مستخدمين متعدّدين (Enterprise Users) -->
    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
    </svg>
</div>
                    <div>
                        <div class="brand-name">QCore<span>Sys</span></div>
                        <div class="brand-sub">ENTERPRISE PLATFORM</div>
                    </div>
                </div>

                <div style="margin-top: 36px;">
                    <div class="badge-live">
                        <span class="pulse-dot"></span>
                        ادارة QCoreSys
                    </div>
                    <h1 class="branding-title">بوابة إدارة الأعمال والأنظمة المؤسسية</h1>
                    <p class="branding-desc">وصول مشفر وموثق للخدمات، العقود، والأنظمة في بيئة عمل آمنة وعالية الموثوقية.</p>

                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-val" style="color: #0284c7;">99.9%</div>
                            <div class="stat-lbl">جاهزية واستقرار الخدمة</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-val" style="color: #0369a1;">256-bit</div>
                            <div class="stat-lbl">تشفير وأمان البيانات</div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="font-size: 11px; color: #94a3b8; display: flex; justify-content: space-between; margin-top: 24px;">
                <span>&copy; {{ date('Y') }} QCoreSys.</span>
                <span>v1.00</span>
            </div>
        </div>

        <!-- الجانب الأيسر: نموذج الدخول الأبيض النقي -->
        <div class="side-form">
            <div style="margin-bottom: 24px;">
                <h2 style="font-size: 24px; font-weight: 800; color: #0f172a;">تسجيل الدخول</h2>
                <p style="font-size: 12px; color: #64748b; margin-top: 4px;">أدخل بيانات الاعتماد المعتمدة للوصول للنظام</p>
            </div>

            <!-- إرسال ديناميكي للرابط الحالي لضمان عمل الدخول للإدارة والعملاء -->
            <form method="POST" action="{{ url()->current() }}">
                @csrf

                <!-- البريد الإلكتروني -->
                <div class="form-group">
                    <label class="form-label">البريد الإلكتروني</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@company.com" class="form-input">
                    </div>
                    @error('email')
                        <div style="font-size: 11px; color: #ef4444; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- كلمة المرور -->
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label class="form-label" style="margin-bottom: 0;">كلمة المرور</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" style="font-size: 11px; color: #0284c7; text-decoration: none; font-weight: 600;">نسيت كلمة المرور؟</a>
                        @endif
                    </div>
                    <div class="input-wrapper">
                        <svg class="input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <input id="pwd-field" type="password" name="password" required placeholder="••••••••" class="form-input">
                        <button type="button" id="toggle-pwd" class="toggle-btn" tabindex="-1">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <div style="font-size: 11px; color: #ef4444; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- تذكرني -->
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                    <input type="checkbox" name="remember" id="remember" style="accent-color: #0284c7; cursor: pointer; width: 16px; height: 16px;">
                    <label for="remember" style="font-size: 12px; color: #64748b; cursor: pointer; user-select: none;">تذكر بيانات الجلسة على هذا المتصفح</label>
                </div>

                <!-- زر تسجيل الدخول مع أيقونة القفل -->
                <button type="submit" class="btn-submit">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>تسجيل الدخول للنظام</span>
                </button>
            </form>

            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; text-align: center;">

            </div>
        </div>

    </div>

    <!-- كود إظهار/إخفاء كلمة المرور -->
    <script>
        const pwd = document.getElementById('pwd-field');
        const toggle = document.getElementById('toggle-pwd');
        if (pwd && toggle) {
            toggle.addEventListener('click', () => {
                pwd.type = pwd.type === 'password' ? 'text' : 'password';
            });
        }
    </script>

    @include('partials.app-flash')
</body>
</html>

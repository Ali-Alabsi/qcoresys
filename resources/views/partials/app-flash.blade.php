@if (session('error') || session('status') || session('success') || $errors->any())
    @php
        $isError = session('error') || $errors->any();
        $title = $isError ? 'فشلت العملية' : 'تمت العملية بنجاح';

        if (session('error')) {
            $msg = session('error');
        } elseif (session('status')) {
            $msg = session('status');
        } elseif (session('success')) {
            $msg = session('success');
        } else {
            $msg = $errors->first();
        }

        // ترجمة الرسالة الإنجليزية الشائعة تلقائياً إن وجدت
        if ($msg === 'This account has no customer profile.') {
            $msg = 'هذا الحساب لا يملك ملف عميل معتمد في النظام.';
        }
    @endphp

    <div id="qcs-flash-modal" style="
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(3, 7, 18, 0.75);
        backdrop-filter: blur(8px);
        padding: 16px;
        animation: fadeIn 0.2s ease-out;
    ">
        <!-- نافذة التنبيه -->
        <div style="
            position: relative;
            width: 100%;
            max-width: 400px;
            background: #0d1527;
            border: 1px solid {{ $isError ? 'rgba(239, 68, 68, 0.35)' : 'rgba(6, 182, 212, 0.35)' }};
            border-radius: 20px;
            padding: 28px 24px;
            text-align: center;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.8), 0 0 30px {{ $isError ? 'rgba(239, 68, 68, 0.1)' : 'rgba(6, 182, 212, 0.1)' }};
            font-family: 'Cairo', system-ui, sans-serif;
            color: #f1f5f9;
        ">
            <!-- زر الإغلاق X -->
            <button onclick="document.getElementById('qcs-flash-modal').remove()" style="
                position: absolute;
                top: 14px;
                left: 14px;
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
                color: #94a3b8;
                width: 28px;
                height: 28px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s;
            " onmouseover="this.style.color='#fff'; this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.color='#94a3b8'; this.style.background='rgba(255,255,255,0.05)'">
                ✕
            </button>

            <!-- الأيقونة العلوية المعبرة -->
            <div style="
                width: 54px;
                height: 54px;
                margin: 0 auto 16px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: {{ $isError ? 'rgba(239, 68, 68, 0.12)' : 'rgba(6, 182, 212, 0.12)' }};
                border: 1px solid {{ $isError ? 'rgba(239, 68, 68, 0.3)' : 'rgba(6, 182, 212, 0.3)' }};
                color: {{ $isError ? '#f87171' : '#22d3ee' }};
            ">
                @if($isError)
                    <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                @else
                    <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @endif
            </div>

            <!-- العنوان -->
            <h3 style="
                font-size: 18px;
                font-weight: 800;
                margin-bottom: 8px;
                color: {{ $isError ? '#fca5a5' : '#67e8f9' }};
            ">
                {{ $title }}
            </h3>

            <!-- نص الرسالة -->
            <p style="
                font-size: 13px;
                color: #cbd5e1;
                line-height: 1.6;
                margin-bottom: 22px;
                padding: 0 8px;
            ">
                {{ $msg }}
            </p>

            <!-- زر التأكيد -->
            <button onclick="document.getElementById('qcs-flash-modal').remove()" style="
                width: 100%;
                background: {{ $isError ? 'linear-gradient(135deg, #ef4444 0%, #b91c1c 100%)' : 'linear-gradient(135deg, #06b6d4 0%, #2563eb 100%)' }};
                border: none;
                border-radius: 12px;
                padding: 11px;
                color: #ffffff;
                font-size: 13px;
                font-weight: 700;
                cursor: pointer;
                box-shadow: 0 4px 15px {{ $isError ? 'rgba(239, 68, 68, 0.25)' : 'rgba(6, 182, 212, 0.25)' }};
                transition: transform 0.15s ease;
            " onmouseover="this.style.transform='scale(1.01)'" onmouseout="this.style.transform='scale(1)'">
                حسناً، فهمت
            </button>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
@endif

<?php

namespace Database\Seeders;

use App\Enums\DocumentSequenceKey;
use App\Enums\SettingType;
use App\Models\Account;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::query()
            ->whereIn('account_code', ['1000', '1100', '1200', '2000', '4000', '4100', '5100'])
            ->pluck('id', 'account_code');

        $settings = [
            [
                'key' => 'company_name',
                'value' => 'QCoreSys',
                'type' => SettingType::String,
                'group' => 'general',
                'description' => 'Company legal name',
                'is_public' => true,
            ],
            [
                'key' => 'base_currency',
                'value' => 'USD',
                'type' => SettingType::String,
                'group' => 'general',
                'description' => 'Base currency code',
                'is_public' => true,
            ],
            [
                'key' => 'account_cash',
                'value' => (string) ($accounts['1000'] ?? ''),
                'type' => SettingType::Integer,
                'group' => 'accounting',
                'description' => 'Default cash account',
                'is_public' => false,
            ],
            [
                'key' => 'account_bank',
                'value' => (string) ($accounts['1100'] ?? ''),
                'type' => SettingType::Integer,
                'group' => 'accounting',
                'description' => 'Default bank account',
                'is_public' => false,
            ],
            [
                'key' => 'account_ar',
                'value' => (string) ($accounts['1200'] ?? ''),
                'type' => SettingType::Integer,
                'group' => 'accounting',
                'description' => 'Accounts receivable control account',
                'is_public' => false,
            ],
            [
                'key' => 'account_ap',
                'value' => (string) ($accounts['2000'] ?? ''),
                'type' => SettingType::Integer,
                'group' => 'accounting',
                'description' => 'Accounts payable control account',
                'is_public' => false,
            ],
            [
                'key' => 'account_revenue_consulting',
                'value' => (string) ($accounts['4000'] ?? ''),
                'type' => SettingType::Integer,
                'group' => 'accounting',
                'description' => 'Consulting revenue account',
                'is_public' => false,
            ],
            [
                'key' => 'account_revenue_project',
                'value' => (string) ($accounts['4100'] ?? ''),
                'type' => SettingType::Integer,
                'group' => 'accounting',
                'description' => 'Project revenue account',
                'is_public' => false,
            ],
            [
                'key' => 'account_expense_default',
                'value' => (string) ($accounts['5100'] ?? ''),
                'type' => SettingType::Integer,
                'group' => 'accounting',
                'description' => 'Default expense account',
                'is_public' => false,
            ],
        ];

        foreach (DocumentSequenceKey::cases() as $key) {
            $settings[] = [
                'key' => 'prefix_'.strtolower($key->name),
                'value' => $key->value,
                'type' => SettingType::String,
                'group' => 'documents',
                'description' => "Document prefix for {$key->name}",
                'is_public' => false,
            ];
        }

        $publicSettings = [
            ['company_description', 'Technology consulting and services for modern organizations, with deep expertise in banking and financial systems.', 'company', 'Company description (EN)'],
            ['company_description_en', 'Technology consulting and services for modern organizations, with deep expertise in banking and financial systems.', 'company', 'Company description English'],
            ['company_description_ar', 'استشارات وخدمات تقنية للمؤسسات الحديثة، مع خبرة عميقة في النظم المصرفية والمالية.', 'company', 'Company description Arabic'],
            ['company_email', 'info@qcoresystems.com', 'company', 'Public email'],
            ['company_phone', '', 'company', 'Public phone'],
            ['company_whatsapp', '', 'company', 'Public WhatsApp'],
            ['company_address', '', 'company', 'Public address'],
            ['company_address_en', '', 'company', 'Public address English'],
            ['company_address_ar', '', 'company', 'Public address Arabic'],
            ['company_logo', 'brand/qcore-logo.png', 'company', 'Public logo path'],
            ['social_linkedin', '', 'company', 'LinkedIn URL'],
            ['social_twitter', '', 'company', 'Twitter URL'],
            ['social_facebook', '', 'company', 'Facebook URL'],
            ['social_instagram', '', 'company', 'Instagram URL'],
            ['social_youtube', '', 'company', 'YouTube URL'],
            ['hero_title_en', 'Technology Solutions & Consulting That Make a Real Difference', 'hero', 'Home hero title EN'],
            ['hero_title_ar', 'حلول تقنية واستشارات تصنع فرقًا حقيقيًا', 'hero', 'Home hero title AR'],
            ['hero_subtitle_en', 'We help organizations design, develop, and operate modern technology solutions—with strong expertise across banking, finance, and enterprise systems.', 'hero', 'Home hero subtitle EN'],
            ['hero_subtitle_ar', 'نساعد المؤسسات على تصميم وتطوير وتشغيل حلول تقنية حديثة—مع خبرة قوية في القطاع المصرفي والمالي وأنظمة المؤسسات.', 'hero', 'Home hero subtitle AR'],
            ['services_hero_title_en', 'Technology Services', 'hero', 'Services page hero EN'],
            ['services_hero_title_ar', 'الخدمات التقنية', 'hero', 'Services page hero AR'],
            ['services_hero_subtitle_en', 'Browse our portfolio—from banking and financial systems to software, cloud, and enterprise technology services.', 'hero', 'Services page subtitle EN'],
            ['services_hero_subtitle_ar', 'تصفح محفظة خدماتنا—من النظم المصرفية والمالية إلى البرمجيات والسحابة وخدمات تقنية المؤسسات.', 'hero', 'Services page subtitle AR'],
            ['trust_title_en', 'Technology Expertise Focused on Results', 'trust', 'Trust title EN'],
            ['trust_title_ar', 'خبرة تقنية تركز على النتائج', 'trust', 'Trust title AR'],
            ['trust_body_en', 'We partner with organizations across industries to design, integrate, and deliver production-ready technology—drawing on deep experience in banking and financial platforms.', 'trust', 'Trust body EN'],
            ['trust_body_ar', 'نشارك المؤسسات في مختلف القطاعات على تصميم وتكامل وتسليم حلول تقنية جاهزة للإنتاج—مستندين إلى خبرة عميقة في المنصات المصرفية والمالية.', 'trust', 'Trust body AR'],
            ['cta_title_en', 'Have a Technology Challenge?', 'cta', 'Final CTA title EN'],
            ['cta_title_ar', 'لديك تحدٍ تقني؟', 'cta', 'Final CTA title AR'],
            ['cta_subtitle_en', "Let's talk about your idea or project.", 'cta', 'Final CTA subtitle EN'],
            ['cta_subtitle_ar', 'دعنا نناقش فكرتك أو مشروعك.', 'cta', 'Final CTA subtitle AR'],
            ['about_title_en', 'About QCoreSys', 'company', 'About title EN'],
            ['about_title_ar', 'عن QCoreSys', 'company', 'About title AR'],
            ['about_body_en', 'QCoreSys is a technology consulting and services company helping organizations design, develop, and operate modern solutions—with particular strength in banking and financial systems.', 'company', 'About body EN'],
            ['about_body_ar', 'QCoreSys شركة استشارات وخدمات تقنية تساعد المؤسسات على تصميم وتطوير وتشغيل حلول حديثة—مع تميّز خاص في النظم المصرفية والمالية.', 'company', 'About body AR'],
        ];

        foreach ($publicSettings as [$key, $value, $group, $description]) {
            $settings[] = [
                'key' => $key,
                'value' => $value,
                'type' => SettingType::String,
                'group' => $group,
                'description' => $description,
                'is_public' => true,
            ];
        }

        foreach ($settings as $setting) {
            Setting::query()->updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}

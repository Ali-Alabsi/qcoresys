<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Financial-first ordering
            ['code' => 'CORE_BANKING', 'slug' => 'core-banking', 'name' => 'Core Banking Solutions', 'name_ar' => 'الاستشارات والنظم المصرفية', 'description' => 'Banking systems consulting, branch setup, FX, remittance, and compliance for financial institutions', 'description_ar' => 'استشارات النظم المصرفية وتهيئة الفروع والصرافة والحوالات والامتثال للمؤسسات المالية', 'icon' => 'bank', 'sort_order' => 1],
            ['code' => 'API_INTEGRATION', 'slug' => 'api-integration', 'name' => 'API Management & Integration', 'name_ar' => 'الربط وإدارة الواجهات', 'description' => 'Secure banking APIs, payment/ATM switches, middleware, and integration hubs', 'description_ar' => 'واجهات مصرفية آمنة ومحولات دفع وصراف آلي ووسائط ربط ومراكز تكامل', 'icon' => 'api', 'sort_order' => 2],
            ['code' => 'DATABASE_APEX', 'slug' => 'database-apex', 'name' => 'Database & Oracle APEX', 'name_ar' => 'قواعد البيانات وتطبيقات APEX', 'description' => 'Enterprise database tuning, APEX apps, automation, and disaster recovery for financial workloads', 'description_ar' => 'تحسين قواعد البيانات وتطبيقات APEX والأتمتة والتعافي من الكوارث لأحمال العمل المالية', 'icon' => 'database', 'sort_order' => 3],
            ['code' => 'GO_LIVE_SUPPORT', 'slug' => 'go-live-support', 'name' => 'Go-Live & SLA Support', 'name_ar' => 'الإطلاق الحي والدعم الفني', 'description' => 'Cutover management, hypercare, and SLA support for banking production launches', 'description_ar' => 'إدارة الانتقال الحي والدعم الحرج واتفاقيات مستوى الخدمة لإطلاق الأنظمة المصرفية', 'icon' => 'rocket', 'sort_order' => 4],
            ['code' => 'QA_TESTING', 'slug' => 'qa-testing', 'name' => 'QA & System Testing', 'name_ar' => 'ضبط الجودة وفحص الأنظمة', 'description' => 'UAT, stress testing, and security auditing for financial and operational systems', 'description_ar' => 'اختبارات القبول والتحمل وفحص الأمان للأنظمة المالية والتشغيلية', 'icon' => 'check', 'sort_order' => 5],
            ['code' => 'SOFTWARE_DEV', 'slug' => 'software-development', 'name' => 'Software Development', 'name_ar' => 'تطوير البرمجيات', 'description' => 'Custom software development and engineering', 'description_ar' => 'تطوير وهندسة برمجيات مخصصة', 'icon' => 'code', 'sort_order' => 6],
            ['code' => 'MOBILE_APPS', 'slug' => 'mobile-applications', 'name' => 'Mobile Applications', 'name_ar' => 'تطبيقات الجوال', 'description' => 'Native and cross-platform mobile applications', 'description_ar' => 'تطبيقات جوال أصلية ومتعددة المنصات', 'icon' => 'mobile', 'sort_order' => 7],
            ['code' => 'WEB_APPS', 'slug' => 'web-applications', 'name' => 'Web Applications', 'name_ar' => 'تطبيقات الويب', 'description' => 'Modern web applications and portals', 'description_ar' => 'تطبيقات وبوابات ويب حديثة', 'icon' => 'globe', 'sort_order' => 8],
            ['code' => 'CLOUD', 'slug' => 'cloud-solutions', 'name' => 'Cloud Solutions', 'name_ar' => 'الحلول السحابية', 'description' => 'Cloud architecture, migration, and managed services', 'description_ar' => 'هندسة وترحيل وإدارة الخدمات السحابية', 'icon' => 'cloud', 'sort_order' => 9],
            ['code' => 'DEVOPS', 'slug' => 'devops', 'name' => 'DevOps', 'name_ar' => 'DevOps', 'description' => 'CI/CD, automation, and platform engineering', 'description_ar' => 'التكامل المستمر والأتمتة وهندسة المنصات', 'icon' => 'cog', 'sort_order' => 10],
            ['code' => 'CYBER_SECURITY', 'slug' => 'cyber-security', 'name' => 'Cyber Security', 'name_ar' => 'الأمن السيبراني', 'description' => 'Security assessments, compliance, and protection', 'description_ar' => 'تقييمات الأمن والامتثال والحماية', 'icon' => 'shield', 'sort_order' => 11],
            ['code' => 'INFRASTRUCTURE', 'slug' => 'infrastructure', 'name' => 'Infrastructure', 'name_ar' => 'البنية التحتية', 'description' => 'Infrastructure design, deployment, and management', 'description_ar' => 'تصميم ونشر وإدارة البنية التحتية', 'icon' => 'server', 'sort_order' => 12],
            ['code' => 'NETWORKING', 'slug' => 'networking', 'name' => 'Networking', 'name_ar' => 'الشبكات', 'description' => 'Enterprise networking and connectivity', 'description_ar' => 'شبكات المؤسسات والاتصال', 'icon' => 'network', 'sort_order' => 13],
            ['code' => 'DATABASE', 'slug' => 'database-solutions', 'name' => 'Database Solutions', 'name_ar' => 'حلول قواعد البيانات', 'description' => 'Database design, optimization, and administration', 'description_ar' => 'تصميم وتحسين وإدارة قواعد البيانات', 'icon' => 'database', 'sort_order' => 14],
            ['code' => 'INTEGRATION', 'slug' => 'system-integration', 'name' => 'System Integration', 'name_ar' => 'تكامل الأنظمة', 'description' => 'Enterprise system and API integration', 'description_ar' => 'تكامل أنظمة المؤسسات وواجهات البرمجة', 'icon' => 'link', 'sort_order' => 15],
            ['code' => 'IT_CONSULTING', 'slug' => 'it-consulting', 'name' => 'IT Consulting', 'name_ar' => 'الاستشارات التقنية', 'description' => 'Strategic and technical IT consulting services', 'description_ar' => 'خدمات الاستشارات التقنية الاستراتيجية', 'icon' => 'briefcase', 'sort_order' => 16],
            ['code' => 'SUPPORT', 'slug' => 'technical-support', 'name' => 'Technical Support', 'name_ar' => 'الدعم الفني', 'description' => 'Managed support and maintenance services', 'description_ar' => 'خدمات الدعم والصيانة المدارة', 'icon' => 'support', 'sort_order' => 17],
            ['code' => 'TRAINING', 'slug' => 'training', 'name' => 'Training', 'name_ar' => 'التدريب التقني', 'description' => 'Technical training and enablement programs', 'description_ar' => 'برامج التدريب والتمكين التقني', 'icon' => 'academic', 'sort_order' => 18],
            ['code' => 'SMART_PARKING', 'slug' => 'smart-parking', 'name' => 'Smart Parking Solutions', 'name_ar' => 'أنظمة المواقف الذكية', 'description' => 'Smart parking apps, PMS, IoT/ANPR integration, and analytics', 'description_ar' => 'تطبيقات المواقف الذكية ونظام الإدارة والربط مع العتاد والتحليلات', 'icon' => 'parking', 'sort_order' => 19],
            ['code' => 'PROJECT_MGMT', 'slug' => 'project-management', 'name' => 'Project Management', 'name_ar' => 'إدارة المشاريع', 'description' => 'Project planning, delivery, and governance', 'description_ar' => 'تخطيط المشاريع والتسليم والحوكمة', 'icon' => 'clipboard', 'sort_order' => 20, 'is_public' => false],
        ];

        foreach ($categories as $category) {
            $isPublic = $category['is_public'] ?? true;
            unset($category['is_public']);

            ServiceCategory::query()->updateOrCreate(
                ['code' => $category['code']],
                array_merge($category, [
                    'slug' => $category['slug'] ?? Str::slug($category['name']),
                    'is_active' => true,
                    'is_public' => $isPublic,
                ])
            );
        }
    }
}

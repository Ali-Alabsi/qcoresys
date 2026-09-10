<?php

namespace Database\Seeders;

use App\Enums\ServiceRequestOptionType;
use App\Models\ServiceRequestOption;
use Illuminate\Database\Seeder;

class ServiceRequestOptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            [ServiceRequestOptionType::ProjectType, 'NEW_BUILD', 'New Build', 'بناء جديد', 1],
            [ServiceRequestOptionType::ProjectType, 'ENHANCEMENT', 'Enhancement', 'تطوير وتحسين', 2],
            [ServiceRequestOptionType::ProjectType, 'MIGRATION', 'Migration', 'ترحيل', 3],
            [ServiceRequestOptionType::ProjectType, 'CONSULTING_ONLY', 'Consulting Only', 'استشارة فقط', 4],
            [ServiceRequestOptionType::ProjectType, 'SUPPORT_RETAINER', 'Support Retainer', 'عقد دعم', 5],

            [ServiceRequestOptionType::BudgetRange, 'BUDGET_10K', 'Under $10,000', 'أقل من 10,000 دولار', 1],
            [ServiceRequestOptionType::BudgetRange, 'BUDGET_10_50K', '$10,000 – $50,000', '10,000 – 50,000 دولار', 2],
            [ServiceRequestOptionType::BudgetRange, 'BUDGET_50_150K', '$50,000 – $150,000', '50,000 – 150,000 دولار', 3],
            [ServiceRequestOptionType::BudgetRange, 'BUDGET_150K_PLUS', '$150,000+', 'أكثر من 150,000 دولار', 4],
            [ServiceRequestOptionType::BudgetRange, 'BUDGET_TBD', 'To be discussed', 'يتم تحديدها لاحقًا', 5],

            [ServiceRequestOptionType::Timeline, 'ASAP', 'As soon as possible', 'في أقرب وقت', 1],
            [ServiceRequestOptionType::Timeline, '1_3_MONTHS', '1 – 3 months', '1 – 3 أشهر', 2],
            [ServiceRequestOptionType::Timeline, '3_6_MONTHS', '3 – 6 months', '3 – 6 أشهر', 3],
            [ServiceRequestOptionType::Timeline, '6_PLUS_MONTHS', '6+ months', 'أكثر من 6 أشهر', 4],
            [ServiceRequestOptionType::Timeline, 'FLEXIBLE', 'Flexible', 'مرن', 5],

            [ServiceRequestOptionType::ContactMethod, 'EMAIL', 'Email', 'البريد الإلكتروني', 1],
            [ServiceRequestOptionType::ContactMethod, 'PHONE', 'Phone', 'الهاتف', 2],
            [ServiceRequestOptionType::ContactMethod, 'WHATSAPP', 'WhatsApp', 'واتساب', 3],
            [ServiceRequestOptionType::ContactMethod, 'MEETING', 'Meeting', 'اجتماع', 4],
        ];

        foreach ($options as [$type, $code, $label, $labelAr, $sort]) {
            ServiceRequestOption::query()->updateOrCreate(
                ['code' => $code],
                [
                    'option_type' => $type,
                    'label' => $label,
                    'label_ar' => $labelAr,
                    'sort_order' => $sort,
                    'is_active' => true,
                ]
            );
        }
    }
}

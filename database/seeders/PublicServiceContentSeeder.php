<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Service;
use App\Models\ServiceBenefit;
use App\Models\ServiceFeature;
use App\Models\ServiceProcessStep;
use App\Models\Technology;
use Illuminate\Database\Seeder;

class PublicServiceContentSeeder extends Seeder
{
    public function run(): void
    {
        $techIds = Technology::query()->pluck('id', 'slug');

        $content = [
            'mobile-application-development' => [
                'technologies' => ['flutter', 'ios', 'android', 'firebase', 'laravel'],
                'features' => [
                    ['UI/UX Design', 'تصميم واجهات وتجربة المستخدم', 'Product-focused mobile interface design.', 'تصميم واجهات جوال تركز على المنتج.'],
                    ['Mobile Development', 'تطوير الجوال', 'iOS and Android application engineering.', 'هندسة تطبيقات iOS وAndroid.'],
                    ['Backend APIs', 'واجهات خلفية', 'Secure APIs and integrations.', 'واجهات برمجة آمنة وتكاملات.'],
                    ['Authentication', 'المصادقة', 'Secure sign-in and access control.', 'تسجيل دخول آمن والتحكم في الوصول.'],
                    ['Testing', 'الاختبار', 'Functional and device testing.', 'اختبارات وظيفية واختبارات الأجهزة.'],
                    ['Deployment', 'النشر', 'App Store and Play Store release support.', 'دعم النشر في متاجر التطبيقات.'],
                ],
                'benefits' => [
                    ['Scalable Architecture', 'بنية قابلة للتوسع', 'Built for growth and maintainability.', 'مبنية للنمو وسهولة الصيانة.'],
                    ['Secure Development', 'تطوير آمن', 'Security considered from design to delivery.', 'الأمن مأخوذ بعين الاعتبار من التصميم حتى التسليم.'],
                    ['Modern Technology', 'تقنيات حديثة', 'Current mobile frameworks and tooling.', 'أطر وأدوات جوال حديثة.'],
                    ['Business-focused Solutions', 'حلول تركز على احتياجات العمل', 'Aligned with your business goals.', 'متوافقة مع أهداف أعمالك.'],
                    ['Professional Support', 'دعم احترافي', 'Ongoing guidance after launch.', 'إرشاد مستمر بعد الإطلاق.'],
                ],
                'steps' => [
                    ['Understanding Your Needs', 'الاستماع إلى احتياجك', 'Discovery workshops and stakeholder interviews.', 'ورش اكتشاف ومقابلات أصحاب المصلحة.'],
                    ['Requirements Analysis', 'تحليل المتطلبات', 'Translate goals into clear requirements.', 'تحويل الأهداف إلى متطلبات واضحة.'],
                    ['Solution Design', 'تصميم الحل', 'UX flows, architecture, and delivery plan.', 'تدفقات التجربة والهندسة وخطة التسليم.'],
                    ['Development & Implementation', 'التنفيذ والتطوير', 'Iterative mobile development and integrations.', 'تطوير جوال تكاملي متكرر.'],
                    ['Testing', 'الاختبار', 'Quality assurance across devices and scenarios.', 'ضمان الجودة عبر الأجهزة والسيناريوهات.'],
                    ['Delivery & Support', 'التسليم والدعم', 'Store release and post-launch support.', 'النشر في المتاجر والدعم بعد الإطلاق.'],
                ],
                'faqs' => [
                    ['Do you build for both iOS and Android?', 'هل تطورون لنظامي iOS وAndroid؟', 'Yes. We deliver cross-platform and native options based on your needs.', 'نعم. نقدم خيارات متعددة المنصات وأصلية حسب احتياجك.'],
                    ['Can you integrate with existing systems?', 'هل يمكن التكامل مع أنظمة قائمة؟', 'Yes. We integrate with APIs, backends, and third-party platforms.', 'نعم. نتكامل مع واجهات البرمجة والأنظمة الخلفية والمنصات الخارجية.'],
                ],
            ],
            'software-development' => [
                'technologies' => ['laravel', 'aspnet-core', 'c', 'php', 'mysql', 'postgresql'],
                'features' => [
                    ['Requirements Engineering', 'هندسة المتطلبات', 'Clear scope and acceptance criteria.', 'نطاق واضح ومعايير قبول.'],
                    ['Solution Architecture', 'هندسة الحلول', 'Scalable and maintainable designs.', 'تصميمات قابلة للتوسع والصيانة.'],
                    ['Custom Development', 'تطوير مخصص', 'Business software engineered to fit.', 'برمجيات أعمال مصممة لتناسبكم.'],
                    ['Quality Assurance', 'ضمان الجودة', 'Testing strategy and regression coverage.', 'استراتيجية اختبار وتغطية انحدار.'],
                    ['Secure Delivery', 'تسليم آمن', 'Secure coding and controlled releases.', 'تطوير آمن وإصدارات مضبوطة.'],
                ],
                'benefits' => [
                    ['Business Alignment', 'توافق مع الأعمال', 'Software that supports real workflows.', 'برمجيات تدعم سير العمل الفعلي.'],
                    ['Maintainability', 'قابلية الصيانة', 'Clean code and documentation.', 'شيفرة نظيفة وتوثيق.'],
                    ['Security First', 'الأمن أولاً', 'Controls designed into the product.', 'ضوابط مدمجة في المنتج.'],
                ],
                'steps' => [
                    ['Understanding Your Needs', 'الاستماع إلى احتياجك', null, null],
                    ['Requirements Analysis', 'تحليل المتطلبات', null, null],
                    ['Solution Design', 'تصميم الحل', null, null],
                    ['Development & Implementation', 'التنفيذ والتطوير', null, null],
                    ['Testing', 'الاختبار', null, null],
                    ['Delivery & Support', 'التسليم والدعم', null, null],
                ],
                'faqs' => [],
            ],
            'cyber-security-services' => [
                'technologies' => ['aws', 'azure', 'docker'],
                'features' => [
                    ['Security Assessment', 'تقييم أمني', 'Identify risks and gaps.', 'تحديد المخاطر والفجوات.'],
                    ['Risk Analysis', 'تحليل المخاطر', 'Prioritize remediation by impact.', 'ترتيب المعالجة حسب الأثر.'],
                    ['Hardening', 'التقوية', 'Strengthen systems and configurations.', 'تقوية الأنظمة والإعدادات.'],
                    ['Compliance Advisory', 'استشارات الامتثال', 'Guidance for security frameworks.', 'إرشاد لأطر الأمن والامتثال.'],
                ],
                'benefits' => [
                    ['Reduced Risk', 'تقليل المخاطر', 'Practical controls for critical assets.', 'ضوابط عملية للأصول الحرجة.'],
                    ['Clear Roadmap', 'خارطة طريق واضحة', 'Actionable remediation plan.', 'خطة معالجة قابلة للتنفيذ.'],
                ],
                'steps' => [
                    ['Assessment', 'التقييم', 'Review current security posture.', 'مراجعة الوضع الأمني الحالي.'],
                    ['Risk Analysis', 'تحليل المخاطر', 'Evaluate likelihood and impact.', 'تقييم الاحتمال والأثر.'],
                    ['Security Implementation', 'تنفيذ الأمن', 'Apply prioritized controls.', 'تطبيق الضوابط ذات الأولوية.'],
                    ['Testing', 'الاختبار', 'Validate effectiveness.', 'التحقق من الفعالية.'],
                    ['Monitoring', 'المراقبة', 'Ongoing visibility and guidance.', 'رؤية مستمرة وإرشاد.'],
                ],
                'faqs' => [],
            ],
            'cloud-solutions' => [
                'technologies' => ['aws', 'azure', 'docker', 'kubernetes'],
                'features' => [
                    ['Cloud Architecture', 'الهندسة السحابية', 'Secure scalable cloud designs.', 'تصاميم سحابية آمنة وقابلة للتوسع.'],
                    ['Migration', 'الترحيل', 'Plan and execute cloud moves.', 'تخطيط وتنفيذ الانتقال للسحابة.'],
                    ['Cost Optimization', 'تحسين التكلفة', 'Right-size resources and spend.', 'ضبط الموارد والإنفاق.'],
                ],
                'benefits' => [
                    ['Reliability', 'الموثوقية', 'Resilient cloud foundations.', 'أساسات سحابية مرنة.'],
                    ['Operational Readiness', 'الاستعداد التشغيلي', 'Monitoring and runbooks.', 'مراقبة وأدلة تشغيل.'],
                ],
                'steps' => [
                    ['Understanding Your Needs', 'الاستماع إلى احتياجك', null, null],
                    ['Requirements Analysis', 'تحليل المتطلبات', null, null],
                    ['Solution Design', 'تصميم الحل', null, null],
                    ['Development & Implementation', 'التنفيذ والتطوير', null, null],
                    ['Testing', 'الاختبار', null, null],
                    ['Delivery & Support', 'التسليم والدعم', null, null],
                ],
                'faqs' => [],
            ],
            'devops-cicd' => [
                'technologies' => ['docker', 'kubernetes', 'aws', 'azure', 'redis'],
                'features' => [
                    ['CI/CD Pipelines', 'خطوط التكامل المستمر', 'Automated build and deploy.', 'بناء ونشر آلي.'],
                    ['Containerization', 'الحاويات', 'Consistent runtime environments.', 'بيئات تشغيل متسقة.'],
                    ['Observability', 'قابلية الملاحظة', 'Logs, metrics, and alerts.', 'سجلات ومقاييس وتنبيهات.'],
                ],
                'benefits' => [
                    ['Faster Releases', 'إصدارات أسرع', 'Shorter delivery cycles.', 'دورات تسليم أقصر.'],
                    ['Higher Reliability', 'موثوقية أعلى', 'Repeatable automated processes.', 'عمليات آلية قابلة للتكرار.'],
                ],
                'steps' => [
                    ['Understanding Your Needs', 'الاستماع إلى احتياجك', null, null],
                    ['Requirements Analysis', 'تحليل المتطلبات', null, null],
                    ['Solution Design', 'تصميم الحل', null, null],
                    ['Development & Implementation', 'التنفيذ والتطوير', null, null],
                    ['Testing', 'الاختبار', null, null],
                    ['Delivery & Support', 'التسليم والدعم', null, null],
                ],
                'faqs' => [],
            ],
            'it-consulting' => [
                'technologies' => ['laravel', 'aws', 'azure'],
                'features' => [
                    ['Technology Strategy', 'الاستراتيجية التقنية', 'Roadmaps aligned to business goals.', 'خرائط طريق متوافقة مع أهداف العمل.'],
                    ['Architecture Advisory', 'استشارات الهندسة', 'Independent technical guidance.', 'إرشاد تقني مستقل.'],
                    ['Decision Support', 'دعم القرار', 'Evaluate options and trade-offs.', 'تقييم الخيارات والمفاضلات.'],
                ],
                'benefits' => [
                    ['Clarity', 'وضوح', 'Better technology decisions.', 'قرارات تقنية أفضل.'],
                    ['Risk Reduction', 'تقليل المخاطر', 'Avoid costly missteps.', 'تجنب خطوات مكلفة خاطئة.'],
                ],
                'steps' => [
                    ['Understanding Your Needs', 'الاستماع إلى احتياجك', null, null],
                    ['Requirements Analysis', 'تحليل المتطلبات', null, null],
                    ['Solution Design', 'تصميم الحل', null, null],
                    ['Recommendations', 'التوصيات', 'Prioritized advisory outcomes.', 'نتائج استشارية مرتبة حسب الأولوية.'],
                    ['Delivery & Support', 'التسليم والدعم', null, null],
                ],
                'faqs' => [],
            ],
            'core-banking-solutions' => [
                'technologies' => ['oracle', 'oracle-apex', 'soap', 'oauth-20', 'iso-8583'],
                'features' => [
                    ['Core System Assessment', 'تقييم ومراجعة الأنظمة المصرفية القائمة', 'Audit core stability under load and analyze accounting/transaction paths.', 'تدقيق استقرار النظام تحت ضغط العمليات وتحليل مسارات القيود والمعاملات.'],
                    ['EOD / EOM Diagnostics', 'تشخيص الإغلاق اليومي والدوري', 'Inspect workflow integrity and root causes of slow EOD/EOM processing.', 'فحص سلامة تدفق العمليات واكتشاف أسباب بطء الإغلاق اليومي أو الدوري.'],
                    ['Architecture & Risk Reports', 'تقارير التقييم المعماري والمخاطر', 'Comprehensive reports on architectural weaknesses, data risks, and remediation plans.', 'تقارير شاملة لنقاط الضعف المعمارية ومخاطر البيانات وخطة التحسين الفنية.'],
                    ['Branch Rollout & Setup', 'تهيئة وتوسعة الفروع', 'Launch new branches and outlets in the core without disrupting live operations.', 'إطلاق الفروع والمنافذ داخل النظام المركزي دون تعطيل العمليات الجارية.'],
                    ['Access & Chart of Accounts', 'الصلاحيات وشجرة الحسابات', 'Configure user permissions, CoA structures, and automated posting templates.', 'ضبط صلاحيات المستخدمين وشجرة الحسابات والقوالب المحاسبية الآلية.'],
                    ['Cash & Teller Limits', 'خزائن الفروع وسقوف العمليات', 'Configure branch vaults, teller drawers, and cash/transfer ceilings.', 'ضبط خزائن الفروع وصناديق الصرافين وسقوف العمليات النقدية والتحويلية.'],
                    ['FX Trading Workflows', 'دورات أعمال الصرافة', 'Build FX buy/sell cycles linked to live exchange-rate feeds.', 'بناء دورات بيع وشراء العملات وربطها بنشرات أسعار الصرف الحية.'],
                    ['Remittance Networks', 'شبكات الحوالات', 'Engineer internal and express remittance paths with send/receive/payout tracking.', 'هندسة شبكات الحوالات وتتبع مسارات الإرسال والاستلام والصرف.'],
                    ['Digital Wallets Clearing', 'المحافظ الرقمية والمقاصة', 'Integrate wallets and manage daily clearing/settlement with central bank accounts.', 'الربط مع المحافظ وإدارة مقاصة وتسوية العمليات اليومية مع الحسابات المركزية.'],
                    ['Audit Trails & Compliance', 'سجلات التدقيق والامتثال', 'Strict audit trails for sensitive operations and regulatory reporting packs.', 'سجلات تدقيق صارمة للعمليات الحساسة وتقارير رقابية دورية.'],
                ],
                'benefits' => [
                    ['Operational Stability', 'استقرار تشغيلي', 'Safer peak processing and predictable closings.', 'معالجة أكثر أماناً في الذروة وإغلاقات أكثر قابلية للتنبؤ.'],
                    ['Controlled Expansion', 'توسعة مضبوطة', 'Open branches without interrupting serving customers.', 'فتح فروع دون تعطيل خدمة العملاء.'],
                    ['Regulatory Readiness', 'جاهزية رقابية', 'Controls and reports aligned with supervisory expectations.', 'ضوابط وتقارير متوافقة مع التوقعات الرقابية.'],
                    ['Traceable Finance Ops', 'عمليات مالية قابلة للتتبع', 'Clear accountability for balances and sensitive changes.', 'مساءلة واضحة للأرصدة والتعديلات الحساسة.'],
                ],
                'steps' => [
                    ['Understanding Your Needs', 'الاستماع إلى احتياجك', 'Map products, channels, and regulatory constraints.', 'تحديد المنتجات والقنوات والقيود الرقابية.'],
                    ['Requirements Analysis', 'تحليل المتطلبات', 'Assess core health, closings, and branch readiness.', 'تقييم صحة النظام والإغلاقات وجاهزية الفروع.'],
                    ['Solution Design', 'تصميم الحل', 'Target architecture, controls, and rollout plan.', 'الهندسة المستهدفة والضوابط وخطة الإطلاق.'],
                    ['Development & Implementation', 'التنفيذ والتطوير', 'Configure workflows, limits, FX/remittance, and reports.', 'تهيئة الدورات والسقوف والصرافة/الحوالات والتقارير.'],
                    ['Testing', 'الاختبار', 'UAT on financial paths and closing cycles.', 'اختبارات قبول للمسارات المالية ودورات الإغلاق.'],
                    ['Delivery & Support', 'التسليم والدعم', 'Go-live supervision and stabilization support.', 'إشراف الإطلاق ودعم الاستقرار.'],
                ],
                'faqs' => [
                    ['Do you work on existing core banking platforms?', 'هل تعملون على أنظمة مصرفية قائمة؟', 'Yes. We assess and improve incumbent cores without unnecessary rip-and-replace.', 'نعم. نقيّم ونحسّن الأنظمة القائمة دون استبدال غير ضروري.'],
                ],
            ],
            'api-management-integration' => [
                'technologies' => ['soap', 'oauth-20', 'swagger', 'iso-8583', 'oracle-weblogic', 'tomcat', 'docker'],
                'features' => [
                    ['Secure REST & SOAP APIs', 'بناء وتأمين واجهات REST وSOAP', 'High-performance APIs to connect cores with partners and external systems.', 'واجهات عالية الأداء لربط النظام المصرفي بالشركاء والأنظمة الخارجية.'],
                    ['Modern Auth Standards', 'معايير المصادقة الحديثة', 'OAuth 2.0, JWT, WS-Security UsernameToken, and Mutual TLS.', 'OAuth 2.0 وJWT وWS-Security UsernameToken وMutual TLS.'],
                    ['OpenAPI Documentation', 'توثيق Swagger / OpenAPI', 'Developer-ready interface documentation for third-party onboarding.', 'توثيق جاهز للمطورين لتسهيل ربط الأطراف الثالثة.'],
                    ['ATM & Payment Switches', 'محولات الصراف الآلي والدفع', 'ISO 8583 messaging and dual-interface switch integration.', 'رسائل ISO 8583 والربط الثنائي مع محولات الصراف الآلي.'],
                    ['Card Network Connectivity', 'الربط مع شبكات البطاقات', 'Integration with Mastercard/Visa clearing and settlement services.', 'الربط مع Mastercard/Visa وخدمات المقاصة والتسوية.'],
                    ['Middleware Deployment', 'نشر وسائط الربط', 'Configure WebLogic/Tomcat, load balancers, and web tiers for peak load.', 'ضبط WebLogic/Tomcat وموازنات الأحمال وطبقات الويب لذروة الحمل.'],
                    ['SSL/TLS Hardening', 'تأمين بيئة النشر', 'Certificates and network isolation for sensitive zones.', 'شهادات الأمان وعزل الشبكات الحساسة.'],
                    ['Centralized API Hub', 'منصة تتبع مركزية للواجهات', 'Request/response logging, latency dashboards, and intrusion/failure alerts.', 'تسجيل الطلبات والاستجابات ولوحات الزمن والتنبيهات.'],
                ],
                'benefits' => [
                    ['Safer Partner Connectivity', 'ربط أكثر أماناً مع الشركاء', 'Authenticated, encrypted channels with auditability.', 'قنوات موثّقة ومشفّرة وقابلة للتدقيق.'],
                    ['Peak Resilience', 'مرونة في الذروة', 'Middleware and switches tuned for transaction spikes.', 'وسائط ومحولات مضبوطة لذروات المعاملات.'],
                    ['Operational Visibility', 'رؤية تشغيلية', 'Live metrics for errors, latency, and abuse attempts.', 'مؤشرات لحظية للأخطاء والتأخير ومحاولات الإساءة.'],
                ],
                'steps' => [
                    ['Understanding Your Needs', 'الاستماع إلى احتياجك', null, null],
                    ['Requirements Analysis', 'تحليل المتطلبات', 'Map partners, protocols, and security baselines.', 'تحديد الشركاء والبروتوكولات وخط الأساس الأمني.'],
                    ['Solution Design', 'تصميم الحل', 'API contracts, switch topology, and hub design.', 'عقود الواجهات وطوبولوجيا المحول وتصميم المركز.'],
                    ['Development & Implementation', 'التنفيذ والتطوير', 'Build, secure, and deploy interfaces and middleware.', 'بناء وتأمين ونشر الواجهات والوسائط.'],
                    ['Testing', 'الاختبار', 'Contract, load, and failover validation.', 'التحقق من العقود والحمل والتحويل عند التعثر.'],
                    ['Delivery & Support', 'التسليم والدعم', 'Handover with monitoring and runbooks.', 'التسليم مع المراقبة وأدلة التشغيل.'],
                ],
                'faqs' => [],
            ],
            'enterprise-database-apex' => [
                'technologies' => ['oracle', 'oracle-apex', 'oracle-weblogic'],
                'features' => [
                    ['SQL & PL/SQL Tuning', 'تحسين أداء الاستعلامات', 'Tune complex SQL/PL-SQL to cut CPU, memory, and elapsed time.', 'ضبط استعلامات SQL وإجراءات PL/SQL لتقليص الزمن واستهلاك الموارد.'],
                    ['Index & Plan Optimization', 'إعادة هيكلة الفهارس وخطط التنفيذ', 'Rebuild/reorganize indexes and optimize execution plans.', 'إعادة بناء الفهارس وضبط خطط التنفيذ.'],
                    ['Locking & Concurrency', 'تحسين القفل والتزامن', 'Remove bottlenecks from locking and table contention.', 'إزالة اختناقات القفل وتزاحم الجداول.'],
                    ['Oracle APEX Applications', 'تطوير تطبيقات Oracle APEX', 'Internal platforms and executive dashboards with strong input controls.', 'منصات داخلية ولوحات تنفيذية مع ضوابط إدخال صارمة.'],
                    ['Interactive Reporting', 'تقارير تفاعلية متعددة الصيغ', 'PDF/Excel/CSV exports with live statistical charts.', 'تصدير PDF وExcel وCSV مع رسوم بيانية مباشرة.'],
                    ['Scheduled Automation', 'أتمتة المهام المجدولة', 'Cron/DBMS_SCHEDULER jobs for backup and periodic maintenance.', 'مهام مجدولة للنسخ الاحتياطي والصيانة الدورية.'],
                    ['Housekeeping & Growth Alerts', 'تنظيف السجلات وتنبيهات النمو', 'Purge temporary data and monitor tablespace growth proactively.', 'تفريغ البيانات المؤقتة ومراقبة نمو Tablespaces باستباقية.'],
                    ['Disaster Recovery', 'التعافي من الكوارث', 'Hot/cold backup strategies with RPO/RTO drills.', 'نسخ ساخن وبارد مع اختبارات RPO وRTO.'],
                ],
                'benefits' => [
                    ['Faster Critical Queries', 'استعلامات حرجة أسرع', 'Lower latency for peak financial workloads.', 'زمن استجابة أقل لأحمال العمل المالية في الذروة.'],
                    ['Faster Internal Delivery', 'تسليم داخلي أسرع', 'APEX accelerates secure business screens and dashboards.', 'APEX يسرّع الشاشات ولوحات الأعمال الآمنة.'],
                    ['Continuity Confidence', 'ثقة في الاستمرارية', 'Tested recovery objectives for mission-critical data.', 'أهداف استعادة مختبرة للبيانات الحرجة.'],
                ],
                'steps' => [
                    ['Understanding Your Needs', 'الاستماع إلى احتياجك', null, null],
                    ['Requirements Analysis', 'تحليل المتطلبات', 'Profile workloads, locks, and recovery targets.', 'تحليل الأحمال والأقفال وأهداف الاستعادة.'],
                    ['Solution Design', 'تصميم الحل', 'Tuning plan, APEX scope, and DR strategy.', 'خطة الضبط ونطاق APEX واستراتيجية التعافي.'],
                    ['Development & Implementation', 'التنفيذ والتطوير', 'Apply tuning, build APEX apps, automate jobs.', 'تطبيق الضبط وبناء APEX وأتمتة المهام.'],
                    ['Testing', 'الاختبار', 'Performance benchmarks and recovery drills.', 'معايير أداء وتمارين استعادة.'],
                    ['Delivery & Support', 'التسليم والدعم', 'Handover with monitoring thresholds.', 'التسليم مع عتبات المراقبة.'],
                ],
                'faqs' => [],
            ],
            'custom-fullstack-development' => [
                'technologies' => ['laravel', 'react', 'vuejs', 'flutter', 'docker', 'redis', 'kafka', 'mysql', 'postgresql'],
                'features' => [
                    ['Enterprise Web Platforms', 'تطبيقات الويب المؤسسية', 'Portals built with microservices/clean architecture.', 'بوابات بمعماريات خدمات دقيقة/نظيفة.'],
                    ['Bilingual Responsive UX', 'واجهات ثنائية اللغة وسريعة', 'Professional AR/EN responsive interfaces.', 'واجهات عربية/إنجليزية احترافية ومتجاوبة.'],
                    ['Role-Based Access Control', 'صلاحيات قائمة على الأدوار', 'Advanced RBAC for enterprise governance.', 'RBAC متقدم لحوكمة المؤسسات.'],
                    ['Mobile Apps iOS & Android', 'تطبيقات الجوال', 'Native or cross-platform apps with secure APIs.', 'تطبيقات أصلية أو متعددة المنصات مع واجهات آمنة.'],
                    ['Realtime Cloud Connectivity', 'ربط سحابي في الوقت الفعلي', 'Live sync with central databases and services.', 'مزامنة حية مع قواعد البيانات والخدمات المركزية.'],
                    ['Push, Payments & Biometrics', 'إشعارات ودفع ومصادقة حيوية', 'Push notifications, embedded payments, fingerprint/face auth.', 'إشعارات مباشرة ودفع مدمج ومصادقة بالبصمة والوجه.'],
                    ['Backend & Message Queues', 'هندسة الخلفية والطوابير', 'Complex business engines with async queues.', 'محركات أعمال معقدة مع طوابير غير متزامنة.'],
                    ['Data Encryption', 'تشفير البيانات الحساسة', 'Protect data in transit and at rest.', 'حماية البيانات أثناء النقل والتخزين.'],
                    ['Business Process Automation', 'أتمتة العمليات الإدارية', 'Replace paper workflows with approved digital paths, file sync, SMS/Email gateways.', 'تحويل المسارات الورقية إلى رقمية مع مزامنة الملفات وبوابات الرسائل.'],
                ],
                'benefits' => [
                    ['Fit-for-Purpose Software', 'برمجيات تناسب احتياجكم', 'Built around real regulated and high-volume workflows.', 'مبنية حول مسارات منظمة وعالية الحجم فعلياً.'],
                    ['Secure by Design', 'أمن منذ التصميم', 'RBAC, encryption, and audited integrations.', 'صلاحيات وتشفير وتكاملات قابلة للتدقيق.'],
                    ['Faster Operations', 'عمليات أسرع', 'Automation reduces manual and paper handoffs.', 'الأتمتة تقلل التسليم اليدوي والورقي.'],
                ],
                'steps' => [
                    ['Understanding Your Needs', 'الاستماع إلى احتياجك', null, null],
                    ['Requirements Analysis', 'تحليل المتطلبات', null, null],
                    ['Solution Design', 'تصميم الحل', null, null],
                    ['Development & Implementation', 'التنفيذ والتطوير', null, null],
                    ['Testing', 'الاختبار', null, null],
                    ['Delivery & Support', 'التسليم والدعم', null, null],
                ],
                'faqs' => [],
            ],
            'smart-parking-solutions' => [
                'technologies' => ['flutter', 'laravel', 'react', 'firebase', 'anpr', 'docker', 'aws'],
                'features' => [
                    ['Driver Mobile Apps', 'تطبيقات الجوال للسائقين', 'Live availability, map search, and navigation to spaces.', 'الشواغر لحظياً والبحث بالخريطة والملاحة إلى الموقف.'],
                    ['Booking & Extensions', 'الحجز والتمديد والإلغاء', 'Advance booking with automatic capacity updates.', 'حجز مسبق مع تحديث آلي للسعة.'],
                    ['Digital Payments', 'الدفع الإلكتروني', 'Cards, wallets, or prepaid balance settlement.', 'بطاقات أو محافظ أو رصيد مسبق.'],
                    ['Subscriptions & Passes', 'الاشتراكات والتصاريح', 'Monthly/yearly plans with QR/NFC digital permits.', 'اشتراكات دورية وبطاقات عبور رقمية QR/NFC.'],
                    ['Parking Management System', 'نظام إدارة المواقف المركزي', 'Cloud console for capacity, gates, and multi-zone lots.', 'لوحة سحابية للسعة والبوابات والمناطق المتعددة.'],
                    ['Dynamic Pricing Engine', 'محرك تسعير ديناميكي', 'Per-minute/hour, peak, daily, or event pricing.', 'تسعير بالدقيقة/الساعة والذروة واليوم والمناسبات.'],
                    ['VIP & Staff Permits', 'تصاريح VIP والموظفين', 'Manage VIP bays, permanent staff, and visitor tickets.', 'إدارة مواقف VIP وتصاريح الموظفين وتذاكر الزوار.'],
                    ['ANPR / LPR Integration', 'الربط مع التعرف على اللوحات', 'Auto barrier open without tickets via plate recognition.', 'فتح الحواجز تلقائياً دون تذاكر عبر التعرف على اللوحات.'],
                    ['Sensors & Guidance Screens', 'حساسات وشاشات إرشادية', 'Occupancy sensors and digital vacancy displays per lane.', 'حساسات إشغال وشاشات شواغر لكل مسار.'],
                    ['Kiosks & Barrier Gates', 'أكشاك الدفع والبوابات', 'Pay stations and electronic entry/exit barriers.', 'ماكينات دفع ذاتي وحواجز دخول وخروج إلكترونية.'],
                    ['Revenue & Ops Analytics', 'تحليلات الإيرادات والتشغيل', 'Daily/monthly revenue, peak hours, dwell time, occupancy.', 'إيرادات يومية/شهرية وساعات الذروة ومتوسط المكوث والإشغال.'],
                    ['Entry/Exit Audit Trail', 'سجلات الدخول والخروج', 'Timestamped visual records to deter fraud.', 'سجلات مصورة مؤرخة لمنع التلاعب.'],
                ],
                'benefits' => [
                    ['Higher Occupancy Yield', 'عائد إشغال أعلى', 'Dynamic pricing and live guidance fill spaces faster.', 'التسعير الديناميكي والإرشاد اللحظي يملآن الشواغر أسرع.'],
                    ['Less Manual Gate Work', 'أقل اعتماداً على العمل اليدوي', 'ANPR and digital passes streamline entry/exit.', 'التعرف على اللوحات والتصاريح الرقمية تسهّل الدخول والخروج.'],
                    ['Clear Financial Control', 'رقابة مالية أوضح', 'Detailed revenue by gate, method, and customer type.', 'إيرادات مفصلة حسب البوابة والطريقة ونوع العميل.'],
                ],
                'steps' => [
                    ['Understanding Your Needs', 'الاستماع إلى احتياجك', 'Survey sites, hardware, and commercial model.', 'مسح المواقع والعتاد والنموذج التجاري.'],
                    ['Requirements Analysis', 'تحليل المتطلبات', null, null],
                    ['Solution Design', 'تصميم الحل', 'App, PMS, pricing, and IoT topology.', 'التطبيق ونظام الإدارة والتسعير وطوبولوجيا العتاد.'],
                    ['Development & Implementation', 'التنفيذ والتطوير', 'Build software and integrate gates/sensors/ANPR.', 'بناء البرمجيات وربط البوابات والحساسات وANPR.'],
                    ['Testing', 'الاختبار', 'Field UAT for booking, payment, and barriers.', 'اختبارات ميدانية للحجز والدفع والحواجز.'],
                    ['Delivery & Support', 'التسليم والدعم', 'Launch monitoring and operator training.', 'مراقبة الإطلاق وتدريب المشغّلين.'],
                ],
                'faqs' => [],
            ],
            'qa-system-testing' => [
                'technologies' => ['docker', 'swagger', 'oracle', 'laravel'],
                'features' => [
                    ['User Acceptance Testing', 'اختبارات القبول والاعتماد', 'Full UAT packs for financial and operational use cases before launch.', 'حزم فحص شاملة لحالات الاستخدام المالي والتشغيلي قبل الإطلاق.'],
                    ['Edge Case Coverage', 'الحالات الاستثنائية والحرجة', 'Validate normal paths and critical exceptions that could halt the system.', 'فحص المسارات الاعتيادية والحالات الحرجة التي قد توقف النظام.'],
                    ['Stress & Load Testing', 'اختبارات الأداء والتحمل', 'Measure database/system capacity under concurrent peak transactions.', 'قياس قدرة النظام وقاعدة البيانات تحت تزاحم المعاملات.'],
                    ['Breaking-Point Analysis', 'حدود التحمل ونقاط الانهيار', 'Identify ceilings and remediate before production.', 'تحديد الحدود القصوى ومعالجتها قبل الإنتاج.'],
                    ['Integration Data Integrity', 'فحص تكامل تدفق البيانات', 'Verify interface data flows and prevent sensitive financial leakage.', 'التحقق من تدفق البيانات بين الواجهات ومنع تسرب البيانات الحساسة.'],
                    ['Security & Auth Abuse Tests', 'فحص الثغرات وانتحال الصلاحيات', 'Basic vulnerability checks and privilege-impersonation simulations.', 'اختبارات ثغرات أساسية ومحاكاة انتحال الصلاحيات.'],
                ],
                'benefits' => [
                    ['Fewer Production Surprises', 'مفاجآت أقل في الإنتاج', 'Defects found under realistic scenarios before go-live.', 'عيوب تُكتشف في سيناريوهات واقعية قبل الإطلاق.'],
                    ['Capacity Confidence', 'ثقة في القدرة الاستيعابية', 'Know peak limits before customers do.', 'معرفة حدود الذروة قبل أن يكتشفها العملاء.'],
                    ['Safer Financial Paths', 'مسارات مالية أكثر أماناً', 'Integrity and authorization checks on sensitive flows.', 'فحوص سلامة وتفويض على المسارات الحساسة.'],
                ],
                'steps' => [
                    ['Understanding Your Needs', 'الاستماع إلى احتياجك', null, null],
                    ['Requirements Analysis', 'تحليل المتطلبات', 'Define scope, risks, and acceptance criteria.', 'تحديد النطاق والمخاطر ومعايير القبول.'],
                    ['Solution Design', 'تصميم الحل', 'Build test strategy and scenario matrix.', 'بناء استراتيجية الفحص ومصفوفة السيناريوهات.'],
                    ['Development & Implementation', 'التنفيذ والتطوير', 'Prepare scripts, data sets, and harnesses.', 'إعداد السكربتات ومجموعات البيانات وأدوات الفحص.'],
                    ['Testing', 'الاختبار', 'Execute UAT, load, and security suites.', 'تنفيذ حزم القبول والحمل والأمان.'],
                    ['Delivery & Support', 'التسليم والدعم', 'Report findings with prioritized remediation.', 'تقرير النتائج مع معالجة مرتبة حسب الأولوية.'],
                ],
                'faqs' => [],
            ],
            'go-live-sla-support' => [
                'technologies' => ['oracle', 'docker', 'aws', 'azure'],
                'features' => [
                    ['Cutover Planning', 'إدارة الانتقال الحي', 'Near-zero downtime schedule into live production.', 'جدول انتقال بأقل فترة توقف ممكنة إلى الإنتاج.'],
                    ['Opening Balances Migration', 'نقل الأرصدة والبيانات الافتتاحية', 'Supervise legacy freeze and opening data cutover.', 'الإشراف على إيقاف الأنظمة السابقة ونقل البيانات الافتتاحية.'],
                    ['Hypercare Monitoring', 'الدعم الحرج بعد الإطلاق', 'On-site and remote teams resolving issues as they appear.', 'فريق ميداني وعن بُعد يعالج المعوقات فور ظهورها.'],
                    ['First Closing Supervision', 'الإشراف على الإغلاق الأول', 'Support the first daily/banking close and reconciliations.', 'التواجد خلال أول إغلاق يومي/مصرفي وضمان المطابقات.'],
                    ['SLA Maintenance Contracts', 'اتفاقيات مستوى الخدمة', 'Defined response times for urgent technical incidents.', 'أوقات استجابة محددة للمشكلات الطارئة.'],
                    ['Safe Upgrades', 'تحديثات وترقيات آمنة', 'Periodic patches and security upgrades without operational disruption.', 'ترقيات دورية وأمنية مع الحفاظ على استقرار العمليات.'],
                ],
                'benefits' => [
                    ['Controlled Go-Live', 'إطلاق مضبوط', 'Structured cutover reduces freeze windows and risk.', 'انتقال منظم يقلل نافذة التوقف والمخاطر.'],
                    ['Immediate Stabilization', 'استقرار فوري', 'Hypercare catches early defects in live cycles.', 'الدعم الحرج يلتقط العيوب المبكرة في الدورات الحية.'],
                    ['Predictable Support', 'دعم قابل للتنبؤ', 'SLA commitments keep operations covered after launch.', 'التزامات SLA تبقي العمليات مغطاة بعد الإطلاق.'],
                ],
                'steps' => [
                    ['Understanding Your Needs', 'الاستماع إلى احتياجك', 'Align freeze windows and success criteria.', 'مواءمة نوافذ التوقف ومعايير النجاح.'],
                    ['Requirements Analysis', 'تحليل المتطلبات', 'Inventory cutover tasks and rollback points.', 'جرد مهام الانتقال ونقاط التراجع.'],
                    ['Solution Design', 'تصميم الحل', 'Cutover runbook and hypercare roster.', 'دليل الانتقال وتشكيلة فريق الدعم الحرج.'],
                    ['Development & Implementation', 'التنفيذ والتطوير', 'Execute migration and production enablement.', 'تنفيذ الترحيل وتفعيل الإنتاج.'],
                    ['Testing', 'الاختبار', 'Smoke checks and first-cycle reconciliations.', 'فحوص دخان ومطابقات الدورة الأولى.'],
                    ['Delivery & Support', 'التسليم والدعم', 'Hypercare then steady-state SLA support.', 'دعم حرج ثم دعم SLA مستقر.'],
                ],
                'faqs' => [],
            ],
        ];

        $defaultSteps = [
            ['Understanding Your Needs', 'الاستماع إلى احتياجك'],
            ['Requirements Analysis', 'تحليل المتطلبات'],
            ['Solution Design', 'تصميم الحل'],
            ['Development & Implementation', 'التنفيذ والتطوير'],
            ['Testing', 'الاختبار'],
            ['Delivery & Support', 'التسليم والدعم'],
        ];

        $publicServices = Service::query()->public()->get();

        foreach ($publicServices as $service) {
            $bundle = $content[$service->slug] ?? null;

            if ($bundle && ! empty($bundle['technologies'])) {
                $sync = [];
                foreach ($bundle['technologies'] as $i => $slug) {
                    // C# slug may be "c" from Str::slug - TechnologySeeder uses Str::slug('C#') which is "c"
                    $mapped = $slug === 'aspnet-core' ? 'aspnet-core' : $slug;
                    if ($slug === 'c') {
                        $mapped = Technology::query()->where('name', 'C#')->value('slug') ?? 'c';
                    }
                    if (isset($techIds[$mapped])) {
                        $sync[$techIds[$mapped]] = ['sort_order' => $i + 1];
                    } elseif ($id = Technology::query()->where('slug', $mapped)->value('id')) {
                        $sync[$id] = ['sort_order' => $i + 1];
                    }
                }
                $service->technologies()->sync($sync);
            }

            if ($bundle && ! empty($bundle['features'])) {
                foreach ($bundle['features'] as $i => $feature) {
                    ServiceFeature::query()->updateOrCreate(
                        ['service_id' => $service->id, 'title' => $feature[0]],
                        [
                            'title_ar' => $feature[1],
                            'description' => $feature[2] ?? null,
                            'description_ar' => $feature[3] ?? null,
                            'sort_order' => $i + 1,
                            'is_active' => true,
                        ]
                    );
                }
            }

            if ($bundle && ! empty($bundle['benefits'])) {
                foreach ($bundle['benefits'] as $i => $benefit) {
                    ServiceBenefit::query()->updateOrCreate(
                        ['service_id' => $service->id, 'title' => $benefit[0]],
                        [
                            'title_ar' => $benefit[1],
                            'description' => $benefit[2] ?? null,
                            'description_ar' => $benefit[3] ?? null,
                            'sort_order' => $i + 1,
                            'is_active' => true,
                        ]
                    );
                }
            }

            $steps = $bundle['steps'] ?? array_map(fn ($s) => [$s[0], $s[1], null, null], $defaultSteps);
            foreach ($steps as $i => $step) {
                ServiceProcessStep::query()->updateOrCreate(
                    ['service_id' => $service->id, 'step_number' => $i + 1],
                    [
                        'title' => $step[0],
                        'title_ar' => $step[1],
                        'description' => $step[2] ?? null,
                        'description_ar' => $step[3] ?? null,
                        'sort_order' => $i + 1,
                        'is_active' => true,
                    ]
                );
            }

            if ($bundle && ! empty($bundle['faqs'])) {
                foreach ($bundle['faqs'] as $i => $faqData) {
                    $faq = Faq::query()->updateOrCreate(
                        ['question' => $faqData[0]],
                        [
                            'question_ar' => $faqData[1],
                            'answer' => $faqData[2],
                            'answer_ar' => $faqData[3],
                            'is_active' => true,
                            'sort_order' => $i + 1,
                        ]
                    );
                    $service->faqs()->syncWithoutDetaching([
                        $faq->id => ['sort_order' => $i + 1],
                    ]);
                }
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Technology;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TechnologySeeder extends Seeder
{
    public function run(): void
    {
        $technologies = [
            ['name' => 'Flutter', 'category' => 'Mobile'],
            ['name' => 'Laravel', 'category' => 'Backend'],
            ['name' => 'ASP.NET Core', 'category' => 'Backend'],
            ['name' => 'C#', 'category' => 'Language'],
            ['name' => 'PHP', 'category' => 'Language'],
            ['name' => 'MySQL', 'category' => 'Database'],
            ['name' => 'PostgreSQL', 'category' => 'Database'],
            ['name' => 'Oracle', 'category' => 'Database'],
            ['name' => 'Docker', 'category' => 'DevOps'],
            ['name' => 'Kubernetes', 'category' => 'DevOps'],
            ['name' => 'Redis', 'category' => 'Infrastructure'],
            ['name' => 'Kafka', 'category' => 'Infrastructure'],
            ['name' => 'Firebase', 'category' => 'Cloud'],
            ['name' => 'AWS', 'category' => 'Cloud'],
            ['name' => 'Azure', 'category' => 'Cloud'],
            ['name' => 'React', 'category' => 'Frontend'],
            ['name' => 'Vue.js', 'category' => 'Frontend'],
            ['name' => 'iOS', 'category' => 'Mobile'],
            ['name' => 'Android', 'category' => 'Mobile'],
            ['name' => 'Oracle APEX', 'category' => 'Database'],
            ['name' => 'Oracle WebLogic', 'category' => 'Middleware'],
            ['name' => 'Tomcat', 'category' => 'Middleware'],
            ['name' => 'SOAP', 'category' => 'Integration'],
            ['name' => 'OAuth 2.0', 'category' => 'Security'],
            ['name' => 'ISO 8583', 'category' => 'Payments'],
            ['name' => 'Swagger', 'category' => 'Integration'],
            ['name' => 'ANPR', 'category' => 'IoT'],
        ];

        foreach ($technologies as $index => $technology) {
            Technology::query()->updateOrCreate(
                ['slug' => Str::slug($technology['name'])],
                [
                    'name' => $technology['name'],
                    'name_ar' => $technology['name'],
                    'category' => $technology['category'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}

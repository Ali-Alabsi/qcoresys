<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'Management',
            'Sales / Business Development',
            'Technical Consulting',
            'Software Development',
            'Infrastructure',
            'Cyber Security',
            'DevOps',
            'Finance',
            'Administration',
        ];

        foreach ($departments as $name) {
            Department::query()->updateOrCreate(
                ['code' => Str::upper(Str::slug($name, '_'))],
                [
                    'name' => $name,
                    'description' => $name.' department',
                    'is_active' => true,
                ]
            );
        }
    }
}

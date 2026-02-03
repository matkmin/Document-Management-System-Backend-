<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);

        // 2. Create Departments
        $departments = [
            'Human Resources',
            'Finance',
            'Information Technology',
            'Marketing',
            'Operations'
        ];

        foreach ($departments as $deptName) {
            Department::firstOrCreate([
                'name' => $deptName,
                'description' => "$deptName Department"
            ]);
        }

        // 3. Create Categories
        $categories = ['Policy', 'Report', 'Template', 'Guide', 'Form', 'Other'];
        foreach ($categories as $cat) {
            DocumentCategory::firstOrCreate([
                'title' => $cat,
                'description' => "$cat Documents"
            ]);
        }

        // 4. Create Users

        // Admin Group (Does not belong to a specific department or all? Usually null or IT. Let's say IT)
        $itDept = Department::where('name', 'Information Technology')->first();

        $admin = User::firstOrCreate(
            ['email' => 'admin@dms.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'department_id' => $itDept->id,
            ]
        );
        $admin->assignRole($adminRole);

        // Managers (One per department)
        $allDepts = Department::all();
        foreach ($allDepts as $dept) {
            // Skip one department for manager (Requirements: "4 Managers (one per department, except one)")
            if ($dept->name === 'Operations')
                continue;

            $manager = User::firstOrCreate(
                ['email' => 'manager_' . strtolower(str_replace(' ', '', $dept->name)) . '@dms.com'],
                [
                    'name' => $dept->name . ' Manager',
                    'password' => Hash::make('password'),
                    'department_id' => $dept->id,
                ]
            );
            $manager->assignRole($managerRole);
        }

        // Employees (7 Employees distributed)
        $empCount = 0;
        foreach ($allDepts as $dept) {
            if ($empCount >= 7)
                break;

            $limit = 2; // Create 1-2 employees per dept
            for ($i = 0; $i < $limit; $i++) {
                if ($empCount >= 7)
                    break;

                $employee = User::create([
                    'name' => "Employee $empCount - " . $dept->name,
                    'email' => "employee$empCount@dms.com",
                    'password' => Hash::make('password'),
                    'department_id' => $dept->id,
                ]);
                $employee->assignRole($employeeRole);
                $empCount++;
            }
        }

        // 5. Create Documents
        $this->call(DocumentSeeder::class);
    }
}

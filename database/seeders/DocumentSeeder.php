<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;
use App\Models\User;
use App\Models\Department;
use App\Models\DocumentCategory;
use Illuminate\Support\Facades\File;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure a dummy file exists
        $dummyPath = storage_path('app/public/documents');
        if (!File::exists($dummyPath)) {
            File::makeDirectory($dummyPath, 0755, true);
        }
        $dummyFile = $dummyPath . '/dummy.pdf';
        if (!File::exists($dummyFile)) {
            File::put($dummyFile, 'Dummy content for testing');
        }

        // 2. Get Data
        $users = User::all();
        $departments = Department::all();
        $categories = DocumentCategory::all();

        if ($users->isEmpty() || $departments->isEmpty() || $categories->isEmpty()) {
            $this->command->error("Missing required data (Users, Departments, or Categories). Run DatabaseSeeder first.");
            return;
        }

        $totalDocuments = 35;
        $created = 0;

        // Titles map to make them sound realistic
        $titles = [
            'Annual Financial Report',
            'Q1 Performance Review',
            'Employee Handbook 2024',
            'Safety Guidelines v2',
            'Project Alpha Specification',
            'Marketing Strategy 2025',
            'IT Security Policy',
            'Remote Work Policy',
            'Budget Proposal Q3',
            'Client Meeting Minutes',
            'Product Roadmap',
            'Expense Report Template',
            'Leave Request Form',
            'Invoice #2024-001',
            'Internship Program Guide',
            'Server Maintenance Log',
            'Customer Feedback Summary',
            'Sales Forecast 2024',
            'Brand Guidelines',
            'Office Floor Plan'
        ];

        for ($i = 0; $i < $totalDocuments; $i++) {
            // Determine Access Level (50% public, 30% department, 20% private)
            $rand = rand(1, 100);
            if ($rand <= 50) {
                $accessLevel = 'public';
            } elseif ($rand <= 80) {
                $accessLevel = 'department';
            } else {
                $accessLevel = 'private';
            }

            $uploader = $users->random();
            $dept = $departments->random(); // Documents can belong to any dept usually, or uploader's dept

            // If access is 'department', usually it matches the department_id
            // If access is 'private', it's visible to uploader.

            // For variety, let's sometimes match dept to uploader, sometimes not (unless logic strictly forbits it, but our model allows it)
            if ($accessLevel === 'department') {
                $dept = $uploader->department;
                // Fallback if uploader has no dept (e.g. admin might not?)
                if (!$dept)
                    $dept = $departments->random();
            }

            Document::create([
                'title' => $titles[array_rand($titles)] . " " . rand(100, 999),
                'description' => 'This is a sample document description for testing purposes.',
                'file_name' => 'dummy.pdf',
                'file_path' => 'documents/dummy.pdf', // Relative path for storage
                'file_type' => 'pdf',
                'file_size' => rand(1024, 512000), // Random size 1KB - 500KB
                'document_category_id' => $categories->random()->id,
                'department_id' => $dept->id,
                'uploaded_by' => $uploader->id,
                'access_level' => $accessLevel,
                'download_count' => rand(0, 50),
                'created_at' => now()->subDays(rand(0, 60)), // Spread over last 60 days
            ]);

            $created++;
        }

        $this->command->info("Seeded $created documents.");
    }
}

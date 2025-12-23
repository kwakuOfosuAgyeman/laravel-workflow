<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\Step;

class WorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a sample workflow
        $workflow = Workflow::create([
            'name' => 'Health Check Workflow',
            'description' => 'A sample workflow that demonstrates delay and HTTP check steps',
        ]);

        // Step 1: Short delay
        $workflow->steps()->create([
            'type' => Step::TYPE_DELAY,
            'config' => ['seconds' => 1],
            'step_order' => 1,
        ]);

        // Step 2: Check a reliable external URL
        $workflow->steps()->create([
            'type' => Step::TYPE_HTTP_CHECK,
            'config' => ['url' => 'https://httpbin.org/status/200'],
            'step_order' => 2,
        ]);

        // Step 3: Another delay
        $workflow->steps()->create([
            'type' => Step::TYPE_DELAY,
            'config' => ['seconds' => 0.5],
            'step_order' => 3,
        ]);

        // Step 4: Check another endpoint
        $workflow->steps()->create([
            'type' => Step::TYPE_HTTP_CHECK,
            'config' => ['url' => 'https://httpbin.org/delay/1'],
            'step_order' => 4,
        ]);

        // Create a second workflow for demonstration
        $workflow2 = Workflow::create([
            'name' => 'Quick Status Check',
            'description' => 'Fast workflow to check if services are responding',
        ]);

        $workflow2->steps()->create([
            'type' => Step::TYPE_HTTP_CHECK,
            'config' => ['url' => 'https://www.google.com'],
            'step_order' => 1,
        ]);

        $workflow2->steps()->create([
            'type' => Step::TYPE_HTTP_CHECK,
            'config' => ['url' => 'https://api.github.com'],
            'step_order' => 2,
        ]);

        // Create a workflow that will fail (for testing error handling)
        $workflow3 = Workflow::create([
            'name' => 'Failing Workflow',
            'description' => 'Demonstrates error handling with an invalid URL',
        ]);

        $workflow3->steps()->create([
            'type' => Step::TYPE_DELAY,
            'config' => ['seconds' => 0.5],
            'step_order' => 1,
        ]);

        $workflow3->steps()->create([
            'type' => Step::TYPE_HTTP_CHECK,
            'config' => ['url' => 'https://this-url-does-not-exist-12345.com'],
            'step_order' => 2,
        ]);
    }
}

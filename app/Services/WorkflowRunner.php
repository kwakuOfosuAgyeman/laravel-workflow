<?php

namespace App\Services;

use App\Models\Workflow;
use App\Models\Run;
use App\Models\RunLog;
use App\Services\StepExecutorFactory;

class WorkflowRunner
{
    /**
     * Execute a workflow and return the run record.
     *
     * @param Workflow $workflow
     * @return Run
     */
    public function execute(Workflow $workflow): Run
    {
        // Create the run record
        $run = $workflow->runs()->create([
            'status' => Run::STATUS_PENDING,
            'started_at' => now(),
        ]);

        // Mark as started
        $run->markAsStarted();

        try {
            // Get all steps ordered
            $steps = $workflow->steps;

            // Check if workflow has steps
            if ($steps->isEmpty()) {
                RunLog::warn($run, null, 'Workflow has no steps to execute');
                $run->markAsSucceeded();
                return $run;
            }

            // Log workflow start
            RunLog::info($run, null, "Starting workflow execution with {$steps->count()} step(s)");

            // Execute each step in order
            foreach ($steps as $step) {
                // Get the appropriate executor for this step type
                $executor = StepExecutorFactory::make($step->type);

                if (!$executor) {
                    RunLog::error(
                        $run,
                        $step,
                        "Unknown step type '{$step->type}'. No executor available."
                    );
                    $run->markAsFailed();
                    return $run;
                }

                // Execute the step
                RunLog::info($run, $step, "Executing step: {$step->type} (order: {$step->step_order})");

                $success = $executor->execute($step, $run);

                // If step failed, mark workflow as failed and stop
                if (!$success) {
                    RunLog::error(
                        $run,
                        $step,
                        "Step execution failed. Stopping workflow."
                    );
                    $run->markAsFailed();
                    return $run;
                }
            }

            // All steps succeeded
            RunLog::info($run, null, 'Workflow completed successfully');
            $run->markAsSucceeded();

        } catch (\Exception $e) {
            // Log the exception
            RunLog::error(
                $run,
                null,
                "Workflow execution failed with exception: {$e->getMessage()}"
            );

            // Mark as failed
            $run->markAsFailed();
        }

        return $run;
    }
}

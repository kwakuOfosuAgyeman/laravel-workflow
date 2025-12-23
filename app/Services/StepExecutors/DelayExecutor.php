<?php

namespace App\Services\StepExecutors;

use App\Models\Step;
use App\Models\Run;
use App\Models\RunLog;

class DelayExecutor implements StepExecutorInterface
{
    /**
     * Maximum allowed delay in seconds.
     */
    const MAX_DELAY_SECONDS = 2;

    /**
     * Execute a delay step.
     *
     * @param Step $step
     * @param Run $run
     * @return bool
     */
    public function execute(Step $step, Run $run): bool
    {
        $config = $step->config;

        // Validate that seconds is provided
        if (!isset($config['seconds'])) {
            RunLog::error(
                $run,
                $step,
                "Delay step missing required 'seconds' parameter in config"
            );
            return false;
        }

        $seconds = $config['seconds'];

        // Validate that seconds is numeric
        if (!is_numeric($seconds)) {
            RunLog::error(
                $run,
                $step,
                "Delay 'seconds' must be numeric, got: " . gettype($seconds)
            );
            return false;
        }

        // Convert to float/int and validate it's not negative
        $seconds = (float) $seconds;

        if ($seconds < 0) {
            RunLog::error(
                $run,
                $step,
                "Delay 'seconds' cannot be negative: {$seconds}"
            );
            return false;
        }

        // Cap at maximum allowed delay
        $originalSeconds = $seconds;
        $seconds = min($seconds, self::MAX_DELAY_SECONDS);

        // Log if we capped the delay
        if ($originalSeconds > self::MAX_DELAY_SECONDS) {
            RunLog::warn(
                $run,
                $step,
                "Requested delay of {$originalSeconds}s exceeds maximum. Capped to " . self::MAX_DELAY_SECONDS . "s"
            );
        }

        // Log start of delay
        RunLog::info(
            $run,
            $step,
            "Starting delay for {$seconds} second(s)"
        );

        // Execute the delay
        try {
            // Use usleep for sub-second precision if needed
            if ($seconds < 1) {
                usleep((int)($seconds * 1000000));
            } else {
                sleep((int)$seconds);

                // Handle fractional seconds
                $fractional = $seconds - floor($seconds);
                if ($fractional > 0) {
                    usleep((int)($fractional * 1000000));
                }
            }

            // Log successful completion
            RunLog::info(
                $run,
                $step,
                "Delay completed successfully ({$seconds}s)"
            );

            return true;

        } catch (\Exception $e) {
            // This should rarely happen, but catch any unexpected errors
            RunLog::error(
                $run,
                $step,
                "Delay execution failed: {$e->getMessage()}"
            );
            return false;
        }
    }

    /**
     * Check if this executor supports the given step type.
     *
     * @param string $type
     * @return bool
     */
    public function supports(string $type): bool
    {
        return $type === Step::TYPE_DELAY;
    }
}

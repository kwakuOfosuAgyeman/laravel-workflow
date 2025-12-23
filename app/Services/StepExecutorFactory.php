<?php

namespace App\Services;

use App\Models\Step;
use App\Services\StepExecutors\StepExecutorInterface;
use App\Services\StepExecutors\DelayExecutor;
use App\Services\StepExecutors\HttpCheckExecutor;

class StepExecutorFactory
{
    /**
     * Registry of available executors.
     *
     * @var array<StepExecutorInterface>
     */
    protected static array $executors = [];

    /**
     * Get the appropriate executor for a step type.
     *
     * @param string $type
     * @return StepExecutorInterface|null
     */
    public static function make(string $type): ?StepExecutorInterface
    {
        // Initialize executors on first call
        if (empty(self::$executors)) {
            self::registerDefaultExecutors();
        }

        // Find executor that supports this type
        foreach (self::$executors as $executor) {
            if ($executor->supports($type)) {
                return $executor;
            }
        }

        return null;
    }

    /**
     * Register the default executors.
     *
     * @return void
     */
    protected static function registerDefaultExecutors(): void
    {
        self::$executors = [
            new DelayExecutor(),
            new HttpCheckExecutor(),
        ];
    }

    /**
     * Register a custom executor (useful for testing or extending).
     *
     * @param StepExecutorInterface $executor
     * @return void
     */
    public static function register(StepExecutorInterface $executor): void
    {
        self::$executors[] = $executor;
    }

    /**
     * Clear all registered executors (useful for testing).
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$executors = [];
    }

    /**
     * Get all registered executors.
     *
     * @return array<StepExecutorInterface>
     */
    public static function all(): array
    {
        if (empty(self::$executors)) {
            self::registerDefaultExecutors();
        }

        return self::$executors;
    }
}

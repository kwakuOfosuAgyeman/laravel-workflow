<?php
namespace App\Services\StepExecutors;

use App\Models\Step;
use App\Models\Run;

interface StepExecutorInterface
{
    public function execute(Step $step, Run $run): bool;
    public function supports(string $type): bool;
}

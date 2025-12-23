<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\StepController;
use App\Http\Controllers\RunController;

// Route::get('/', function () {
//     return view('welcome');
// });

// web.php
Route::get('/', function () {
    return redirect()->route('workflows.index');
});

Route::resource('workflows', WorkflowController::class);
Route::post('workflows/{workflow}/run', [WorkflowController::class, 'run'])
    ->name('workflows.run');

// Steps nested under workflows
Route::post('workflows/{workflow}/steps', [StepController::class, 'store'])
    ->name('workflows.steps.store');
Route::patch('steps/{step}', [StepController::class, 'update'])
    ->name('steps.update');
Route::delete('steps/{step}', [StepController::class, 'destroy'])
    ->name('steps.destroy');
Route::patch('steps/{step}/move/{direction}', [StepController::class, 'move'])
    ->name('steps.move');

// Run details
Route::get('runs/{run}', [RunController::class, 'show'])
    ->name('runs.show');

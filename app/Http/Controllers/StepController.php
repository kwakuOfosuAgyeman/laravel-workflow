<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Models\Step;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StepController extends Controller
{
    /**
     * Store a newly created step in storage.
     */
    public function store(Request $request, Workflow $workflow)
    {
        // Validate the request
        $validated = $request->validate([
            'type' => ['required', Rule::in([Step::TYPE_DELAY, Step::TYPE_HTTP_CHECK])],
            'config' => 'required|array',
            'step_order' => 'nullable|integer|min:1',
        ]);

        // Additional validation based on step type
        if ($validated['type'] === Step::TYPE_DELAY) {
            $configValidation = [
                'config.seconds' => 'required|numeric|min:0|max:10',
            ];
        } elseif ($validated['type'] === Step::TYPE_HTTP_CHECK) {
            $configValidation = [
                'config.url' => 'required|url|max:500',
            ];
        } else {
            return back()
                ->withErrors(['type' => 'Invalid step type'])
                ->withInput();
        }

        $request->validate($configValidation);

        // Determine order position
        $maxOrder = $workflow->steps()->max('order') ?? 0;
        $targetOrder = $validated['step_order'] ?? ($maxOrder + 1);

        // If inserting in middle, shift existing steps down
        if ($targetOrder <= $maxOrder) {
            $workflow->steps()
                ->where('step_order', '>=', $targetOrder)
                ->increment('step_order');
        }

        // Create the step
        $step = $workflow->steps()->create([
            'type' => $validated['type'],
            'config' => $validated['config'],
            'step_order' => $targetOrder,
        ]);

        return redirect()
            ->route('workflows.show', $workflow)
            ->with('success', 'Step added successfully!');
    }

    /**
     * Update the specified step in storage.
     */
    public function update(Request $request, Step $step)
    {
        // Validate the request
        $validated = $request->validate([
            'type' => ['required', Rule::in([Step::TYPE_DELAY, Step::TYPE_HTTP_CHECK])],
            'config' => 'required|array',
        ]);

        // Additional validation based on step type
        if ($validated['type'] === Step::TYPE_DELAY) {
            $configValidation = [
                'config.seconds' => 'required|numeric|min:0|max:10',
            ];
        } elseif ($validated['type'] === Step::TYPE_HTTP_CHECK) {
            $configValidation = [
                'config.url' => 'required|url|max:500',
            ];
        } else {
            return back()
                ->withErrors(['type' => 'Invalid step type'])
                ->withInput();
        }

        $request->validate($configValidation);

        // Update the step
        $step->update([
            'type' => $validated['type'],
            'config' => $validated['config'],
        ]);

        return redirect()
            ->route('workflows.show', $step->workflow_id)
            ->with('success', 'Step updated successfully!');
    }

    /**
     * Remove the specified step from storage.
     */
    public function destroy(Step $step)
    {
        $workflowId = $step->workflow_id;
        $step->delete();

        // Reorder remaining steps to fill the gap
        $workflow = Workflow::find($workflowId);
        $steps = $workflow->steps()->orderBy('step_order')->get();

        foreach ($steps as $index => $s) {
            $s->update(['step_order' => $index + 1]);
        }

        return redirect()
            ->route('workflows.show', $workflowId)
            ->with('success', 'Step deleted successfully!');
    }

    /**
     * Move a step up or down in the order.
     */
    public function move(Step $step, string $direction)
    {
        // Validate direction
        if (!in_array($direction, ['up', 'down'])) {
            return back()->with('error', 'Invalid direction. Must be "up" or "down".');
        }

        // Move the step
        if ($direction === 'up') {
            $step->moveUp();
            $message = 'Step moved up successfully!';
        } else {
            $step->moveDown();
            $message = 'Step moved down successfully!';
        }

        return redirect()
            ->route('workflows.show', $step->workflow_id)
            ->with('success', $message);
    }
}

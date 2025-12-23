<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Services\WorkflowRunner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkflowController extends Controller
{
    /**
     * Display a listing of workflows.
     */
    public function index()
    {
        $workflows = Workflow::with(['latestRun', 'steps'])
            ->latest()
            ->paginate(15);

        return view('workflows.index', compact('workflows'));
    }

    /**
     * Show the form for creating a new workflow.
     */
    public function create()
    {
        return view('workflows.create');
    }

    /**
     * Store a newly created workflow in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $workflow = Workflow::create($validated);

        return redirect()
            ->route('workflows.show', $workflow)
            ->with('success', 'Workflow created successfully!');
    }

    /**
     * Display the specified workflow.
     */
    public function show(Workflow $workflow)
    {
        $workflow->load(['steps', 'runs.logs']);

        return view('workflows.show', compact('workflow'));
    }

    /**
     * Show the form for editing the specified workflow.
     */
    public function edit(Workflow $workflow)
    {
        return view('workflows.edit', compact('workflow'));
    }

    /**
     * Update the specified workflow in storage.
     */
    public function update(Request $request, Workflow $workflow)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $workflow->update($validated);

        return redirect()
            ->route('workflows.show', $workflow)
            ->with('success', 'Workflow updated successfully!');
    }

    /**
     * Remove the specified workflow from storage.
     */
    public function destroy(Workflow $workflow)
    {
        $workflowName = $workflow->name;
        $workflow->delete();

        return redirect()
            ->route('workflows.index')
            ->with('success', "Workflow '{$workflowName}' deleted successfully!");
    }

    /**
     * Execute the workflow.
     */
    public function run(Workflow $workflow)
    {
        // Check if workflow has steps
        if ($workflow->steps()->count() === 0) {
            return redirect()
                ->route('workflows.show', $workflow)
                ->with('error', 'Cannot run workflow without steps. Please add at least one step.');
        }

        // Execute the workflow
        $runner = new WorkflowRunner();
        $run = $runner->execute($workflow);

        // Redirect to run details with appropriate message
        $message = match($run->status) {
            'succeeded' => "Workflow executed successfully in {$run->formatted_duration}!",
            'failed' => "Workflow execution failed. Check logs for details.",
            default => "Workflow execution completed with status: {$run->status}",
        };

        $flashType = $run->status === 'succeeded' ? 'success' : 'error';

        return redirect()
            ->route('runs.show', $run)
            ->with($flashType, $message);
    }
}

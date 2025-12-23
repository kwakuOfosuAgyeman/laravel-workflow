<?php

namespace Tests\Feature;

use App\Models\Workflow;
use App\Models\Step;
use App\Models\Run;
use App\Models\RunLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a workflow can be created.
     */
    public function test_can_create_workflow()
    {
        $response = $this->post('/workflows', [
            'name' => 'Test Workflow',
            'description' => 'This is a test workflow description',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('workflows', [
            'name' => 'Test Workflow',
            'description' => 'This is a test workflow description',
        ]);
    }

    /**
     * Test that workflow name is required.
     */
    public function test_workflow_name_is_required()
    {
        $response = $this->post('/workflows', [
            'name' => '',
            'description' => 'Test description',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test that a workflow can be viewed with its steps.
     */
    public function test_can_view_workflow_with_steps()
    {
        $workflow = Workflow::create([
            'name' => 'Test Workflow',
            'description' => 'Test Description',
        ]);

        $step1 = $workflow->steps()->create([
            'type' => Step::TYPE_DELAY,
            'config' => ['seconds' => 1],
            'step_order' => 1,
        ]);

        $step2 = $workflow->steps()->create([
            'type' => Step::TYPE_HTTP_CHECK,
            'config' => ['url' => 'https://example.com'],
            'step_order' => 2,
        ]);

        $response = $this->get("/workflows/{$workflow->id}");

        $response->assertStatus(200);
        $response->assertSee('Test Workflow');
        $response->assertSee('delay');
        $response->assertSee('http_check');
    }

    /**
     * Test that a step can be added to a workflow.
     */
    public function test_can_add_step_to_workflow()
    {
        $workflow = Workflow::create([
            'name' => 'Test Workflow',
            'description' => 'Test Description',
        ]);

        $response = $this->post("/workflows/{$workflow->id}/steps", [
            'type' => Step::TYPE_DELAY,
            'config' => ['seconds' => 2],
        ]);

        $response->assertRedirect("/workflows/{$workflow->id}");

        $this->assertDatabaseHas('steps', [
            'workflow_id' => $workflow->id,
            'type' => Step::TYPE_DELAY,
            'step_order' => 1,
        ]);

        $step = Step::where('workflow_id', $workflow->id)->first();
        $this->assertEquals(['seconds' => 2], $step->config);
    }

    /**
     * Test that steps can be reordered.
     */
    public function test_can_reorder_steps()
    {
        $workflow = Workflow::create([
            'name' => 'Test Workflow',
            'description' => 'Test Description',
        ]);

        $step1 = $workflow->steps()->create([
            'type' => Step::TYPE_DELAY,
            'config' => ['seconds' => 1],
            'step_order' => 1,
        ]);

        $step2 = $workflow->steps()->create([
            'type' => Step::TYPE_HTTP_CHECK,
            'config' => ['url' => 'https://example.com'],
            'step_order' => 2,
        ]);

        // Move step2 up (should swap with step1)
        $response = $this->patch("/steps/{$step2->id}/move/up");

        $response->assertRedirect("/workflows/{$workflow->id}");

        // Refresh from database
        $step1->refresh();
        $step2->refresh();

        $this->assertEquals(2, $step1->step_order);
        $this->assertEquals(1, $step2->step_order);
    }

    /**
     * Test that a step can be deleted.
     */
    public function test_can_delete_step()
    {
        $workflow = Workflow::create([
            'name' => 'Test Workflow',
            'description' => 'Test Description',
        ]);

        $step1 = $workflow->steps()->create([
            'type' => Step::TYPE_DELAY,
            'config' => ['seconds' => 1],
            'step_order' => 1,
        ]);

        $step2 = $workflow->steps()->create([
            'type' => Step::TYPE_HTTP_CHECK,
            'config' => ['url' => 'https://example.com'],
            'step_order' => 2,
        ]);

        $step3 = $workflow->steps()->create([
            'type' => Step::TYPE_DELAY,
            'config' => ['seconds' => 2],
            'step_order' => 3,
        ]);

        // Delete step2
        $response = $this->delete("/steps/{$step2->id}");

        $response->assertRedirect("/workflows/{$workflow->id}");

        // Step should be deleted
        $this->assertDatabaseMissing('steps', ['id' => $step2->id]);

        // Remaining steps should be reordered
        $step1->refresh();
        $step3->refresh();

        $this->assertEquals(1, $step1->step_order);
        $this->assertEquals(2, $step3->step_order); // Was 3, now 2
    }

    /**
     * Test that a workflow can be updated.
     */
    public function test_can_update_workflow()
    {
        $workflow = Workflow::create([
            'name' => 'Original Name',
            'description' => 'Original Description',
        ]);

        $response = $this->patch("/workflows/{$workflow->id}", [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ]);

        $response->assertRedirect("/workflows/{$workflow->id}");

        $workflow->refresh();

        $this->assertEquals('Updated Name', $workflow->name);
        $this->assertEquals('Updated Description', $workflow->description);
    }

    /**
     * Test that a workflow can be deleted.
     */
    public function test_can_delete_workflow()
    {
        $workflow = Workflow::create([
            'name' => 'Test Workflow',
            'description' => 'Test Description',
        ]);

        $workflow->steps()->create([
            'type' => Step::TYPE_DELAY,
            'config' => ['seconds' => 1],
            'step_order' => 1,
        ]);

        $response = $this->delete("/workflows/{$workflow->id}");

        $response->assertRedirect('/workflows');

        $this->assertDatabaseMissing('workflows', ['id' => $workflow->id]);
        $this->assertDatabaseMissing('steps', ['workflow_id' => $workflow->id]);
    }

    /**
     * Test that running a workflow creates a run record.
     */
    public function test_run_workflow_creates_run_record()
    {
        $workflow = Workflow::create([
            'name' => 'Test Workflow',
            'description' => 'Test Description',
        ]);

        $workflow->steps()->create([
            'type' => Step::TYPE_DELAY,
            'config' => ['seconds' => 0.1],
            'step_order' => 1,
        ]);

        $response = $this->post("/workflows/{$workflow->id}/run");

        $response->assertRedirect();

        $this->assertDatabaseHas('runs', [
            'workflow_id' => $workflow->id,
        ]);

        $run = Run::where('workflow_id', $workflow->id)->first();
        $this->assertNotNull($run->started_at);
        $this->assertNotNull($run->completed_at);
        $this->assertContains($run->status, ['succeeded', 'failed']);
    }

    /**
     * Test that running a workflow creates logs.
     */
    public function test_run_workflow_creates_logs()
    {
        $workflow = Workflow::create([
            'name' => 'Test Workflow',
            'description' => 'Test Description',
        ]);

        $step = $workflow->steps()->create([
            'type' => Step::TYPE_DELAY,
            'config' => ['seconds' => 0.1],
            'step_order' => 1,
        ]);

        $this->post("/workflows/{$workflow->id}/run");

        $run = Run::where('workflow_id', $workflow->id)->first();

        // Should have workflow-level and step-level logs
        $this->assertTrue($run->logs()->count() > 0);

        // Check for specific log entries
        $this->assertDatabaseHas('run_logs', [
            'run_id' => $run->id,
            'step_id' => $step->id,
            'level' => RunLog::LEVEL_INFO,
        ]);
    }

    /**
     * Test that a successful workflow is marked as succeeded.
     */
    public function test_successful_workflow_marks_as_succeeded()
    {
        $workflow = Workflow::create([
            'name' => 'Test Workflow',
            'description' => 'Test Description',
        ]);

        $workflow->steps()->create([
            'type' => Step::TYPE_DELAY,
            'config' => ['seconds' => 0.1],
            'step_order' => 1,
        ]);

        $this->post("/workflows/{$workflow->id}/run");

        $run = Run::where('workflow_id', $workflow->id)->first();

        $this->assertEquals(Run::STATUS_SUCCEEDED, $run->status);
        $this->assertNotNull($run->completed_at);
    }

    /**
     * Test that a failed step marks workflow as failed.
     */
    public function test_failed_step_marks_workflow_as_failed()
    {
        $workflow = Workflow::create([
            'name' => 'Test Workflow',
            'description' => 'Test Description',
        ]);

        // Create a delay step with invalid config (negative seconds)
        $workflow->steps()->create([
            'type' => Step::TYPE_DELAY,
            'config' => ['seconds' => -1],
            'step_order' => 1,
        ]);

        $this->post("/workflows/{$workflow->id}/run");

        $run = Run::where('workflow_id', $workflow->id)->first();

        $this->assertEquals(Run::STATUS_FAILED, $run->status);

        // Should have error log
        $this->assertDatabaseHas('run_logs', [
            'run_id' => $run->id,
            'level' => RunLog::LEVEL_ERROR,
        ]);
    }

    /**
     * Test that workflow execution stops on first failure.
     */
    public function test_workflow_stops_on_first_failure()
    {
        $workflow = Workflow::create([
            'name' => 'Test Workflow',
            'description' => 'Test Description',
        ]);

        // Step 1: Valid delay
        $step1 = $workflow->steps()->create([
            'type' => Step::TYPE_DELAY,
            'config' => ['seconds' => 0.1],
            'step_order' => 1,
        ]);

        // Step 2: Invalid delay (will fail)
        $step2 = $workflow->steps()->create([
            'type' => Step::TYPE_DELAY,
            'config' => ['seconds' => -1],
            'step_order' => 2,
        ]);

        // Step 3: Valid delay (should not execute)
        $step3 = $workflow->steps()->create([
            'type' => Step::TYPE_DELAY,
            'config' => ['seconds' => 0.1],
            'step_order' => 3,
        ]);

        $this->post("/workflows/{$workflow->id}/run");

        $run = Run::where('workflow_id', $workflow->id)->first();

        // Workflow should have failed
        $this->assertEquals(Run::STATUS_FAILED, $run->status);

        // Step 1 should have logs (executed)
        $step1Logs = RunLog::where('run_id', $run->id)
            ->where('step_id', $step1->id)
            ->count();
        $this->assertGreaterThan(0, $step1Logs);

        // Step 2 should have logs (executed and failed)
        $step2Logs = RunLog::where('run_id', $run->id)
            ->where('step_id', $step2->id)
            ->count();
        $this->assertGreaterThan(0, $step2Logs);

        // Step 3 should NOT have logs (never executed)
        $step3Logs = RunLog::where('run_id', $run->id)
            ->where('step_id', $step3->id)
            ->count();
        $this->assertEquals(0, $step3Logs);
    }

    /**
     * Test that cannot run workflow without steps.
     */
    public function test_cannot_run_workflow_without_steps()
    {
        $workflow = Workflow::create([
            'name' => 'Empty Workflow',
            'description' => 'No steps',
        ]);

        $response = $this->post("/workflows/{$workflow->id}/run");

        $response->assertRedirect("/workflows/{$workflow->id}");
        $response->assertSessionHas('error');

        // Should not create a run
        $this->assertDatabaseMissing('runs', [
            'workflow_id' => $workflow->id,
        ]);
    }

    /**
     * Test step config validation for delay type.
     */
    public function test_delay_step_requires_seconds()
    {
        $workflow = Workflow::create([
            'name' => 'Test Workflow',
            'description' => 'Test Description',
        ]);

        $response = $this->post("/workflows/{$workflow->id}/steps", [
            'type' => Step::TYPE_DELAY,
            'config' => ['invalid_key' => 'value'], // Has config but missing seconds
        ]);

        $response->assertSessionHasErrors('config.seconds');
    }

    /**
     * Test step config validation for http_check type.
     */
    public function test_http_check_step_requires_url()
    {
        $workflow = Workflow::create([
            'name' => 'Test Workflow',
            'description' => 'Test Description',
        ]);

        $response = $this->post("/workflows/{$workflow->id}/steps", [
            'type' => Step::TYPE_HTTP_CHECK,
            'config' => ['invalid_key' => 'value'], // Has config but missing url
        ]);

        $response->assertSessionHasErrors('config.url');
    }

    /**
     * Test that http_check validates URL format.
     */
    public function test_http_check_validates_url_format()
    {
        $workflow = Workflow::create([
            'name' => 'Test Workflow',
            'description' => 'Test Description',
        ]);

        $response = $this->post("/workflows/{$workflow->id}/steps", [
            'type' => Step::TYPE_HTTP_CHECK,
            'config' => ['url' => 'not-a-valid-url'],
        ]);

        $response->assertSessionHasErrors('config.url');
    }
}

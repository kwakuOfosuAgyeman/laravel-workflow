# AI Usage Appendix

## AI Tools Used
- **Claude (Anthropic)**: Used for complete backend architecture, service layer implementation, controllers, models, migrations, tests, and seeder
- **Gemini (Google)**: Used for frontend Blade templates and UI components

## High-impact Prompts 

### 1. Initial Architecture Planning
**Prompt:**
"Can you create a plan for implementing this project" (provided full Laravel workflow runner spec)

Followed the plan structure but adjusted time allocations as needed. The service layer separation (StepExecutors, Factory pattern) proved essential for testability and met all requirements.

### 2. Error with Runner not registering certain parts of the plan
**Prompt:**
"Fed it the error to help with identifying the parts of the code necessary for correction"

Correction where necessary to ensure when the workflow is started it always passes the right started_at

*
Ran workflow executions and verified logs were created correctly.

### 3. Logic Issue with Store function
**Prompt:**
"The store function, gave the impression that it would do the shifts if you for example had 3 steps and tried to insert a new step at step 2 but in reality it would always add to the end"

The AI readjusted the code to ensure that depending on the value entered when creating a step, it sufficiently adjusted the step_order values stored in the db.



### 4. Comprehensive Test Suite
**Prompt:**
"Can you create comprehensive workflow tests ensuring you retain the structure of the models"


17 feature tests covering:
- CRUD operations (6 tests)
- Step management with reordering (5 tests)
- Workflow execution with logs (6 tests)
- Edge cases like empty workflows and validation

**Validation **
Two tests failed initially due to empty config arrays not triggering nested validation. Fixed by sending config with dummy keys to properly test missing required fields. Also added HTTP::fake() for reliable testing without external dependencies.



## One Example Where AI Was Wrong (Required)

**What the AI suggested:**
In the StepController, AI initially used `$step->workflow_id` after the step was deleted:
```php
public function destroy(Step $step)
{
    $step->delete();
    return redirect()->route('workflows.show', $step->workflow_id); 
}
```

**Why it was incorrect:**
After calling `$step->delete()`, the model is removed from the database but the object still exists in memory with its original attributes. While this technically works in Laravel (soft deletes preserve attributes), it's poor practice and could fail with hard deletes or in edge cases.

**How you detected the issue:**
Code review - noticed the pattern of accessing properties after delete() which is fragile and unclear to future maintainers.

**What you changed:**
Store workflow_id before deletion:
```php
public function destroy(Step $step)
{
    $workflowId = $step->workflow_id; // ✅ Store before delete
    $step->delete();
    
    // Reorder remaining steps
    $workflow = Workflow::find($workflowId);
    // ... rest of logic
}
```

This makes the code more explicit and maintainable, even though both versions technically work in Laravel.

## Verification Approach

**Tests written:**
- 17 feature tests in `WorkflowTest.php`
- Coverage includes:
  - CRUD operations for workflows
  - Step management (create, update, delete, reorder)
  - Workflow execution with both success and failure paths
  - Validation for all input fields
  - Log creation verification
  - Sequential execution stopping on first failure

**Manual test script:**
1. Run seeder: `php artisan db:seed --class=WorkflowSeeder`
2. Navigate to workflows list
3. Create new workflow with name/description
4. Add multiple steps (delay and http_check)
5. Reorder steps using up/down buttons
6. Run workflow and verify:
   - Run record created with correct status
   - Logs show all step executions
   - Timestamps (started_at, completed_at) populated
   - Duration calculated correctly
7. Test failure path by adding invalid URL
8. Verify workflow stops on first failure

**Linters/formatters used:**
- Laravel Pint (PSR-12 code style)
- PHPStan for static analysis
- Native Laravel validation for all inputs

## Time Breakdown (Estimate)

- **Setup/scaffolding:** 10 minutes
  - Laravel installation
  - Database configuration (SQLite)
  - Initial migration setup

- **Backend core:** 60 minutes
  - Models with relationships and helpers: 15 min
  - Service layer (executors, factory, runner): 25 min
  - Controllers with validation: 20 min
  - Seeder: 5 min

- **Frontend core:** 30 minutes
  - Blade layouts and components (Gemini)
  - Forms with dynamic validation
  - Step management UI with reordering
  - Run logs display

- **Tests:** 10 minutes
  - Writing 17 feature tests: 5 min
  - Debugging and fixing test issues: 5 min

- **Cleanup/README:** 12 minutes
  - Documentation
  - .env.example
  - AI appendix
  - Code cleanup

**Total:** ~2 hours 


**Future Works**
Was unable to implement the movement on the frontend, logic does exist on the backend
Currently the system is only able to handle get requests for the http requests, later changes would have to be made to allow GET, PUT, POST and other request types
Also implement a form of parrallel execution so that users don't have to wait for one to run before going unto the others (using queues, jobs)
It also doesn't have retry logic for if there is unforeseen circumstances such as network problems
Other architectural problems as well as security requirements, better rate limiting and more advanced validation to help the system handle attacks better.



**Installation**
Install PHP Dependencies
Install the backend dependencies defined in the composer.json file:
```bash
composer install
```

You might also run composer update if you need to update existing dependencies.
Create and Configure the Environment File
Laravel projects use a .env file for configuration, which is usually not included in the repository for security reasons. Copy the example environment file to create your own:
For Windows (Command Prompt):
```bash
copy .env.example .env
```
For macOS or Linux (Terminal):
```bash
cp .env.example .env
```
Edit the .env File
Open the newly created .env file in a text editor. Update the DB_ variables to match your local database configuration (database name, username, and password).

env
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```
Generate an Application Key
Generate a unique application key. This sets the APP_KEY variable in your .env file and is crucial for security and encryption.
```bash
php artisan key:generate
```
Run Database Migrations
Set up your database tables by running the migrations and you can also seed with the default test data:
```bash
php artisan migrate
php artisan db:seed
```


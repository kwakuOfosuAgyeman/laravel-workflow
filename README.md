# laravel-workflow

Got a workflow error having to do with the runner not registering the started_at and completed_at
The store function, gave the impression that it would do the shifts if you for example had 3 steps and tried to insert a new step at step 2 but in reality it would always add to the end

@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Create Workflow</div>
            <div class="card-body">
                <form action="{{ route('workflows.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Workflow Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Daily Data Backup">
                    </div>
                    <button type="submit" class="btn btn-primary">Create Workflow</button>
                    <a href="{{ route('workflows.index') }}" class="btn btn-link">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

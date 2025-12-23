@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Workflows</h1>
    <a href="{{ route('workflows.create') }}" class="btn btn-primary">Create New Workflow</a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workflows as $workflow)
                <tr>
                    <td>{{ $workflow->id }}</td>
                    <td>{{ $workflow->name }}</td>
                    <td class="text-end">
                        <a href="{{ route('workflows.show', $workflow) }}" class="btn btn-sm btn-info text-white">View</a>
                        <a href="{{ route('workflows.edit', $workflow) }}" class="btn btn-sm btn-secondary">Edit</a>
                        <form action="{{ route('workflows.run', $workflow) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">Run Now</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center">No workflows found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

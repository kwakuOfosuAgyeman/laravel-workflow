@extends('layouts.app')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('workflows.index') }}">Workflows</a></li>
                <li class="breadcrumb-item active">{{ $workflow->name }}</li>
            </ol>
        </nav>
        <h2 class="mb-0">{{ $workflow->name }}</h2>
        @if($workflow->description)
            <p class="text-muted mb-0">{{ $workflow->description }}</p>
        @endif
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('workflows.edit', $workflow) }}" class="btn btn-outline-primary">Edit Details</a>

        <form action="{{ route('workflows.run', $workflow) }}" method="POST" onsubmit="return confirm('Start this workflow now?')">
            @csrf
            <button type="submit" class="btn btn-success">
                ▶ Run Workflow
            </button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <h4>Steps Schedule</h4>
        <div class="list-group mb-4">
            @forelse($workflow->steps->sortBy('step_order') as $step)
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <h6 class="mb-1">
                            <span class="badge bg-secondary me-2">Step {{ $step->step_order }}</span>
                            <strong>{{ $step->type === 'delay' ? '⏳ DELAY' : '🌐 HTTP CHECK' }}</strong>
                        </h6>
                    </div>

                    <div class="mt-2 small">
                        @if($step->type === 'delay')
                            <p class="mb-0 text-muted">
                                Pause execution for <strong>{{ $step->config['seconds'] ?? 0 }} seconds</strong>.
                            </p>
                        @elseif($step->type === 'http_check')
                            <p class="mb-0 text-muted">
                                Ping URL: <code class="text-primary">{{ $step->config['url'] ?? 'N/A' }}</code>
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="list-group-item text-center py-3 text-muted">
                    No steps added to this workflow yet.
                </div>
            @endforelse
        </div>

        <h4 class="mt-5">Recent Executions</h4>
        <div class="card shadow-sm mb-4">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Run Date</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workflow->runs()->latest()->take(5)->get() as $run)
                            <tr>
                                <td class="align-middle">{{ $run->created_at->format('M d, H:i') }}</td>
                                <td class="align-middle">
                                    @if($run->status === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($run->status === 'failed')
                                        <span class="badge bg-danger">Failed</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Running</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('runs.show', $run) }}" class="btn btn-sm btn-outline-secondary">View Logs</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">No runs recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white font-weight-bold">Add New Step</div>
            <div class="card-body">
                <form action="{{ route('workflows.steps.store', $workflow) }}" method="POST">
                    @csrf
                    @include('steps._form')
                    <button type="submit" class="btn btn-primary w-100 mt-3">Save Step</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

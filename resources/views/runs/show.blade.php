@extends('layouts.app')

@section('content')
<div class="mb-3">
    <a href="{{ route('workflows.show', $run->workflow_id) }}" class="text-decoration-none">&larr; Back to Workflow</a>
</div>
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Run #{{ $run->id }} Details</h5>

        @if($run->status == 'succeeded')
            <span class="badge bg-success">Completed</span>
        @elseif($run->status == 'failed')
            <span class="badge bg-danger">Failed</span>
        @elseif($run->status ==  "running")
            <span class="badge bg-warning text-dark">Running</span>
        @else
            <span class="badge bg-warning text-yellow">Pending</span>
        @endif
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 180px;" class="ps-3">Timestamp</th>
                        <th style="width: 100px;">Level</th>
                        <th>Message</th>
                        <th>Step Context</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Decode the JSON log if it's still a string
                        $logs = is_array($run->logs) ? $run->logs : json_decode($run->logs, true);
                    @endphp

                    @forelse($logs as $log)
                        <tr class="{{ $log['level'] === 'error' ? 'table-danger' : '' }}">
                            <td class="ps-3">
                                <small class="text-muted font-monospace">
                                    {{ \Carbon\Carbon::parse($log['timestamp'])->format('H:i:s.v') }}
                                </small>
                            </td>
                            <td>
                                @php
                                    $bootstrapClass = str_contains($log['level_badge_class'], 'red') ? 'bg-danger' : 'bg-info';
                                @endphp
                                <span class="badge {{ $bootstrapClass }}">{{ strtoupper($log['level']) }}</span>
                            </td>
                            <td>
                                <span class="{{ $log['level'] === 'error' ? 'fw-bold' : '' }}">
                                    {{ $log['message'] }}
                                </span>
                            </td>
                            <td>
                                @if($log['step'])
                                    <span class="badge bg-light text-dark border">
                                        {{ $log['step']['type'] }} (Order: {{ $log['step']['step_order'] }})
                                    </span>
                                @else
                                    <span class="text-muted small">System</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No logs recorded for this run.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

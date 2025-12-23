@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-primary">Edit Workflow Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('workflows.update', $workflow) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label font-weight-bold">Workflow Name</label>
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $workflow->name) }}"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label font-weight-bold">Description</label>
                        <textarea name="description"
                                  id="description"
                                  class="form-control @error('description') is-invalid @enderror"
                                  rows="4"
                                  placeholder="What is the purpose of this workflow?">{{ old('description', $workflow->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text text-muted">A brief summary of what these steps accomplish.</div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('workflows.show', $workflow) }}" class="btn btn-link text-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Update Workflow</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

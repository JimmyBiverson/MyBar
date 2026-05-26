@extends('layouts.app')
@section('title', isset($branch) ? 'Edit Branch' : 'Add Branch')
@section('page-title', isset($branch) ? 'Edit Branch' : 'Add Branch')

@section('breadcrumb-plugins')
    <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ isset($branch) ? route('branches.update', $branch->id) : route('branches.store') }}">
                    @csrf
                    @isset($branch) @method('PUT') @endisset
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $branch->name ?? '') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control @error('location') is-invalid @enderror" name="location" value="{{ old('location', $branch->location ?? '') }}">
                        @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" name="address" rows="2">{{ old('address', $branch->address ?? '') }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $branch->phone ?? '') }}">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $branch->email ?? '') }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city', $branch->city ?? '') }}">
                        @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" value="1" id="isActive" {{ old('is_active', $branch->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">Active</label>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ isset($branch) ? 'Update' : 'Save' }} Branch</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

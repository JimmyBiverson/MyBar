@extends('layouts.app')
@section('title', isset($category) ? 'Edit Category' : 'Add Category')
@section('page-title', isset($category) ? 'Edit Category' : 'Add Category')

@section('breadcrumb-plugins')
    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ isset($category) ? route('categories.update', $category->id) : route('categories.store') }}">
                    @csrf
                    @isset($category) @method('PUT') @endisset
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $category->name ?? '') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description', $category->description ?? '') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" value="1" id="isActive" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">Active</label>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ isset($category) ? 'Update' : 'Save' }} Category</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

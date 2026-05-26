@extends('layouts.app')
@section('title', isset($unit) ? 'Edit Unit' : 'Add Unit')
@section('page-title', isset($unit) ? 'Edit Unit' : 'Add Unit')

@section('breadcrumb-plugins')
    <a href="{{ route('units.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ isset($unit) ? route('units.update', $unit->id) : route('units.store') }}">
                    @csrf
                    @isset($unit) @method('PUT') @endisset
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $unit->name ?? '') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Short Code</label>
                        <input type="text" class="form-control @error('short_code') is-invalid @enderror" name="short_code" value="{{ old('short_code', $unit->short_code ?? '') }}" placeholder="e.g. kg, pcs, ltr" maxlength="50">
                        @error('short_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ isset($unit) ? 'Update' : 'Save' }} Unit</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

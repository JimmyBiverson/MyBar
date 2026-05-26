@extends('layouts.app')
@section('title', isset($table) ? 'Edit Table' : 'Add Table')
@section('page-title', isset($table) ? 'Edit Table' : 'Add Table')

@section('breadcrumb-plugins')
    <a href="{{ route('tables.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ isset($table) ? route('tables.update', $table->id) : route('tables.store') }}">
                    @csrf
                    @isset($table) @method('PUT') @endisset
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Table Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $table->name ?? '') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Table Number</label>
                            <input type="number" class="form-control @error('table_number') is-invalid @enderror" name="table_number" value="{{ old('table_number', $table->table_number ?? '') }}">
                            @error('table_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Capacity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('capacity') is-invalid @enderror" name="capacity" value="{{ old('capacity', $table->capacity ?? 4) }}" required>
                            @error('capacity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" name="status">
                                <option value="available" {{ old('status', $table->status ?? '') === 'available' ? 'selected' : '' }}>Available</option>
                                <option value="occupied" {{ old('status', $table->status ?? '') === 'occupied' ? 'selected' : '' }}>Occupied</option>
                                <option value="reserved" {{ old('status', $table->status ?? '') === 'reserved' ? 'selected' : '' }}>Reserved</option>
                                <option value="maintenance" {{ old('status', $table->status ?? '') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" value="{{ old('location', $table->location ?? '') }}" placeholder="e.g. Indoor, Patio">
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ isset($table) ? 'Update' : 'Save' }} Table</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

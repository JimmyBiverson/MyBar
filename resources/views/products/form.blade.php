@extends('layouts.app')
@section('title', isset($product) ? 'Edit Product' : 'Add Product')
@section('page-title', isset($product) ? 'Edit Product' : 'Add Product')

@section('breadcrumb-plugins')
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ isset($product) ? route('products.update', $product->id) : route('products.store') }}" enctype="multipart/form-data">
                    @csrf
                    @isset($product) @method('PUT') @endisset

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $product->name ?? '') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" name="category_id" required>
                                <option value="">Select Category</option>
                                @foreach(\App\Models\Category::all() as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Selling Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('selling_price') is-invalid @enderror" name="selling_price" value="{{ old('selling_price', $product->selling_price ?? '') }}" required>
                            @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cost Price</label>
                            <input type="number" step="0.01" class="form-control @error('cost_price') is-invalid @enderror" name="cost_price" value="{{ old('cost_price', $product->cost_price ?? '') }}">
                            @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unit</label>
                            <select class="form-select @error('unit_id') is-invalid @enderror" name="unit_id">
                                <option value="">Select Unit</option>
                                @foreach(\App\Models\Unit::all() as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id ?? '') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stock</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('current_stock') is-invalid @enderror" name="current_stock" value="{{ old('current_stock', $product->current_stock ?? 0) }}">
                                @if(isset($product))
                                <span class="input-group-text p-0">
                                    <span class="badge bg-{{ $product->stock_status === 'low' ? 'danger' : ($product->stock_status === 'medium' ? 'warning' : 'success') }} d-block rounded-0 px-2 py-1">
                                        {{ ucfirst($product->stock_status) }}
                                    </span>
                                </span>
                                @endif
                            </div>
                            @error('current_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Reorder Level</label>
                            <input type="number" class="form-control @error('reorder_level') is-invalid @enderror" name="reorder_level" value="{{ old('reorder_level', $product->reorder_level ?? 10) }}">
                            @error('reorder_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Barcode</label>
                            <input type="text" class="form-control @error('barcode') is-invalid @enderror" name="barcode" value="{{ old('barcode', $product->barcode ?? '') }}">
                            @error('barcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="2">{{ old('description', $product->description ?? '') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" value="1" id="isActive" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">Active</label>
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> {{ isset($product) ? 'Update' : 'Save' }} Product
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

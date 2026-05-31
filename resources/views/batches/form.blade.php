@extends('layouts.app')
@section('title', isset($batch) ? 'Edit Batch' : 'New Batch')
@section('page-title', isset($batch) ? 'Edit Batch' : 'New Batch')

@section('breadcrumb-plugins')
    <a href="{{ route('batches.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ isset($batch) ? route('batches.update', $batch->id) : route('batches.store') }}">
                    @csrf
                    @isset($batch) @method('PUT') @endisset
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Batch No <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('batch_no') is-invalid @enderror" name="batch_no" value="{{ old('batch_no', $batch->batch_no ?? 'BATCH-' . date('Ymd-His')) }}" required>
                            @error('batch_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Product <span class="text-danger">*</span></label>
                            <select class="form-select @error('product_id') is-invalid @enderror" name="product_id" required>
                                <option value="">Select Product</option>
                                @foreach($products ?? [] as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id', $batch->product_id ?? '') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                @endforeach
                            </select>
                            @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('quantity') is-invalid @enderror" name="quantity" value="{{ old('quantity', $batch->quantity ?? '') }}" required>
                            @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cost Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('cost_price') is-invalid @enderror" name="cost_price" value="{{ old('cost_price', $batch->cost_price ?? '') }}" required>
                            @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Remaining</label>
                            <input type="number" step="0.01" class="form-control" name="remaining" value="{{ old('remaining', $batch->remaining ?? '') }}" @isset($batch) readonly @else value="0" @endisset>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Supplier</label>
                            <select class="form-select @error('supplier_id') is-invalid @enderror" name="supplier_id">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers ?? [] as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id', $batch->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Purchase Reference</label>
                            <select class="form-select @error('purchase_id') is-invalid @enderror" name="purchase_id">
                                <option value="">Select Purchase</option>
                                @foreach($purchases ?? [] as $purchase)
                                    <option value="{{ $purchase->id }}" {{ old('purchase_id', $batch->purchase_id ?? '') == $purchase->id ? 'selected' : '' }}>{{ $purchase->reference_no }}</option>
                                @endforeach
                            </select>
                            @error('purchase_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" name="expiry_date" value="{{ old('expiry_date', isset($batch) && $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : '') }}">
                            @error('expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" rows="2">{{ old('notes', $batch->notes ?? '') }}</textarea>
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ isset($batch) ? 'Update' : 'Save' }} Batch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

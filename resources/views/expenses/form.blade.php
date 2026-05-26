@extends('layouts.app')
@section('title', isset($expense) ? 'Edit Expense' : 'Add Expense')
@section('page-title', isset($expense) ? 'Edit Expense' : 'Add Expense')

@section('breadcrumb-plugins')
    <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ isset($expense) ? route('expenses.update', $expense->id) : route('expenses.store') }}">
                    @csrf
                    @isset($expense) @method('PUT') @endisset
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('description') is-invalid @enderror" name="description" value="{{ old('description', $expense->description ?? '') }}" required>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" name="category_id" required>
                                <option value="">Select Category</option>
                                @foreach(\App\Models\ExpenseCategory::all() as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $expense->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" name="amount" value="{{ old('amount', $expense->amount ?? '') }}" required>
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror" name="date" value="{{ old('date', isset($expense) ? $expense->date->format('Y-m-d') : date('Y-m-d')) }}" required>
                            @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_method">
                                <option value="cash" {{ old('payment_method', $expense->payment_method ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="mobile_money" {{ old('payment_method', $expense->payment_method ?? '') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                <option value="bank" {{ old('payment_method', $expense->payment_method ?? '') === 'bank' ? 'selected' : '' }}>Bank</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="2">{{ old('notes', $expense->notes ?? '') }}</textarea>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ isset($expense) ? 'Update' : 'Save' }} Expense</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

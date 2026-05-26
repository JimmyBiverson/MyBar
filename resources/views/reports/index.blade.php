@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.index') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Report Type</label>
                <select class="form-select" name="type" id="reportType">
                    <option value="daily-sales" {{ request('type') === 'daily-sales' ? 'selected' : '' }}>Daily Sales</option>
                    <option value="monthly-sales" {{ request('type') === 'monthly-sales' ? 'selected' : '' }}>Monthly Sales</option>
                    <option value="profit-loss" {{ request('type') === 'profit-loss' ? 'selected' : '' }}>Profit & Loss</option>
                    <option value="inventory" {{ request('type') === 'inventory' ? 'selected' : '' }}>Inventory Report</option>
                    <option value="product-performance" {{ request('type') === 'product-performance' ? 'selected' : '' }}>Product Performance</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" name="from" value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" name="to" value="{{ request('to', now()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-chart-simple me-1"></i> Generate
                </button>
            </div>
        </form>

        @if(session('reportData'))
        <div class="text-end mb-3">
            <a href="{{ route('reports.export-pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm"><i class="fas fa-file-pdf me-1"></i> PDF</a>
            <a href="{{ route('reports.export-excel', request()->query()) }}" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel me-1"></i> Excel</a>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
        </div>
        @endif
    </div>
</div>

@if(session('reportData'))
    @php $data = session('reportData'); $type = request('type'); @endphp

    @if($type === 'daily-sales')
        @include('reports.daily-sales')
    @elseif($type === 'monthly-sales')
        @include('reports.monthly-sales')
    @elseif($type === 'profit-loss')
        @include('reports.profit-loss')
    @elseif($type === 'inventory')
        @include('reports.inventory')
    @elseif($type === 'product-performance')
        @include('reports.product-performance')
    @endif
@else
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="fas fa-chart-bar fa-4x mb-3"></i>
            <h5>Select a report type and date range, then click Generate</h5>
        </div>
    </div>
@endif
@endsection

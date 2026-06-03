@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports')

@push('styles')
<style>
    @media print {
        .sidebar, .topbar, footer, .no-print, .breadcrumb-plugins, .card:first-of-type {
            display: none !important;
        }
        .main-content { margin-left: 0 !important; }
        .page-content { padding: 0 !important; }
        .app-layout { display: block !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; border-radius: 4px !important; page-break-inside: avoid; }
        .card-header { background: #f5f5f5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .table { font-size: 10px; }
        .table th { background: #f0f0f0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .bg-success-subtle, .bg-danger-subtle { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .border-rounded { border: 1px solid #ddd !important; }
        body { background: white !important; }
        .page-break { page-break-before: always; }
        .print-header { display: block !important; }
        .print-only { display: block !important; }
        .d-print-none { display: none !important; }
    }
    .print-header { display: none; text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
    .print-header h2 { margin: 0; font-size: 18px; }
    .print-header p { margin: 4px 0 0; color: #666; }
    .report-card { transition: all 0.2s; }
    .report-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
    .stat-box { padding: 1rem; border-radius: 10px; background: #f8f9fa; }
    .dark .stat-box { background: #1e2126; }
</style>
@endpush

@section('content')
<div class="card no-print">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Report Type</label>
                <select class="form-select" name="type" id="reportType" onchange="toggleDateFields()">
                    <option value="">-- Select Report --</option>
                    <option value="daily-sales" {{ ($type ?? '') === 'daily-sales' ? 'selected' : '' }}>Daily Sales</option>
                    <option value="monthly-sales" {{ ($type ?? '') === 'monthly-sales' ? 'selected' : '' }}>Monthly Sales</option>
                    <option value="profit-loss" {{ ($type ?? '') === 'profit-loss' ? 'selected' : '' }}>Profit & Loss</option>
                    <option value="inventory" {{ ($type ?? '') === 'inventory' ? 'selected' : '' }}>Inventory Report</option>
                    <option value="product-performance" {{ ($type ?? '') === 'product-performance' ? 'selected' : '' }}>Product Performance</option>
                </select>
            </div>
            <div class="col-md-3" id="fromDateField">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" name="from" value="{{ $dateFrom ? $dateFrom->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d') }}">
            </div>
            <div class="col-md-3" id="toDateField">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" name="to" value="{{ $dateTo ? $dateTo->format('Y-m-d') : now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-chart-simple me-1"></i> Generate
                </button>
            </div>
        </form>
    </div>
</div>

@if($type && count($data) > 0)
<div class="print-header">
    <h2>{{ config('app.name', 'MyBar') }}</h2>
    <p>{{ ucfirst(str_replace('-', ' ', $type)) }} Report</p>
    @if(isset($data['report_date']))
    <p>{{ $data['report_date'] }}</p>
    @elseif(isset($data['report_month']))
    <p>{{ $data['report_month'] }}</p>
    @elseif(isset($data['report_period']))
    <p>{{ $data['report_period'] }}</p>
    @endif
</div>

<div class="text-end mb-3 no-print">
    <a href="{{ route('reports.export-pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm"><i class="fas fa-file-pdf me-1"></i> PDF</a>
    <a href="{{ route('reports.export-excel', request()->query()) }}" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel me-1"></i> Excel</a>
    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
</div>

@if($type === 'daily-sales')
    @include('reports.daily-sales', ['data' => $data])
@elseif($type === 'monthly-sales')
    @include('reports.monthly-sales', ['data' => $data])
@elseif($type === 'profit-loss')
    @include('reports.profit-loss', ['data' => $data])
@elseif($type === 'inventory')
    @include('reports.inventory', ['data' => $data])
@elseif($type === 'product-performance')
    @include('reports.product-performance', ['data' => $data])
@endif
@elseif($type)
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="fas fa-chart-bar fa-4x mb-3"></i>
        <h5>No data available for the selected criteria</h5>
        <p>Try adjusting the date range or selecting a different report type.</p>
    </div>
</div>
@else
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="fas fa-chart-bar fa-4x mb-3"></i>
        <h5>Select a report type and date range, then click Generate</h5>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function toggleDateFields() {
    var type = document.getElementById('reportType').value;
    var fromField = document.getElementById('fromDateField');
    var toField = document.getElementById('toDateField');
    if (type === 'inventory' || type === '') {
        fromField.style.display = 'none';
        toField.style.display = 'none';
    } else if (type === 'daily-sales') {
        fromField.style.display = 'block';
        toField.style.display = 'none';
    } else {
        fromField.style.display = 'block';
        toField.style.display = 'block';
    }
}
toggleDateFields();
</script>
@endpush

@extends('layouts.app')
@section('title', 'Tables')
@section('page-title', 'Tables')

@section('content')
<div x-data="waiterTables()" x-init="init()">
    <div class="row g-3">
        <template x-for="table in tables" :key="table.id">
            <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                <div class="card text-center h-100 table-card" :class="'border-' + table.borderColor" @click="selectTable(table)">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center" style="min-height:180px;">
                        <i class="fas fa-chair fa-3x mb-2" :class="'text-' + table.color"></i>
                        <h5 class="mb-1 fw-bold" x-text="table.name"></h5>
                        <span class="badge mb-1 fs-6 px-3 py-1" :class="'bg-' + table.color" x-text="table.status_label"></span>
                        <small class="text-muted" x-text="'Capacity: ' + table.capacity"></small>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table-card { cursor: pointer; transition: all 0.2s; }
    .table-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
    .table-card:active { transform: scale(0.97); }
</style>
@endpush

@push('scripts')
<script>
    function waiterTables() {
        return {
            tables: [],
            async init() {
                try {
                    const resp = await fetch('{{ route('waiter.tables.data') }}');
                    const data = await resp.json();
                    this.tables = (data.tables || []).map(t => ({
                        ...t,
                        color: t.status === 'available' ? 'success' : (t.status === 'occupied' ? 'danger' : (t.status === 'reserved' ? 'info' : 'secondary')),
                        borderColor: t.status === 'available' ? 'border-success' : (t.status === 'occupied' ? 'border-danger' : (t.status === 'reserved' ? 'border-info' : 'border-secondary')),
                        status_label: t.status.charAt(0).toUpperCase() + t.status.slice(1)
                    }));
                } catch(e) { this.tables = []; }
            },
            selectTable(table) {
                if (table.status === 'available') {
                    window.location.href = '{{ route('waiter.orders.create') }}?table_id=' + table.id;
                } else if (table.status === 'occupied') {
                    window.location.href = '{{ route('waiter.orders') }}?table_id=' + table.id;
                }
            }
        }
    }
</script>
@endpush

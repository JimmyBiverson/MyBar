@extends('layouts.app')
@section('title', 'Expenses')
@section('page-title', 'Expenses')

@section('breadcrumb-plugins')
    <a href="{{ route('expenses.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Add New
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
            <div class="d-flex gap-2 flex-wrap">
                <div class="input-group input-group-sm" style="max-width:250px;">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="tableSearch" placeholder="Search expenses...">
                </div>
                <input type="date" class="form-control form-control-sm" id="dateFrom" style="max-width:140px;">
                <input type="date" class="form-control form-control-sm" id="dateTo" style="max-width:140px;">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>Description</th><th>Category</th><th>Amount</th><th>Date</th><th>Recorded By</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($expenses ?? [] as $expense)
                    <tr>
                        <td class="fw-medium">{{ $expense->description }}</td>
                        <td>{{ $expense->category ?? 'N/A' }}</td>
                        <td>{{ formatCurrency($expense->amount) }}</td>
                        <td><small>{{ $expense->date->format('d M Y') }}</small></td>
                        <td>{{ $expense->createdBy->name ?? 'N/A' }}</td>
                        <td class="text-end">
                            <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('expenses.destroy', $expense->id) }}')"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No expenses found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($expenses ?? [], 'links'))<div class="d-flex justify-content-end">{{ $expenses->links() }}</div>@endif
    </div>
</div>
<form id="deleteForm" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endsection
@push('scripts')
<script>
    document.getElementById('tableSearch')?.addEventListener('keyup', function() {
        const v = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(tr => tr.style.display = tr.textContent.toLowerCase().includes(v) ? '' : 'none');
    });
</script>
@endpush

@extends('layouts.app')
@section('title', 'Batches')
@section('page-title', 'Batches')

@section('breadcrumb-plugins')
    <a href="{{ route('batches.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> New Batch
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
            <div class="input-group input-group-sm" style="max-width:250px;">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="tableSearch" placeholder="Search batches...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>Batch No</th><th>Product</th><th class="text-end">Quantity</th><th class="text-end">Remaining</th><th class="text-end">Cost Price</th><th>Supplier</th><th>Expiry</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($batches ?? [] as $batch)
                    <tr>
                        <td class="fw-medium">{{ $batch->batch_no }}</td>
                        <td>{{ $batch->product->name ?? 'N/A' }}</td>
                        <td class="text-end">{{ number_format($batch->quantity) }}</td>
                        <td class="text-end">
                            <span class="badge bg-{{ $batch->remaining > 0 ? 'success' : 'secondary' }}">{{ number_format($batch->remaining) }}</span>
                        </td>
                        <td class="text-end">{{ number_format($batch->cost_price) }}</td>
                        <td>{{ $batch->supplier->name ?? 'N/A' }}</td>
                        <td>{{ $batch->expiry_date?->format('d M Y') ?? 'N/A' }}</td>
                        <td class="text-end">
                            <a href="{{ route('batches.edit', $batch->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('batches.destroy', $batch->id) }}')"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No batches found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($batches ?? [], 'links'))<div class="d-flex justify-content-end">{{ $batches->links() }}</div>@endif
    </div>
</div>
<form id="deleteForm" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endsection
@push('scripts')
<script>
    function confirmDelete(url) {
        Swal.fire({ title:'Are you sure?', text:"This action cannot be undone!", icon:'warning', showCancelButton:true, confirmButtonColor:'#dc3545', confirmButtonText:'Yes, delete it!' })
        .then((r) => { if(r.isConfirmed) { const f = document.getElementById('deleteForm'); f.action = url; f.submit(); } });
    }
    document.getElementById('tableSearch')?.addEventListener('keyup', function() {
        const v = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(tr => tr.style.display = tr.textContent.toLowerCase().includes(v) ? '' : 'none');
    });
</script>
@endpush

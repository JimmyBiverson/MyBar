@extends('layouts.app')
@section('title', 'Units')
@section('page-title', 'Units')

@section('breadcrumb-plugins')
    <a href="{{ route('units.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Add New
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <div class="input-group input-group-sm" style="max-width:300px;">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="tableSearch" placeholder="Search units...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>Name</th><th>Short Code</th><th>Products</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($units ?? [] as $unit)
                    <tr>
                        <td class="fw-medium">{{ $unit->name }}</td>
                        <td>{{ $unit->short_code ?? 'N/A' }}</td>
                        <td>{{ $unit->products_count ?? $unit->products()->count() }}</td>
                        <td class="text-end">
                            <a href="{{ route('units.edit', $unit->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('units.destroy', $unit->id) }}')"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No units found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($units ?? [], 'links'))<div class="d-flex justify-content-end">{{ $units->links() }}</div>@endif
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

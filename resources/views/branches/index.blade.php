@extends('layouts.app')
@section('title', 'Branches')
@section('page-title', 'Branches')

@section('breadcrumb-plugins')
    <a href="{{ route('branches.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> New Branch
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <div class="input-group input-group-sm" style="max-width:250px;">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="tableSearch" placeholder="Search branches...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>Name</th><th>Location</th><th>Phone</th><th>Email</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($branches as $branch)
                    <tr>
                        <td class="fw-medium">{{ $branch->name }}</td>
                        <td>{{ $branch->location ?? 'N/A' }}</td>
                        <td>{{ $branch->phone ?? 'N/A' }}</td>
                        <td>{{ $branch->email ?? 'N/A' }}</td>
                        <td><span class="badge bg-{{ $branch->is_active ? 'success' : 'danger' }}">{{ $branch->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('branches.destroy', $branch->id) }}')"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No branches found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($branches, 'links'))<div class="d-flex justify-content-end">{{ $branches->links() }}</div>@endif
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

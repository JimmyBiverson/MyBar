@extends('layouts.app')
@section('title', 'Categories')
@section('page-title', 'Categories')

@section('breadcrumb-plugins')
    <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Add New
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <div class="input-group input-group-sm" style="max-width:300px;">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="tableSearch" placeholder="Search categories...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>Name</th><th>Description</th><th>Products</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($categories ?? [] as $category)
                    <tr>
                        <td class="fw-medium">{{ $category->name }}</td>
                        <td>{{ Str::limit($category->description, 40) }}</td>
                        <td>{{ $category->products_count ?? $category->products()->count() }}</td>
                        <td><span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('categories.destroy', $category->id) }}')"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No categories found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($categories ?? [], 'links'))<div class="d-flex justify-content-end">{{ $categories->links() }}</div>@endif
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

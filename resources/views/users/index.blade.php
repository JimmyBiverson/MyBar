@extends('layouts.app')
@section('title', 'Users')
@section('page-title', 'Users & Roles')

@section('breadcrumb-plugins')
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Add New
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
            <div class="input-group input-group-sm" style="max-width:300px;">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="tableSearch" placeholder="Search users...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>Name</th><th>Email</th><th>Role</th><th>Branch</th><th>Status</th><th>Last Login</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($users ?? [] as $user)
                    <tr>
                        <td class="fw-medium">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ $user->role->name ?? 'N/A' }}</span></td>
                        <td>{{ $user->branch->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td><small>{{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : 'Never' }}</small></td>
                        <td class="text-end">
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            @if($user->id !== 1)
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('users.destroy', $user->id) }}')"><i class="fas fa-trash"></i></button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No users found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($users ?? [], 'links'))<div class="d-flex justify-content-end">{{ $users->links() }}</div>@endif
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

@extends('layouts.app')
@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
            <div class="input-group input-group-sm" style="max-width:250px;">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="tableSearch" placeholder="Search logs...">
            </div>
            <select class="form-select form-select-sm" id="actionFilter" style="max-width:150px;">
                <option value="">All Actions</option>
                @foreach($actions ?? [] as $action)
                    <option value="{{ $action }}">{{ ucfirst($action) }}</option>
                @endforeach
            </select>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>Time</th><th>User</th><th>Action</th><th>Description</th><th>Subject</th></tr>
                </thead>
                <tbody>
                    @forelse($activities ?? [] as $log)
                    <tr>
                        <td><small>{{ $log->created_at->format('d M Y H:i:s') }}</small></td>
                        <td>{{ $log->user->name ?? 'System' }}</td>
                        <td><span class="badge bg-info">{{ ucfirst($log->action) }}</span></td>
                        <td>{{ $log->description }}</td>
                        <td><small class="text-muted">{{ class_basename($log->subject_type ?? '') }} #{{ $log->subject_id ?? '' }}</small></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No activity logs found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($activities ?? [], 'links'))<div class="d-flex justify-content-end">{{ $activities->links() }}</div>@endif
    </div>
</div>
@endsection
@push('scripts')
<script>
    const filterInput = document.getElementById('tableSearch');
    const actionFilter = document.getElementById('actionFilter');
    function filterTable() {
        const search = (filterInput?.value || '').toLowerCase();
        const action = (actionFilter?.value || '').toLowerCase();
        document.querySelectorAll('tbody tr').forEach(tr => {
            const text = tr.textContent.toLowerCase();
            const matchesSearch = !search || text.includes(search);
            const matchesAction = !action || text.includes(action);
            tr.style.display = matchesSearch && matchesAction ? '' : 'none';
        });
    }
    filterInput?.addEventListener('keyup', filterTable);
    actionFilter?.addEventListener('change', filterTable);
</script>
@endpush

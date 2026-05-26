@extends('layouts.app')
@section('title', 'Tables')
@section('page-title', 'Tables')

@section('breadcrumb-plugins')
    <a href="{{ route('tables.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Add New
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
            <div class="input-group input-group-sm" style="max-width:300px;">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="tableSearch" placeholder="Search tables...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>Table #</th><th>Name</th><th>Capacity</th><th>Status</th><th>Location</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($tables ?? [] as $table)
                    <tr>
                        <td class="fw-medium">#{{ $table->table_number ?? $table->id }}</td>
                        <td>{{ $table->name }}</td>
                        <td>{{ $table->capacity }}</td>
                        <td>
                            <span class="badge bg-{{ $table->status === 'available' ? 'success' : ($table->status === 'occupied' ? 'danger' : ($table->status === 'reserved' ? 'info' : 'secondary')) }}">
                                {{ ucfirst($table->status) }}
                            </span>
                        </td>
                        <td>{{ $table->location ?? 'N/A' }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-info" onclick="showQr({{ $table->id }})" title="Show QR Code"><i class="fas fa-qrcode"></i></button>
                            <a href="{{ route('tables.edit', $table->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('tables.destroy', $table->id) }}')"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No tables found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($tables ?? [], 'links'))<div class="d-flex justify-content-end">{{ $tables->links() }}</div>@endif
    </div>
</div>
<form id="deleteForm" method="POST" class="d-none">@csrf @method('DELETE')</form>

<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Table QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="qrModalBody">
            </div>
            <div class="modal-footer justify-content-center">
                <a id="qrDownloadLink" class="btn btn-primary btn-sm" download="table-qr.svg">
                    <i class="fas fa-download me-1"></i> Download SVG
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.getElementById('tableSearch')?.addEventListener('keyup', function() {
        const v = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(tr => tr.style.display = tr.textContent.toLowerCase().includes(v) ? '' : 'none');
    });
    function showQr(id) {
        const modal = new bootstrap.Modal(document.getElementById('qrModal'));
        const body = document.getElementById('qrModalBody');
        body.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading QR code...</p></div>';
        modal.show();
        fetch('{{ route('tables.qr', '') }}/' + id)
            .then(r => r.text())
            .then(svg => {
                body.innerHTML = svg;
                document.getElementById('qrDownloadLink').href = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
            })
            .catch(() => {
                body.innerHTML = '<p class="text-danger">Failed to load QR code.</p>';
            });
    }
</script>
@endpush

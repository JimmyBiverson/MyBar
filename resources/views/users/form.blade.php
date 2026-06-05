<form method="POST" action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}">
    @csrf
    @isset($user) @method('PUT') @endisset
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name ?? '') }}" required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $user->email ?? '') }}" required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Role <span class="text-danger">*</span></label>
            <select class="form-select @error('role_id') is-invalid @enderror" name="role_id" required>
                <option value="">Select Role</option>
                @foreach($roles ?? \App\Models\Role::all() as $role)
                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id ?? '') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
            </select>
            @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Branch</label>
            <select class="form-select @error('branch_id') is-invalid @enderror" name="branch_id">
                <option value="">Select Branch</option>
                @foreach($branches ?? \App\Models\Branch::all() as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id ?? '') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
            @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">PIN Code</label>
            <input type="password" class="form-control @error('pin_code') is-invalid @enderror" name="pin_code" maxlength="4" placeholder="4-digit PIN">
            @error('pin_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $user->phone ?? '') }}">
            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        @if(!isset($user))
        <div class="col-md-6">
            <label class="form-label">Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control" name="password_confirmation" required>
        </div>
        @else
        <div class="col-md-6">
            <label class="form-label">New Password <small class="text-muted">(leave blank to keep current)</small></label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" name="password_confirmation">
        </div>
        @endif
        <div class="col-md-6">
            <div class="form-check mt-4">
                <input type="checkbox" class="form-check-input" name="is_active" value="1" id="isActive" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="isActive">Active</label>
            </div>
        </div>
        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ isset($user) ? 'Update' : 'Create' }} User</button>
        </div>
    </div>
</form>

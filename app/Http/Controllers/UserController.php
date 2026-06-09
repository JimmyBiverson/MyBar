<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with(['role', 'branch'])
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->when($request->role_id, fn ($q) => $q->where('role_id', $request->role_id))
            ->when(!auth()->user()->isAdmin(), fn ($q) => $q->where('branch_id', auth()->user()->branch_id))
            ->orderBy('name')
            ->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $branches = Branch::where('is_active', true)->get();
        return view('users.create', compact('roles', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
            'phone' => 'nullable|string|max:20',
            'pin_code' => 'nullable|string|digits:4',
            'is_active' => 'boolean',
            'status' => 'nullable|string|in:active,inactive,suspended',
        ]);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'branch_id' => $request->branch_id ?? auth()->user()->branch_id,
                'phone' => $request->phone,
                'pin_code' => $request->pin_code ? Hash::make($request->pin_code) : null,
                'is_active' => $request->boolean('is_active', true),
                'status' => $request->status ?? 'active',
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Created user: ' . $request->name,
                'description' => 'Created user with email: ' . $request->email,
                'properties' => ['ip' => $request->ip(), 'user_agent' => $request->userAgent()],
            ]);

            return redirect()->route('users.index')
                ->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create user.');
        }
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $branches = Branch::where('is_active', true)->get();
        return view('users.edit', compact('user', 'roles', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
            'phone' => 'nullable|string|max:20',
            'pin_code' => 'nullable|string|digits:4',
            'is_active' => 'boolean',
            'status' => 'nullable|string|in:active,inactive,suspended',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'role_id' => $request->role_id,
                'branch_id' => $request->branch_id,
                'phone' => $request->phone,
                'pin_code' => $request->pin_code ? Hash::make($request->pin_code) : null,
                'is_active' => $request->boolean('is_active', true),
                'status' => $request->status ?? $user->status,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Updated user: ' . $user->name,
                'description' => 'Updated user ID: ' . $user->id,
                'properties' => ['ip' => $request->ip(), 'user_agent' => $request->userAgent()],
            ]);

            return redirect()->route('users.index')
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to update user.');
        }
    }

    public function destroy(Request $request, User $user)
    {
        try {
            $userName = $user->name;
            $user->delete();

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Deleted user: ' . $userName,
                'description' => 'Deleted user ID: ' . $user->id,
                'properties' => ['ip' => $request->ip(), 'user_agent' => $request->userAgent()],
            ]);

            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cannot delete user with associated records.');
        }
    }
}

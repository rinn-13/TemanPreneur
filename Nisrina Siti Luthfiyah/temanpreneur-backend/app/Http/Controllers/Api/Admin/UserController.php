<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('business');

        if ($request->filled('role')) {
            $role = $request->role;
            $query->where(function ($q) use ($role) {
                $q->where('role', $role)
                    ->orWhereJsonContains('roles', $role);
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('class', 'like', "%{$q}%");
            });
        }

        $users = $query->latest()->get()->map(fn ($u) => $this->formatUser($u));
        return response()->json(['data' => $users]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:buyer,seller,seller_premium,admin',
            'class' => 'nullable|string|max:50',
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['roles'] = [$data['role']];
        $data['status'] = 'active';
        $user = User::create($data);
        return response()->json($this->formatUser($user->fresh()), 201);
    }

    public function show($id)
    {
        $user = User::with('business')->findOrFail($id);
        return response()->json($this->formatUser($user));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => 'sometimes|in:buyer,seller,seller_premium,admin',
            'class' => 'nullable|string|max:50',
        ]);
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        if (isset($data['role'])) {
            $data['roles'] = array_unique(array_merge($user->roles ?? [$user->role], [$data['role']]));
        }
        $user->update($data);
        return response()->json($this->formatUser($user->fresh()));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->role === 'admin') {
            return response()->json(['message' => 'Cannot delete admin user'], 403);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate(['status' => 'required|in:active,banned']);
        $user = User::findOrFail($id);
        if ($user->role === 'admin') {
            return response()->json(['message' => 'Cannot ban admin'], 403);
        }
        $user->update($data);
        return response()->json($this->formatUser($user->fresh()));
    }

    private function formatUser(User $u): array
    {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
            'roles' => $u->roles ?? [$u->role],
            'class' => $u->class ?? '',
            'status' => $u->status ?? 'active',
            'is_verified' => $u->is_verified ?? false,
            'created_at' => $u->created_at?->toIso8601String(),
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $roles = $this->roleOptions();
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'role' => trim((string) $request->query('role', '')),
            'status' => trim((string) $request->query('status', 'all')),
            'per_page' => (int) $request->query('per_page', 15),
        ];

        if (!in_array($filters['per_page'], [10, 15, 20, 50], true)) {
            $filters['per_page'] = 15;
        }

        $query = User::query()->orderByDesc('id');

        if ($filters['q'] !== '') {
            $term = '%' . $filters['q'] . '%';
            $query->where(function ($inner) use ($term): void {
                $inner->where('name', 'like', $term)->orWhere('email', 'like', $term);
            });
        }

        if (array_key_exists($filters['role'], $roles)) {
            $query->where('role', $filters['role']);
        }

        if ($filters['status'] === 'active') {
            $query->where('is_active', true);
        } elseif ($filters['status'] === 'inactive') {
            $query->where('is_active', false);
        }

        $users = $query->paginate($filters['per_page'])->withQueryString();

        return view('users.index', [
            'users' => $users,
            'filters' => $filters,
            'roles' => $roles,
            'activeAdmins' => User::query()->where('role', User::ROLE_ADMIN)->where('is_active', true)->count(),
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'roles' => $this->roleOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', Rule::in(array_keys($this->roleOptions()))],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = User::query()->create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => Hash::make($payload['password']),
            'role' => $payload['role'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log('users.created', [
            'description' => 'Usuario administrativo criado pelo painel.',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
            'new_values' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ],
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario criado com sucesso.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
            'roles' => $this->roleOptions(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'role' => ['required', Rule::in(array_keys($this->roleOptions()))],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $nextRole = $payload['role'];
        $nextIsActive = $request->boolean('is_active');
        $currentUserId = (int) Auth::id();

        if ($currentUserId === (int) $user->id && $nextRole !== User::ROLE_ADMIN) {
            return back()->withInput()->with('error', 'Voce nao pode remover seu proprio perfil de administrador.');
        }

        if ($currentUserId === (int) $user->id && $nextIsActive === false) {
            return back()->withInput()->with('error', 'Voce nao pode inativar seu proprio usuario.');
        }

        if ($this->wouldRemoveLastActiveAdmin($user, $nextRole, $nextIsActive, false)) {
            return back()->withInput()->with('error', 'Deve existir pelo menos um administrador ativo no sistema.');
        }

        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
        ];

        $user->name = $payload['name'];
        $user->email = $payload['email'];
        $user->role = $nextRole;
        $user->is_active = $nextIsActive;

        if (!empty($payload['password'])) {
            $user->password = Hash::make($payload['password']);
        }

        $user->save();

        AuditLogger::log('users.updated', [
            'description' => 'Usuario administrativo atualizado pelo painel.',
            'subject_type' => User::class,
            'subject_id' => (string) $user->id,
            'old_values' => $oldValues,
            'new_values' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ],
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario atualizado com sucesso.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $currentUserId = (int) Auth::id();
        if ($currentUserId === (int) $user->id) {
            return back()->with('error', 'Voce nao pode excluir seu proprio usuario.');
        }

        if ($this->wouldRemoveLastActiveAdmin($user, $user->role, (bool) $user->is_active, true)) {
            return back()->with('error', 'Deve existir pelo menos um administrador ativo no sistema.');
        }

        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
        ];

        $userId = $user->id;
        $user->delete();

        AuditLogger::log('users.deleted', [
            'description' => 'Usuario administrativo removido pelo painel.',
            'subject_type' => User::class,
            'subject_id' => (string) $userId,
            'old_values' => $oldValues,
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario removido com sucesso.');
    }

    private function roleOptions(): array
    {
        return [
            User::ROLE_ADMIN => 'Administrador',
            User::ROLE_OPERATOR => 'Operador',
            User::ROLE_FINANCIAL => 'Financeiro',
        ];
    }

    private function wouldRemoveLastActiveAdmin(User $user, string $nextRole, bool $nextIsActive, bool $isDelete): bool
    {
        $isCurrentActiveAdmin = $user->role === User::ROLE_ADMIN && (bool) $user->is_active;
        if (!$isCurrentActiveAdmin) {
            return false;
        }

        $willRemainActiveAdmin = !$isDelete && $nextRole === User::ROLE_ADMIN && $nextIsActive;
        if ($willRemainActiveAdmin) {
            return false;
        }

        return !User::query()
            ->where('role', User::ROLE_ADMIN)
            ->where('is_active', true)
            ->whereKeyNot($user->id)
            ->exists();
    }
}

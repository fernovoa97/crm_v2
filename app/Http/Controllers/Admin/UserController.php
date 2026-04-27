<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('supervisor')->orderBy('name')->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $supervisors = User::whereIn('role', ['jefe', 'supervisor'])->orderBy('name')->get();
        return view('admin.users.create', compact('supervisors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|min:6',
            'role'           => 'required|in:admin,jefe,supervisor,asesor,mesa_control',
            'supervisor_id'  => 'nullable|exists:users,id',
            'status'         => 'required|in:activo,inactivo',
            'contract_start' => 'nullable|date',
            'contract_end'   => 'nullable|date|after_or_equal:contract_start',
            'salary'         => 'nullable|numeric|min:0',
            'mobility_bonus' => 'nullable|numeric|min:0',
        ]);

        User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'role'           => $request->role,
            'supervisor_id'  => $request->supervisor_id,
            'status'         => $request->status,
            'contract_start' => $request->contract_start,
            'contract_end'   => $request->contract_end,
            'salary'         => $request->salary,
            'mobility_bonus' => $request->mobility_bonus,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $supervisors = User::whereIn('role', ['jefe', 'supervisor'])->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'supervisors'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'password'       => 'nullable|min:6',
            'role'           => 'required|in:admin,jefe,supervisor,asesor,mesa_control',
            'supervisor_id'  => 'nullable|exists:users,id',
            'status'         => 'required|in:activo,inactivo',
            'contract_start' => 'nullable|date',
            'contract_end'   => 'nullable|date|after_or_equal:contract_start',
            'salary'         => 'nullable|numeric|min:0',
            'mobility_bonus' => 'nullable|numeric|min:0',
        ]);

        $data = $request->except('password');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
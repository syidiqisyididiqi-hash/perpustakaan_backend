<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Data user berhasil diambil',
            'data' => User::latest()->get()
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,member',
            'member_number' => 'nullable|string|max:50|unique:users,member_number',
            'name' => 'required|string|max:100',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:6',
            'status' => 'nullable|in:active,inactive'
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'User berhasil ditambahkan',
            'data' => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json([
            'status' => true,
            'message' => 'Data user berhasil diambil',
            'data' => $user
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'sometimes|in:admin,member',
            'member_number' => 'nullable|string|max:50|unique:users,member_number,' . $user->id,
            'name' => 'sometimes|string|max:100',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'sometimes|email|max:100|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'status' => 'nullable|in:active,inactive'
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'User berhasil diperbarui',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User berhasil dihapus',
            'data' => $user
        ], 200);
    }
}

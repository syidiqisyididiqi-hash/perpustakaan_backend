<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json([
            'status' => true,
            'data' => $request->user()
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $user->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Profile berhasil diperbarui',
            'data' => $user
        ]);
    }
}
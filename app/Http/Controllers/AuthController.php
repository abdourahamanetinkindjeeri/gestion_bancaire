<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;
use App\Models\Client;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // if (Auth::attempt($request->only('email', 'password'))) {
        //     $user = Auth::user();
        //     $token = $user->createToken('API Token')->accessToken;

        //     return response()->json([
        //         'user' => $user,
        //         'token' => $token,
        //     ]);
        // }

        return response()->json(['error' => 'Identifiants invalides'], 401);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,client',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        if ($request->role === 'admin') {
            Admin::create([
                'nom' => $request->name,
                'prenom' => '',
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
        } elseif ($request->role === 'client') {
            Client::create([
                'code_client' => 'CL' . str_pad($user->id, 10, '0', STR_PAD_LEFT),
                'titulaire' => $request->name,
                'email' => $request->email,
            ]);
        }

        $token = $user->createToken('API Token')->accessToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'USER',
        ]);

        $token = auth('api')->login($user);

        return response()->json($this->respondWithToken($token), 201);
    }
    
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json($this->respondWithToken($token));
    }
    
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Logged out']);
    }

    public function refresh()
    {
        return response()->json($this->respondWithToken(auth('api')->refresh()));
    }

    private function respondWithToken(string $token): array
    {
        return [
            'accessToken' => $token,
            'tokenType' => 'bearer',
            'expiresIn' => auth('api')->factory()->getTTL() * 60,
        ];
    }
}

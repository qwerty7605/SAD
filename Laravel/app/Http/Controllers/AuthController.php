<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;

class AuthController extends Controller
{
    public function register(UserRequest $userRequest)
    {
        $validated = $userRequest->validated();

        $create = User::create($validated);

        return response()->json([
            'message' => 'register' ,
            'data' => $create       
        ]);
    }

    public function login()
    {
        return response()->json([
            'message' => 'login'        
        ]);
    }

    public function logout()
    {
        return response()->json([
            'message' => 'logout'        
        ]);
    }


}

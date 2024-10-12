<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class APIAuthenticatedSessionController extends Controller
{

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        if (Auth::attempt($request->all())) {
            $authUser = auth()->user();
            $authUser->tokens()->delete();
            $token =  $authUser->createToken($authUser->email)->plainTextToken;

            return response()->json(['token'=>$token,'user'=>$authUser], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        auth()->user()->tokens()->delete();
        return response()->json(['ok'], 200);
    }
}

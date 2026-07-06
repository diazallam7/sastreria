<?php

namespace App\Http\Controllers;

use App\Http\Requests\loginRequest;
use Illuminate\Support\Facades\Auth;

class loginController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('panel');
        }

        return view('auth.login');
    }

    public function login(loginRequest $request)
    {
        // Valida credenciales + aplica rate limiting (ver loginRequest).
        $request->authenticate();

        // Previene session fixation: nueva ID de sesión tras autenticar.
        $request->session()->regenerate();

        return redirect()->intended(route('panel'))
            ->with('success', '¡Bienvenido ' . Auth::user()->name . '!');
    }
}

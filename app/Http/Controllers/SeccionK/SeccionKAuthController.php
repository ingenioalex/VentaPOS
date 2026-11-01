<?php

namespace App\Http\Controllers\SeccionK;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeccionKAuthController extends Controller
{
    public function showLogin()
    {
        return view('seccionk.admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('seccionk')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('seccionk.admin.dashboard'));
        }

        return back()->withErrors([
            'usuario' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('usuario');
    }

    public function logout(Request $request)
    {
        Auth::guard('seccionk')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('seccionk.admin.login');
    }
}

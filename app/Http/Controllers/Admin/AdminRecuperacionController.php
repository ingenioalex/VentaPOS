<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Rules\ContrasenaSuperAdmin;
use App\Services\AdminRecuperacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminRecuperacionController extends Controller
{
    public function __construct(private AdminRecuperacionService $recuperacion) {}

    public function mostrarSolicitud()
    {
        return view('admin.auth.recuperar-solicitud');
    }

    public function solicitar(Request $request)
    {
        $datos = $request->validate([
            'usuario' => 'required|string|max:60',
        ]);

        $usuario = $this->recuperacion->buscarSuperAdmin($datos['usuario']);
        $challenge = bin2hex(random_bytes(16));

        if ($usuario && $this->recuperacion->tieneRecuperacionConfigurada($usuario)) {
            $request->session()->put('admin_recup_uid', $usuario->id);
            $request->session()->put('admin_recup_challenge', $challenge);
            $request->session()->forget('admin_recup_email_ok');

            return redirect()->route('admin.recuperar.pregunta');
        }

        $request->session()->forget(['admin_recup_uid', 'admin_recup_challenge', 'admin_recup_email_ok']);
        Log::info('Recuperación admin sin configuración o usuario inexistente', [
            'usuario' => $datos['usuario'],
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.recuperar.solicitud')
            ->withErrors(['usuario' => 'No hay recuperación configurada para ese usuario. Contacte a otro super administrador.']);
    }

    public function mostrarPregunta(Request $request)
    {
        if (! $request->session()->has('admin_recup_challenge')) {
            return redirect()->route('admin.recuperar.solicitud');
        }

        $uid = $request->session()->get('admin_recup_uid');
        $usuario = $this->recuperacion->usuarioEnRecuperacion($uid);

        return view('admin.auth.recuperar-pregunta', [
            'usuario' => $usuario,
            'emailEnmascarado' => $usuario
                ? $this->recuperacion->enmascararEmail((string) $usuario->email_recuperacion)
                : null,
            'correoVerificado' => (bool) $request->session()->get('admin_recup_email_ok'),
        ]);
    }

    public function verificarCorreo(Request $request)
    {
        if (! $request->session()->has('admin_recup_challenge')) {
            return redirect()->route('admin.recuperar.solicitud');
        }

        $datos = $request->validate([
            'email_recuperacion' => 'required|email|max:150',
        ]);

        $uid = $request->session()->get('admin_recup_uid');
        $usuario = $this->recuperacion->usuarioEnRecuperacion($uid);

        if (! $usuario || ! $this->recuperacion->emailCoincide($usuario, $datos['email_recuperacion'])) {
            Log::warning('Recuperación admin: correo incorrecto', [
                'usuario_id' => $usuario?->id,
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'email_recuperacion' => 'El correo no coincide con el registrado para este usuario.',
            ])->onlyInput('email_recuperacion');
        }

        $request->session()->put('admin_recup_email_ok', true);

        return redirect()->route('admin.recuperar.pregunta')
            ->with('ok', 'Correo verificado. Responda su pregunta secreta.');
    }

    public function verificarPregunta(Request $request)
    {
        if (! $request->session()->has('admin_recup_challenge')) {
            return redirect()->route('admin.recuperar.solicitud');
        }

        if (! $request->session()->get('admin_recup_email_ok')) {
            return redirect()->route('admin.recuperar.pregunta')
                ->withErrors(['email_recuperacion' => 'Primero verifique su correo de recuperación.']);
        }

        $datos = $request->validate([
            'respuesta_secreta' => 'required|string|max:255',
        ]);

        $uid = $request->session()->get('admin_recup_uid');
        $usuario = $this->recuperacion->usuarioEnRecuperacion($uid);

        if (! $usuario || ! $this->recuperacion->respuestaCorrecta($usuario, $datos['respuesta_secreta'])) {
            Log::warning('Recuperación admin: respuesta incorrecta', [
                'usuario_id' => $usuario?->id,
                'ip' => $request->ip(),
            ]);

            $request->session()->forget(['admin_recup_uid', 'admin_recup_challenge', 'admin_recup_email_ok']);

            return redirect()->route('admin.recuperar.solicitud')
                ->withErrors(['usuario' => 'No fue posible verificar su identidad. Revise usuario, correo y respuesta.']);
        }

        $request->session()->forget(['admin_recup_challenge', 'admin_recup_email_ok']);

        $tokenPlano = $this->recuperacion->crearTokenRestablecimiento($usuario);
        $request->session()->put('admin_recup_token', $tokenPlano);
        $request->session()->put('admin_recup_uid', $usuario->id);

        Log::info('Recuperación admin: enlace generado en pantalla', [
            'usuario_id' => $usuario->id,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.recuperar.enlace');
    }

    public function mostrarEnlace(Request $request)
    {
        $tokenPlano = $request->session()->get('admin_recup_token');
        $uid = $request->session()->get('admin_recup_uid');

        if (! $tokenPlano || ! $uid) {
            return redirect()->route('admin.recuperar.solicitud');
        }

        $usuario = $this->recuperacion->usuarioEnRecuperacion($uid);

        if (! $usuario || ! $this->recuperacion->tieneRecuperacionConfigurada($usuario)) {
            $request->session()->forget(['admin_recup_token', 'admin_recup_uid']);

            return redirect()->route('admin.recuperar.solicitud');
        }

        return view('admin.auth.recuperar-enlace', [
            'usuario' => $usuario,
            'emailEnmascarado' => $this->recuperacion->enmascararEmail((string) $usuario->email_recuperacion),
            'urlRestablecer' => $this->recuperacion->urlRestablecimiento($tokenPlano),
            'expiraEn' => now()->addHour(),
        ]);
    }

    public function mostrarRestablecer(string $token)
    {
        $registro = $this->recuperacion->buscarPorToken($token);

        if (! $registro) {
            return redirect()->route('admin.recuperar.solicitud')
                ->withErrors(['usuario' => 'El enlace no es válido o ya expiró. Solicite uno nuevo.']);
        }

        request()->session()->forget(['admin_recup_token', 'admin_recup_uid']);

        return view('admin.auth.recuperar-restablecer', [
            'token' => $token,
            'usuario' => $registro->usuario,
        ]);
    }

    public function restablecer(Request $request, string $token)
    {
        $registro = $this->recuperacion->buscarPorToken($token);

        if (! $registro) {
            return redirect()->route('admin.recuperar.solicitud')
                ->withErrors(['usuario' => 'El enlace no es válido o ya expiró. Solicite uno nuevo.']);
        }

        $datos = $request->validate([
            'password' => ['required', 'string', 'confirmed', new ContrasenaSuperAdmin],
        ]);

        $registro->usuario->update(['password' => $datos['password']]);
        $this->recuperacion->consumirToken($registro);

        Log::info('Recuperación admin: clave restablecida', [
            'usuario_id' => $registro->usuario_id,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.login')
            ->with('ok', 'Clave actualizada. Ya puede ingresar con su nueva contraseña.');
    }
}

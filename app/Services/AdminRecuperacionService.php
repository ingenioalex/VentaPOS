<?php

namespace App\Services;

use App\Models\AdminRecuperacionToken;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminRecuperacionService
{
    public function buscarSuperAdmin(string $usuario): ?Usuario
    {
        return Usuario::query()
            ->where('usuario', $usuario)
            ->where('rol', 'super_admin')
            ->whereNull('empresa_id')
            ->where('activo', true)
            ->first();
    }

    public function tieneRecuperacionConfigurada(Usuario $usuario): bool
    {
        return filled($usuario->email_recuperacion)
            && filled($usuario->pregunta_secreta)
            && filled($usuario->respuesta_secreta);
    }

    public function respuestaCorrecta(Usuario $usuario, string $respuesta): bool
    {
        return Hash::check($this->normalizarRespuesta($respuesta), (string) $usuario->respuesta_secreta);
    }

    public function normalizarRespuesta(string $respuesta): string
    {
        return mb_strtolower(trim($respuesta), 'UTF-8');
    }

    public function hashRespuesta(string $respuesta): string
    {
        return Hash::make($this->normalizarRespuesta($respuesta));
    }

    public function emailCoincide(Usuario $usuario, string $emailIngresado): bool
    {
        return mb_strtolower(trim($emailIngresado), 'UTF-8')
            === mb_strtolower(trim((string) $usuario->email_recuperacion), 'UTF-8');
    }

    public function usuarioEnRecuperacion(int|string|null $uid): ?Usuario
    {
        if (! $uid) {
            return null;
        }

        return Usuario::query()
            ->where('id', $uid)
            ->where('rol', 'super_admin')
            ->whereNull('empresa_id')
            ->where('activo', true)
            ->first();
    }

    public function enmascararEmail(string $email): string
    {
        $email = trim($email);
        if (! str_contains($email, '@')) {
            return '***';
        }

        [$local, $dominio] = explode('@', $email, 2);
        $prefijo = mb_substr($local, 0, min(3, mb_strlen($local)));
        $partes = explode('.', $dominio);
        $tld = count($partes) > 1 ? '.'.end($partes) : '';

        return $prefijo.str_repeat('*', 8).$tld;
    }

    /** Genera token de un solo uso y devuelve el valor en texto plano para el enlace. */
    public function crearTokenRestablecimiento(Usuario $usuario): string
    {
        AdminRecuperacionToken::query()
            ->where('usuario_id', $usuario->id)
            ->delete();

        $plano = Str::random(64);

        AdminRecuperacionToken::create([
            'usuario_id' => $usuario->id,
            'token' => hash('sha256', $plano),
            'expira_en' => now()->addHour(),
        ]);

        return $plano;
    }

    public function urlRestablecimiento(string $tokenPlano): string
    {
        return route('admin.recuperar.restablecer', ['token' => $tokenPlano]);
    }

    public function buscarPorToken(string $tokenPlano): ?AdminRecuperacionToken
    {
        $registro = AdminRecuperacionToken::query()
            ->where('token', hash('sha256', $tokenPlano))
            ->with('usuario')
            ->first();

        if (! $registro || ! $registro->vigente()) {
            return null;
        }

        return $registro;
    }

    public function consumirToken(AdminRecuperacionToken $token): void
    {
        $token->delete();
    }
}

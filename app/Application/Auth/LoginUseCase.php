<?php

namespace App\Application\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginUseCase
{
    /**
     * Autentica a un usuario y genera un token de acceso.
     *
     * @param string $username
     * @param string $password
     * @return string Token de texto plano
     * @throws ValidationException
     */
    public function execute(string $username, string $password): string
    {
        $user = User::where('username', $username)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Se elimina cualquier token anterior por seguridad (opcional, pero buena práctica)
        $user->tokens()->delete();

        return $user->createToken('pharmacovigilance-auth-token')->plainTextToken;
    }
}

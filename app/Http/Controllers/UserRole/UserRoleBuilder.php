<?php

namespace App\Http\Controllers\UserRole;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Resources\Exception\RegularException;
use function env;

class UserRoleBuilder
{

    const GUEST = 'guest';
    const ADMIN = 'admin';
    const PERSONNEL = 'personnel';

    public static function generateJwtToken(string $userRole, string $userId): string
    {
        $payload = [
            'iss' => env('JWT_SERVER'),
//            'jti' => env('JWT_TOKEN_ID'),
            'iat' => time(),
            'exp' => time() + env('JWT_TIMEOUT'),
            'nbf' => time() + env('JWT_ACTIVE_AFTER'),
            'data' => [
                'userRole' => $userRole,
                'userId' => $userId,
            ],
        ];
        $key = env('JWT_KEY');
        return JWT::encode($payload, $key, 'HS256');
    }

    public static function generateUserRole(Request $request)
    {
        $token = $request->bearerToken();
        if (!empty($token)) {
            try {
                $key = env('JWT_KEY');
                $credential = JWT::decode($token, new Key($key, 'HS256'));
                return match ($credential->data->userRole) {
                    static::ADMIN => new AdminRole($credential->data->userId),
                    static::PERSONNEL => new PersonnelRole($credential->data->userId),
                    default => throw RegularException::unauthorized('unrecognized role'),
                };
            } catch (ExpiredException $ex) {
                throw RegularException::unauthorized('token expired');
            }
        } else {
//            return new GuestRole();
            return null;
        }
    }
}

<?php

namespace Shared\Application;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Resources\Exception\RegularException;
use function env;

class JwtHelper
{

    public static function generateJwtToken(UserRole $userRole, string $userId): string
    {
        $payload = [
            'iss' => env('JWT_SERVER'),
//            'jti' => env('JWT_TOKEN_ID'),
            'iat' => time(),
            'exp' => time() + env('JWT_TIMEOUT'),
            'nbf' => time() + env('JWT_ACTIVE_AFTER'),
            'data' => [
                'userRole' => $userRole->value,
                'userId' => $userId,
            ],
        ];
        $key = env('JWT_KEY');
        return JWT::encode($payload, $key, 'HS256');
    }

    /**
     * 
     * @param type $param
     * @return array: [ 'userRole' => string, 'userId' => string]
     */
    public static function decodeJwtToken(Request $request): ?array
    {
        $token = $request->bearerToken();
        if (!empty($token)) {
            try {
                $key = env('JWT_KEY');
                $credential = JWT::decode($token, new Key($key, 'HS256'));
                return [
                    'userRole' => $credential->data->userRole,
                    'userId' => $credential->data->userId,
                ];
            } catch (ExpiredException $ex) {
                throw RegularException::unauthorized('token expired');
            }
        } else {
            throw RegularException::unauthorized('token not provided');
        }
    }
}

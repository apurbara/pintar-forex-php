<?php

namespace Tests\Http\Record;

use Firebase\JWT\JWT;
use function env;

class JwtHeaderTokenGenerator
{
    public static function generate(array $data)
    {
        $payload = [
            'iss' => env('JWT_SERVER'),
//            'jti' => env('JWT_TOKEN_ID'),
            'iat' => time(),
            'data' => $data
        ];
        $key = env('JWT_KEY');

        $token = JWT::encode($payload, $key, 'HS256');
        return ["HTTP_Authorization" => 'Bearer ' . $token];
    }
}

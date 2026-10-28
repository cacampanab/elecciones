<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function generateJWT($user)
{
    $key = getenv('JWT_SECRET_KEY') ?: 'elecciones2026_secure_jwt_secret_key_ec';
    $expiration = (int)(getenv('JWT_EXPIRATION') ?: 3600);

    $payload = [
        'iat' => time(),
        'exp' => time() + $expiration,
        'sub' => $user->id,
        'username' => $user->username,
        'email' => $user->email,
        'role' => $user->role_name ?? '',
        'role_id' => $user->role_id,
    ];

    return JWT::encode($payload, $key, 'HS256');
}

function generateRefreshToken()
{
    return bin2hex(random_bytes(64));
}

function decodeJWT($token)
{
    try {
        $key = getenv('JWT_SECRET_KEY') ?: 'elecciones2026_secure_jwt_secret_key_ec';
        return JWT::decode($token, new Key($key, 'HS256'));
    } catch (\Exception $e) {
        return null;
    }
}

<?php

namespace App\Helpers;

use DomainException;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use UnexpectedValueException;

class JwtAuth
{
    public $key;

    public function __construct()
    {
        $this->key = 'KEY_SECRET_300999';
    }

    public function signup($email, $password, $getToken = null)
    {
        // Find if the user exists in the database
        $user = User::where([
            'email' => $email,
            'password' => $password
        ])->first();

        // Check if your credentials are correct (object)
        $signup = false;
        if (is_object($user)) {
            $signup = true;
        }

        // Generate token with user data
        if ($signup) {
            $token = [
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60) // 1 week
            ];

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

            if (!is_null($getToken)) {

                $data = $decoded;

            } else {

                $data = $jwt;

            }

        } else {
            $data = [
                'status' => 'error',
                'message' => 'Invalid login'
            ];
        }

        // Return user data or token
        return $data;
    }

    public function checkToken($jwt, $getIdentity = false)
    {
        $auth = false;

        try {
            $jwt = str_replace('"', '', $jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (UnexpectedValueException | DomainException $exception) {
            $auth = false;
        }

        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($getIdentity) {
            return $decoded;
        }

        return $auth;
    }
}

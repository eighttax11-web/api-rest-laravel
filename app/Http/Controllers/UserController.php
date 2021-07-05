<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\UserRepository;

class UserController extends Controller
{
    protected $user;

    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $json = $request->input('json', null);

        return $this->user->register($json);
    }

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $json = $request->input('json', null);
        return $this->user->login($json);
    }

    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        $token = $request->header('Authorization');
        $json = $request->input('json', null);
        return $this->user->update($token, $json);
    }

    public function upload(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->user->upload();
    }
}

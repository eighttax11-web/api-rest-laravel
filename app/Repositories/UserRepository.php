<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Validator;

class UserRepository
{
    public function register($json): \Illuminate\Http\JsonResponse
    {
        $params = json_decode($json); //Object
        $params_array = json_decode($json, true); //Array

        if (!empty($params) && !empty($params_array)) {

            $params_array = array_map('trim', $params_array); //Clean spaces

            $validate = Validator::make($params_array, [
                'name' => ['required', 'alpha'],
                'surname' => ['required', 'alpha'],
                'email' => ['required', 'email', 'unique:users'],
                'password' => ['required']
            ]);

            if (!$validate->fails()) {

                $pwd = password_hash($params->password, PASSWORD_BCRYPT, ['cost' => 4]);

                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->role = 'ROLE_USER';
                $user->email = $params_array['email'];
                $user->password = $pwd;

                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'The user has been successfully created',
                    'user' => $user
                );

            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'The user has not been created',
                    'errors' => $validate->errors()
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'The user has not been created'
            );
        }

        return response()->json($data, $data['code']);
    }
}

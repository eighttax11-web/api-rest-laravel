<?php

namespace App\Repositories;

use App\Helpers\JwtAuth;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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

                $pwd = hash('sha256', $params->password);

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

    public function login($json): \Illuminate\Http\JsonResponse
    {
        $jwtAuth = new JwtAuth();

        $params = json_decode($json); //Object
        $params_array = json_decode($json, true); //Array

        if (!empty($params) && !empty($params_array)) {

            $params_array = array_map('trim', $params_array); //Clean spaces

            $validate = Validator::make($params_array, [
                'email' => ['required', 'email'],
                'password' => ['required']
            ]);

            if (!$validate->fails()) {

                $pwd = hash('sha256', $params->password);
                $data = $jwtAuth->signup($params->email, $pwd);

                if (!empty($params->getToken)) {
                    $data = $jwtAuth->signup($params->email, $pwd, true);
                }

            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Invalid login',
                    'errors' => $validate->errors()
                );
            }

        } else {
            $data = [
                'status' => 'error',
                'message' => 'Invalid login'
            ];
        }

        return response()->json($data);
    }

    public function update($token, $json)
    {
        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if ($checkToken) {
            $params = json_decode($json); //Object
            $params_array = json_decode($json, true); //Array

            if (!empty($params) && !empty($params_array)) {

                $params_array = array_map('trim', $params_array); //Clean spaces

                $user = $jwtAuth->checkToken($token, true);

                $validate = Validator::make($params_array, [
                    'name' => ['required', 'alpha'],
                    'surname' => ['required', 'alpha'],
                    'email' => ['required', 'email', 'unique:users,email,' . $user->sub]
                ]);

                if (!$validate->fails()) {

                    unset($params_array['id']);
                    unset($params_array['role']);
                    unset($params_array['password']);
                    unset($params_array['created_at']);
                    unset($params_array['remember_token']);

                    $user_update = User::where('id', $user->sub)->update($params_array);

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'The user has been successfully updated',
                        'user' => $params_array
                    );

                } else {
                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'The user has not been updated',
                        'errors' => $validate->errors()
                    ];
                }

            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'The user has not been updated'
                );
            }

        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Invalid login'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function upload($request): \Illuminate\Http\JsonResponse
    {
        $image = $request->file('file0');

        if ($image) {

            $validate = Validator::make($request->all(), [
                'file0' => ['required', 'image', 'mimes:jpg,jpeg,png,gif']
            ]);

            if (!$validate->fails()) {

                $image_name = time() . "_" . $image->getClientOriginalName();
                Storage::disk('users')->put($image_name, File::get($image));

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'image' => $image_name
                );

            } else {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Error loading image',
                    'errors' => $validate->errors()
                );
            }

        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Error loading image'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename)
    {
        try {
            $file = Storage::disk('users')->get($filename);
            return new Response($file, 200);
        } catch (FileNotFoundException $e) {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Image not found'
            );

            return response()->json($data, $data['code']);
        }
    }

    public function detail($id): \Illuminate\Http\JsonResponse
    {
        $user = User::find($id);

        if (is_object($user)) {
            $data = array(
                'status' => 'success',
                'code' => 200,
                'user' => $user
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'User not found'
            );
        }

        return response()->json($data, $data['code']);
    }
}

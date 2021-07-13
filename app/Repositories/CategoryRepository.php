<?php


namespace App\Repositories;

use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class CategoryRepository
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $categories = Category::all();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'categories' => $categories
        );

        return response()->json($data, $data['code']);
    }

    public function store($json): \Illuminate\Http\JsonResponse
    {
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {

            $params_array = array_map('trim', $params_array);

            $validate = Validator::make($params_array, [
                'name' => ['required']
            ]);

            if (!$validate->fails()) {

                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'The category has been successfully created',
                    'category' => $category
                ];

            } else {
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'The category has not been created',
                    'errors' => $validate->errors()
                ];
            }

        } else {

            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'The category has not been created'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function show($id): \Illuminate\Http\JsonResponse
    {
        $category = Category::find($id);

        if (is_object($category)) {
            $data = array(
                'status' => 'success',
                'code' => 200,
                'category' => $category
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Category not found'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function update($json, $id): \Illuminate\Http\JsonResponse
    {
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {

            $params_array = array_map('trim', $params_array);

            $validate = Validator::make($params_array, [
                'name' => ['required']
            ]);

            if (!$validate->fails()) {

                unset($params_array['id']);
                unset($params_array['created_at']);

                $category = Category::find($id);

                if (is_object($category)) {

                    $category->update($params_array);

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'The category has been successfully updated',
                        'category' => $params_array
                    ];

                } else {
                    $data = [
                        'status' => 'error',
                        'code' => 404,
                        'message' => 'Category not found'
                    ];
                }

            } else {
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'The category has not been updated',
                    'errors' => $validate->errors()
                ];
            }

        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'The category has not been updated'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function destroy()
    {

    }
}

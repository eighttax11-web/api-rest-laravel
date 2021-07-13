<?php


namespace App\Repositories;

use App\Models\Post;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Response;

class PostRepository
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $posts = Post::all()->load('category', 'user');

        $data = [
            'status' => 'success',
            'code' => 200,
            'posts' => $posts
        ];

        return response()->json($data, $data['code']);
    }

    public function store($json, $request): \Illuminate\Http\JsonResponse
    {
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params) && !empty($params_array)) {

            $params_array = array_map('trim', $params_array);

            $user = $this->getIdentity($request);

            $validate = Validator::make($params_array, [
                'title' => ['required'],
                'content' => ['required'],
                'category_id' => ['required'],
                'image' => ['required']
            ]);

            if (!$validate->fails()) {

                try {
                    $post = new Post();
                    $post->user_id = $user->sub;
                    $post->category_id = $params->category_id;
                    $post->title = $params->title;
                    $post->content = $params->content;
                    $post->image = $params->image;
                    $post->save();

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'The post has been successfully created',
                        'post' => $post
                    ];
                } catch (\Exception $exception) {
                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'The post has not been created'
                    ];
                }

            } else {
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'The post has not been created',
                    'errors' => $validate->errors()
                ];
            }
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'The post has not been created'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function show($id): \Illuminate\Http\JsonResponse
    {
        $post = Post::find($id)->load('category', 'user');

        if (is_object($post)) {

            $data = [
                'status' => 'success',
                'code' => 200,
                'category' => $post
            ];

        } else {

            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Post not found'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function update($request, $json, $id): \Illuminate\Http\JsonResponse
    {
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params) && !empty($params_array)) {

            $validate = Validator::make($params_array, [
                'title' => ['required'],
                'content' => ['required'],
                'category_id' => ['required']
            ]);

            if (!$validate->fails()) {

                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['created_at']);

                $user = $this->getIdentity($request);
                $post = Post::where('id', $id)->where('user_id', $user->sub)->first();

                if (is_object($post)) {

                    $post->update($params_array);

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'The post has been successfully updated',
                        'post' => $params_array
                    ];

                } else {

                    $data = [
                        'status' => 'error',
                        'code' => 404,
                        'message' => 'Post not found'
                    ];
                }

            } else {
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'The post has not been updated',
                    'errors' => $validate->errors()
                ];
            }
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'The post has not been updated'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function destroy($request, $id): \Illuminate\Http\JsonResponse
    {
        $user = $this->getIdentity($request);

        $post = Post::where('id', $id)->where('user_id', $user->sub)->first();

        if (is_object($post)) {

            $post->delete();

            $data = [
                'status' => 'success',
                'code' => 204,
                'post' => $post
            ];

        } else {

            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Post not found'
            ];
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
                Storage::disk('posts')->put($image_name, File::get($image));

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'image' => $image_name
                ];

            } else {
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Error loading image',
                    'errors' => $validate->errors()
                ];
            }

        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Error loading image'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename)
    {
        try {
            $file = Storage::disk('posts')->get($filename);
            return new Response($file, 200);
        } catch (FileNotFoundException $e) {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Image not found'
            ];
            return response()->json($data, $data['code']);
        }
    }

    public function getPostsByCategory($id): \Illuminate\Http\JsonResponse
    {
        $posts = Post::where('category_id', $id)->get();

        $data = [
            'status' => 'success',
            'code' => 200,
            'posts' => $posts
        ];

        return response()->json($data, $data['code']);
    }

    public function getPostsByUser($id): \Illuminate\Http\JsonResponse
    {
        $posts = Post::where('user_id', $id)->get();

        $data = [
            'status' => 'success',
            'code' => 200,
            'posts' => $posts
        ];

        return response()->json($data, $data['code']);
    }

    private function getIdentity($request)
    {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        return $jwtAuth->checkToken($token, true);
    }
}

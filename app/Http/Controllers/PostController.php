<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\PostRepository;

class PostController extends Controller
{
    protected $post;

    public function __construct(PostRepository $post)
    {
        $this->post = $post;
        $this->middleware('api.auth', ['except' => ['index', 'show', 'getImage', 'getPostsByCategory', 'getPostsByUser']]);
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        return $this->post->index();
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $json = $request->input('json', null);
        return $this->post->store($json, $request);
    }

    public function show($id): \Illuminate\Http\JsonResponse
    {
        return $this->post->show($id);
    }

    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $json = $request->input('json', null);
        return $this->post->update($request, $json, $id);
    }

    public function destroy(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        return $this->post->destroy($request, $id);
    }

    public function upload(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->post->upload($request);
    }

    public function getImage($filename)
    {
        return $this->post->getImage($filename);
    }

    public function getPostsByCategory($id): \Illuminate\Http\JsonResponse
    {
        return $this->post->getPostsByCategory($id);
    }

    public function getPostsByUser($id): \Illuminate\Http\JsonResponse
    {
        return $this->post->getPostsByUser($id);
    }
}

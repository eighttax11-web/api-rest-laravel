<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\CategoryRepository;

class CategoryController extends Controller
{
    protected $category;

    public function __construct(CategoryRepository $category)
    {
        $this->category = $category;
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index(): \Illuminate\Http\JsonResponse
    {
        return $this->category->index();
    }

    public function show($id): \Illuminate\Http\JsonResponse
    {
        return $this->category->show($id);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $json = $request->input('json', null);
        return $this->category->store($json);
    }

    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $json = $request->input('json', null);
        return $this->category->update($json, $id);
    }
}

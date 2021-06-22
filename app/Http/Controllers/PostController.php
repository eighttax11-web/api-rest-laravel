<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Category;

class PostController extends Controller
{
    public function index(): string
    {
        $categories = Category::all();
        foreach ($categories as $category) {
            echo "<h1>$category->name</h1>";
            foreach ($category->posts as $post) {
                echo "<h3>$post->title</h3>";
                echo "<span style='color: gray'>{$post->user->name} - {$post->category->name}</span>";
                echo "<p>$post->content</p>";
            }
            echo "<hr>";
        }
        die();
    }
}

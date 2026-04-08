<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    /**
     * Display a listing of published blogs.
     */
    public function index(Request $request)
    {
        $limit = $request->query('limit');
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 10);
        $category = $request->query('category');

        $query = Blog::published()->orderBy('published_at', 'desc');

        if ($category) {
            $query->where('category', $category);
        }

        // If only limit is provided (for getLatestBlogs), return simple array
        if ($limit && !$request->query('page') && !$request->query('per_page')) {
            $blogs = $query->limit($limit)->get();
            
            return response()->json(
                $blogs->map(function ($blog) {
                    return [
                        'id' => $blog->id,
                        'title' => $blog->title,
                        'slug' => $blog->slug,
                        'excerpt' => $blog->excerpt,
                        'thumbnail' => $blog->thumbnail ? Storage::url($blog->thumbnail) : null,
                        'category' => $blog->category,
                        'author' => $blog->author,
                        'created_at' => $blog->created_at->toISOString(),
                    ];
                })
            );
        }

        // Otherwise return paginated response (for getBlogs)
        $blogs = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $blogs->getCollection()->map(function ($blog) {
                return [
                    'id' => $blog->id,
                    'title' => $blog->title,
                    'slug' => $blog->slug,
                    'excerpt' => $blog->excerpt,
                    'thumbnail' => $blog->thumbnail ? Storage::url($blog->thumbnail) : null,
                    'category' => $blog->category,
                    'author' => $blog->author,
                    'created_at' => $blog->created_at->toISOString(),
                ];
            }),
            'current_page' => $blogs->currentPage(),
            'last_page' => $blogs->lastPage(),
            'per_page' => $blogs->perPage(),
            'total' => $blogs->total(),
        ]);
    }

    /**
     * Display a single blog post by slug.
     */
    public function show($slug)
    {
        $blog = Blog::published()->where('slug', $slug)->first();

        if (!$blog) {
            return response()->json([
                'error' => 'Blog post not found'
            ], 404);
        }

        return response()->json([
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'excerpt' => $blog->excerpt,
            'content' => $blog->content,
            'thumbnail' => $blog->thumbnail ? Storage::url($blog->thumbnail) : null,
            'category' => $blog->category,
            'author' => $blog->author,
            'created_at' => $blog->created_at->toISOString(),
        ]);
    }
}

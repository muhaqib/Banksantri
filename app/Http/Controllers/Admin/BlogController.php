<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $blogs = Blog::orderBy('created_at', 'desc')
            ->paginate(15);

        return view('pages.admin.blog.index', [
            'blogs' => $blogs,
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.admin.blog.create', [
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs,slug',
            'excerpt' => 'required|string',
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|max:2048',
            'category' => 'nullable|string|max:100',
            'author' => 'nullable|string|max:100',
            'is_published' => 'nullable|boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Handle thumbnail upload
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('blogs/thumbnails', 'public');
        }

        Blog::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'thumbnail' => $thumbnailPath,
            'category' => $validated['category'] ?? null,
            'author' => $validated['author'] ?? null,
            'is_published' => $validated['is_published'] ?? false,
        ]);

        return redirect()->route('admin.blog.index')
            ->with('success', 'Blog post berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        return view('pages.admin.blog.show', [
            'blog' => $blog,
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog)
    {
        return view('pages.admin.blog.edit', [
            'blog' => $blog,
            'activeRole' => 'admin',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Blog $blog)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs,slug,' . $blog->id,
            'excerpt' => 'required|string',
            'content' => 'required|string',
            'thumbnail' => 'nullable|image|max:2048',
            'category' => 'nullable|string|max:100',
            'author' => 'nullable|string|max:100',
            'is_published' => 'nullable|boolean',
        ]);

        // Update slug if title changed and slug not manually set
        if ($request->isMethod('put') && $blog->title !== $validated['title'] && $blog->slug === Str::slug($blog->title)) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $data = [
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? $blog->slug,
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'category' => $validated['category'] ?? $blog->category,
            'author' => $validated['author'] ?? $blog->author,
            'is_published' => $validated['is_published'] ?? $blog->is_published,
        ];

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($blog->thumbnail) {
                Storage::disk('public')->delete($blog->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('blogs/thumbnails', 'public');
        }

        $blog->update($data);

        return redirect()->route('admin.blog.index')
            ->with('success', 'Blog post berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        // Delete thumbnail if exists
        if ($blog->thumbnail) {
            Storage::disk('public')->delete($blog->thumbnail);
        }

        $blog->delete();

        return redirect()->route('admin.blog.index')
            ->with('success', 'Blog post berhasil dihapus!');
    }

    /**
     * Toggle publish status.
     */
    public function togglePublish(Blog $blog)
    {
        $blog->update([
            'is_published' => !$blog->is_published,
        ]);

        $status = $blog->is_published ? 'dipublikasikan' : 'diubah menjadi draft';
        return redirect()->route('admin.blog.index')
            ->with('success', "Blog post berhasil {$status}!");
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            'totalBlogs' => Blog::count(),
            'publishedBlogs' => Blog::published()->count(),
            'draftBlogs' => Blog::draft()->count(),
            ...$this->viewContext(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.admin.blog.create', [
            ...$this->viewContext(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'category' => ['nullable', 'string', 'max:100'],
            'author' => ['nullable', 'string', 'max:100'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? $validated['title']);
        $validated['content'] = $this->normalizeContent($validated['content']);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $validated['is_published'] ? now() : null;

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $this->storeCompressedThumbnail($request->file('thumbnail'));
        }

        Blog::create($validated);

        return redirect()->route($this->routeName('index'))
            ->with('success', 'Blog post berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        if (request()->routeIs('petugas.blog.read')) {
            abort_unless($blog->is_published, 404);
        }

        return view('pages.admin.blog.show', [
            'blog' => $blog,
            'canManageBlog' => $this->canManageBlog(),
            'backRoute' => request()->routeIs('petugas.blog.read')
                ? route('petugas.dashboard')
                : route($this->routeName('index')),
            ...$this->viewContext(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog)
    {
        return view('pages.admin.blog.edit', [
            'blog' => $blog,
            ...$this->viewContext(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Blog $blog)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'category' => ['nullable', 'string', 'max:100'],
            'author' => ['nullable', 'string', 'max:100'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? $validated['title'], $blog->id);
        $validated['content'] = $this->normalizeContent($validated['content']);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['published_at'] = $validated['is_published']
            ? ($blog->published_at ?? now())
            : null;

        if ($request->hasFile('thumbnail')) {
            if ($blog->thumbnail && ! Str::startsWith($blog->thumbnail, ['http://', 'https://', '/storage/', 'storage/'])) {
                Storage::disk('public')->delete($blog->thumbnail);
            }
            $validated['thumbnail'] = $this->storeCompressedThumbnail($request->file('thumbnail'));
        }

        $blog->update($validated);

        return redirect()->route($this->routeName('index'))
            ->with('success', 'Blog post berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        // Delete thumbnail if exists
        if ($blog->thumbnail && ! Str::startsWith($blog->thumbnail, ['http://', 'https://', '/storage/', 'storage/'])) {
            Storage::disk('public')->delete($blog->thumbnail);
        }

        $blog->delete();

        return redirect()->route($this->routeName('index'))
            ->with('success', 'Blog post berhasil dihapus!');
    }

    /**
     * Toggle publish status.
     */
    public function togglePublish(Blog $blog)
    {
        $blog->update([
            'is_published' => ! $blog->is_published,
            'published_at' => $blog->is_published ? null : ($blog->published_at ?? now()),
        ]);

        $status = $blog->is_published ? 'dipublikasikan' : 'diubah menjadi draft';
        return redirect()->route($this->routeName('index'))
            ->with('success', "Blog post berhasil {$status}!");
    }

    private function viewContext(): array
    {
        $role = request()->routeIs('petugas.*') ? 'petugas' : 'admin';

        return [
            'activeRole' => $role,
            'routePrefix' => "{$role}.blog",
        ];
    }

    private function routeName(string $name): string
    {
        return $this->viewContext()['routePrefix'].".{$name}";
    }

    private function canManageBlog(): bool
    {
        if (request()->routeIs('petugas.blog.read')) {
            return false;
        }

        $permission = request()->routeIs('petugas.*')
            ? 'petugas.blog.manage'
            : 'admin.blog.manage';

        return request()->user()?->can($permission) ?? false;
    }

    private function normalizeContent(string $content): string
    {
        $content = trim($content);
        $decoded = json_decode($content, true);

        if (
            is_array($decoded)
            && ($decoded['type'] ?? null) === 'blocks'
            && is_array($decoded['blocks'] ?? null)
        ) {
            $blocks = collect($decoded['blocks'])
                ->map(fn ($block) => [
                    'type' => in_array($block['type'] ?? 'p', ['h2', 'h3', 'p', 'quote'], true) ? $block['type'] : 'p',
                    'text' => trim((string) ($block['text'] ?? '')),
                ])
                ->filter(fn ($block) => $block['text'] !== '')
                ->values()
                ->all();

            if (empty($blocks)) {
                throw ValidationException::withMessages([
                    'content' => 'Konten lengkap wajib diisi.',
                ]);
            }

            return json_encode([
                'type' => 'blocks',
                'blocks' => $blocks,
            ], JSON_UNESCAPED_UNICODE);
        }

        if ($content === '') {
            throw ValidationException::withMessages([
                'content' => 'Konten lengkap wajib diisi.',
            ]);
        }

        return $content;
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::random(8);
        $slug = $base;
        $counter = 2;

        while (Blog::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function storeCompressedThumbnail(UploadedFile $file): string
    {
        if (! extension_loaded('gd') || ! function_exists('imagewebp')) {
            return $file->store('blogs/thumbnails', 'public');
        }

        $sourcePath = $file->getRealPath();
        $imageInfo = @getimagesize($sourcePath);

        if (! $imageInfo) {
            return $file->store('blogs/thumbnails', 'public');
        }

        [$width, $height] = $imageInfo;
        $source = match ($imageInfo['mime'] ?? null) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : null,
            default => null,
        };

        if (! $source) {
            return $file->store('blogs/thumbnails', 'public');
        }

        if (($imageInfo['mime'] ?? null) === 'image/jpeg' && function_exists('exif_read_data')) {
            $source = $this->orientJpeg($source, $sourcePath);
            $width = imagesx($source);
            $height = imagesy($source);
        }

        $maxWidth = 1600;
        $maxHeight = 900;
        $ratio = min($maxWidth / $width, $maxHeight / $height, 1);
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));

        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $path = 'blogs/thumbnails/'.Str::uuid().'.webp';
        $fullPath = Storage::disk('public')->path($path);

        if (! is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $stored = imagewebp($target, $fullPath, 78);
        imagedestroy($source);
        imagedestroy($target);

        return $stored ? $path : $file->store('blogs/thumbnails', 'public');
    }

    private function orientJpeg(\GdImage $image, string $path): \GdImage
    {
        $exif = @exif_read_data($path);
        $orientation = $exif['Orientation'] ?? null;

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        return $rotated ?: $image;
    }
}

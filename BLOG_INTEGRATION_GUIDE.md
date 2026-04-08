# Blog Integration Guide - Laravel Backend + Astro Frontend

This document explains how the Laravel backend (`tabungan`) integrates with the Astro website (`website`) for blog management.

## Overview

The blog system allows administrators to create, edit, and manage blog posts through a Laravel admin panel. The published blog posts are then consumed by the Astro website via RESTful API endpoints.

## Architecture

```
┌─────────────────────┐                    ┌─────────────────────┐
│   Laravel Backend   │                    │   Astro Website     │
│   (Admin Panel)     │                    │   (Frontend)        │
│                     │                    │                     │
│  - Blog CRUD        │────── API ────────▶│  - Blog Listing     │
│  - Image Upload     │   (/api/blog)      │  - Blog Detail      │
│  - Publish/Draft    │                    │  - Search/Filter    │
└─────────────────────┘                    └─────────────────────┘
```

## Database Schema

### Blogs Table

Located in: `database/migrations/2026_04_08_112611_create_blogs_table.php`

| Column       | Type      | Description                           |
|-------------|-----------|---------------------------------------|
| id          | bigint    | Primary key                           |
| title       | string    | Blog post title                       |
| slug        | string    | URL-friendly slug (unique)            |
| excerpt     | text      | Short summary for listings            |
| content     | longText  | Full blog content (HTML allowed)      |
| thumbnail   | string    | Path to featured image (nullable)     |
| category    | string    | Category name (nullable)              |
| author      | string    | Author name (nullable)                |
| is_published| boolean   | Publication status                    |
| published_at| timestamp | When the post was published           |
| created_at  | timestamp | Record creation time                  |
| updated_at  | timestamp | Last update time                      |

## Admin Panel Features

### Access

- **URL**: `/admin/blog`
- **Role**: Admin only
- **Navigation**: Sidebar → Blog & Artikel

### Features

1. **List All Blogs** (`/admin/blog`)
   - View all blog posts in a table
   - See thumbnail, title, category, author, status, and date
   - Quick actions: View, Edit, Publish/Unpublish, Delete
   - Statistics: Total blogs, Published count, Draft count

2. **Create Blog** (`/admin/blog/create`)
   - Title (required)
   - Slug (auto-generated from title, can be custom)
   - Category (optional)
   - Author (defaults to logged-in user)
   - Thumbnail image upload (optional, max 2MB)
   - Excerpt/Summary (required)
   - Full content with HTML support (required)
   - Publish immediately or save as draft

3. **Edit Blog** (`/admin/blog/{blog}/edit`)
   - All fields editable
   - Thumbnail preview and replacement
   - Toggle publish status

4. **View Blog** (`/admin/blog/{blog}`)
   - Full preview of blog post
   - Metadata display
   - Quick edit and publish actions

5. **Toggle Publish** (`POST /admin/blog/{blog}/toggle-publish`)
   - Quick publish/unpublish without editing

## API Endpoints

### Base URL
```
http://localhost:8000/api
```

Or set via environment variable:
```
PUBLIC_API_URL=http://localhost:8000/api
```

### 1. Get All Published Blogs

**Endpoint**: `GET /api/blog`

**Query Parameters**:
- `limit` (int, default: 10) - Number of posts per page
- `page` (int, default: 1) - Page number
- `category` (string, optional) - Filter by category

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "title": "Blog Title",
      "slug": "blog-title",
      "excerpt": "Short summary...",
      "thumbnail": "/storage/blogs/thumbnails/image.jpg",
      "category": "Education",
      "author": "Author Name",
      "created_at": "2026-04-08T04:31:24.000000Z"
    }
  ],
  "current_page": 1,
  "last_page": 1,
  "per_page": 10,
  "total": 4
}
```

### 2. Get Single Blog by Slug

**Endpoint**: `GET /api/blog/{slug}`

**Response**:
```json
{
  "id": 1,
  "title": "Blog Title",
  "slug": "blog-title",
  "excerpt": "Short summary...",
  "content": "<p>Full HTML content...</p>",
  "thumbnail": "/storage/blogs/thumbnails/image.jpg",
  "category": "Education",
  "author": "Author Name",
  "created_at": "2026-04-08T04:31:24.000000Z"
}
```

**Error Response** (404):
```json
{
  "error": "Blog post not found"
}
```

## Astro Frontend Integration

### Configuration

In `/website/src/config/siteConfig.ts`:
```typescript
api: {
  baseUrl: import.meta.env.PUBLIC_API_URL || "http://localhost:8000/api",
  timeout: 10000,
}
```

### API Functions

Located in `/website/src/lib/api.ts`:

```typescript
// Get latest blogs for homepage
getLatestBlogs(limit: number): Promise<BlogPost[]>

// Get paginated blog list
getBlogs(page: number, perPage: number): Promise<{
  data: BlogPost[],
  current_page: number,
  last_page: number
}>

// Get single blog by slug
getBlogBySlug(slug: string): Promise<BlogPost>
```

### TypeScript Interface

```typescript
interface BlogPost {
  id: number;
  title: string;
  slug: string;
  excerpt: string;
  content: string;      // Raw HTML
  thumbnail: string;    // Full URL
  category?: string;
  author?: string;
  created_at: string;   // ISO date
}
```

## Setup Instructions

### 1. Backend Setup (Laravel)

```bash
cd tabungan

# Install dependencies (if not done)
composer install

# Run migrations
php artisan migrate

# Seed sample blog posts (optional)
php artisan db:seed --class=BlogSeeder

# Start development server
php artisan serve --port=8000
```

### 2. Frontend Setup (Astro)

```bash
cd website

# Install dependencies (if not done)
npm install

# Set environment variable (optional)
# Create .env file with:
PUBLIC_API_URL=http://localhost:8000/api

# Start development server
npm run dev
```

### 3. Verify Integration

1. Login to Laravel admin: `http://localhost:8000/login`
   - Username: `admin@tabungan.id`
   - Password: `admin123`

2. Navigate to: **Blog & Artikel** in sidebar

3. Create a new blog post

4. Visit Astro website: `http://localhost:4321/blog`

5. The blog should appear in the listing

## File Upload Configuration

### Storage Link

Make sure the storage is linked:
```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`.

### Thumbnail Storage

- **Path**: `storage/app/public/blogs/thumbnails/`
- **URL**: `/storage/blogs/thumbnails/{filename}`
- **Max Size**: 2MB
- **Allowed Formats**: JPG, PNG, GIF, WEBP

## Sample Data

The `BlogSeeder` creates 4 sample blog posts:

1. Pentingnya Pendidikan Karakter di Pondok Pesantren
2. Kegiatan Hafalan Al-Quran Santri
3. Prestasi Santri dalam Musabaqah Tilawatil Quran
4. Pendaftaran Santri Baru Tahun Ajaran 2026/2027

To re-seed:
```bash
php artisan db:seed --class=BlogSeeder
```

## Security Features

1. **Role-based Access**: Only admin users can manage blogs
2. **CSRF Protection**: All forms include CSRF tokens
3. **File Validation**: Images validated for type and size
4. **SQL Injection Protection**: Laravel's Eloquent ORM
5. **XSS Protection**: Content escaped in Blade templates

## Content Formatting

### Supported HTML Tags in Content

Administrators can use these HTML tags in the content field:

- **Headings**: `<h1>`, `<h2>`, `<h3>`, `<h4>`, `<h5>`, `<h6>`
- **Paragraphs**: `<p>`
- **Lists**: `<ul>`, `<ol>`, `<li>`
- **Formatting**: `<strong>`, `<em>`, `<u>`, `<br>`
- **Links**: `<a href="">`
- **Images**: `<img src="" alt="">`
- **Tables**: `<table>`, `<tr>`, `<td>`, `<th>`
- **Blockquotes**: `<blockquote>`

### Example Content

```html
<h2>Introduction</h2>
<p>This is a sample blog post with <strong>formatted content</strong>.</p>

<h3>Key Points</h3>
<ul>
  <li>Point one</li>
  <li>Point two</li>
  <li>Point three</li>
</ul>

<p>For more information, <a href="/contact">contact us</a>.</p>
```

## Troubleshooting

### API Not Responding

1. Ensure Laravel server is running: `php artisan serve`
2. Check API URL in `/website/src/config/siteConfig.ts`
3. Verify CORS is not blocking requests
4. Check browser console for errors

### Images Not Displaying

1. Run: `php artisan storage:link`
2. Verify thumbnail upload permissions
3. Check file paths in database: `SELECT thumbnail FROM blogs;`

### Blog Posts Not Appearing

1. Ensure `is_published = true` in database
2. Check API endpoint: `curl http://localhost:8000/api/blog`
3. Verify Astro build process fetches data

### Admin Panel Access Issues

1. Login with admin credentials
2. Verify user role: `SELECT role FROM users WHERE email='admin@tabungan.id';`
3. Clear Laravel cache: `php artisan cache:clear`

## Advanced Customization

### Adding Custom Fields

1. Create migration: `php artisan make:migration add_field_to_blogs_table`
2. Update Blog model `$fillable` array
3. Update admin form views
4. Update API controller responses

### Adding Rich Text Editor

Replace the textarea in create/edit forms with a WYSIWYG editor like:
- TinyMCE
- CKEditor
- Quill

Example with TinyMCE:
```blade
<script src="https://cdn.tiny.cloud/1/YOUR-API-KEY/tinymce/6/tinymce.min.js"></script>
<script>
tinymce.init({
  selector: 'textarea[name="content"]',
  plugins: 'lists link image',
  toolbar: 'undo redo | bold italic | bullist numlist | link image'
});
</script>
```

### Adding SEO Fields

Add these fields to the blogs table:
- `meta_title` - Custom meta title
- `meta_description` - Custom meta description
- `meta_keywords` - Comma-separated keywords
- `canonical_url` - Canonical URL

## API Rate Limiting

Currently, API endpoints have no rate limiting. For production, consider adding rate limiting in `routes/api.php`:

```php
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/blog', [ApiBlogController::class, 'index']);
    Route::get('/blog/{slug}', [ApiBlogController::class, 'show']);
});
```

## Deployment Checklist

- [ ] Run migrations on production: `php artisan migrate`
- [ ] Seed initial data (if needed): `php artisan db:seed --class=BlogSeeder`
- [ ] Link storage: `php artisan storage:link`
- [ ] Set `PUBLIC_API_URL` in Astro `.env`
- [ ] Set proper file permissions for uploads
- [ ] Configure production API URL
- [ ] Test blog creation, editing, and deletion
- [ ] Test API endpoints
- [ ] Verify image uploads work
- [ ] Test publish/unpublish functionality

## Future Enhancements

1. **Search Functionality**: Add search API for blog posts
2. **Tags System**: Implement tagging for better organization
3. **Comments**: Allow reader comments on blog posts
4. **Scheduling**: Schedule posts for future publication
5. **Revisions**: Track post revision history
6. **Analytics**: Track blog post views
7. **Social Sharing**: Add social media sharing buttons
8. **RSS Feed**: Generate RSS feed for blog posts
9. **Categories Management**: CRUD for blog categories
10. **Multi-language Support**: Support multiple languages

## Support

For issues or questions:
- Check Laravel logs: `storage/logs/laravel.log`
- Check browser console for frontend errors
- Review this documentation
- Test API endpoints with curl or Postman

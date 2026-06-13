<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DashboardContent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DashboardContentController extends Controller
{
    public function index(Request $request)
    {
        $contents = DashboardContent::query()
            ->with(['creator', 'assignments.user'])
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->type))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.dashboard-content.index', [
            'contents' => $contents,
            'types' => DashboardContent::TYPES,
            'priorities' => DashboardContent::PRIORITIES,
            'petugasList' => User::where('role', 'petugas')->orderBy('name')->get(['id', 'name', 'jabatan']),
            'activeRole' => 'admin',
        ]);
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request): void {
            $data = $this->validated($request);
            $content = DashboardContent::create([
                ...$data,
                'created_by' => $request->user()->id,
                'published_at' => $data['is_published'] ? now() : null,
            ]);
            $this->syncAssignments($content, $request);
        });

        return back()->with('success', 'Konten dashboard berhasil ditambahkan.');
    }

    public function update(Request $request, DashboardContent $dashboardContent)
    {
        DB::transaction(function () use ($request, $dashboardContent): void {
            $data = $this->validated($request);
            $data['published_at'] = $data['is_published']
                ? ($dashboardContent->published_at ?? now())
                : null;
            $dashboardContent->update($data);
            $this->syncAssignments($dashboardContent, $request);
        });

        return back()->with('success', 'Konten dashboard berhasil diperbarui.');
    }

    public function destroy(DashboardContent $dashboardContent)
    {
        $dashboardContent->delete();

        return back()->with('success', 'Konten dashboard berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(DashboardContent::TYPES))],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:10000'],
            'thumbnail_url' => ['nullable', 'url:http,https', 'max:2048', 'required_if:type,news'],
            'priority' => ['nullable', Rule::in(array_keys(DashboardContent::PRIORITIES))],
            'event_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'required_if:type,todo'],
            'is_published' => ['required', 'boolean'],
            'assignment_scope' => ['nullable', 'required_if:type,todo', Rule::in(['all', 'selected'])],
            'assignee_ids' => ['nullable', 'required_if:assignment_scope,selected', 'array', 'min:1'],
            'assignee_ids.*' => ['integer', Rule::exists('users', 'id')->where('role', 'petugas')],
        ]);

        $validated['priority'] = $request->type === 'news' ? 'normal' : ($validated['priority'] ?? 'normal');
        $validated['thumbnail_url'] = $request->type === 'news' ? ($validated['thumbnail_url'] ?? null) : null;
        $validated['due_date'] = $request->type === 'todo' ? ($validated['due_date'] ?? null) : null;
        $validated['assign_to_all'] = $request->type === 'todo' && $request->assignment_scope === 'all';

        unset($validated['assignment_scope'], $validated['assignee_ids']);

        return $validated;
    }

    private function syncAssignments(DashboardContent $content, Request $request): void
    {
        if ($content->type !== 'todo') {
            $content->assignments()->delete();

            return;
        }

        $userIds = $request->assignment_scope === 'all'
            ? User::where('role', 'petugas')->pluck('id')->all()
            : array_map('intval', $request->input('assignee_ids', []));

        $content->assignments()
            ->whereNotIn('user_id', $userIds ?: [0])
            ->delete();

        foreach ($userIds as $userId) {
            $content->assignments()->firstOrCreate(['user_id' => $userId]);
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\DashboardContent;
use App\Models\DashboardContentAssignment;
use App\Models\User;
use App\Support\PermissionRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PetugasDashboardContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_petugas_without_finance_access_can_view_information_dashboard_but_not_finance_dashboard(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $petugas->assignRole(Role::findOrCreate('petugas'));
        $petugas->givePermissionTo(Permission::findOrCreate('petugas.dashboard.view'));
        DashboardContent::create([
            'created_by' => $this->admin()->id,
            'type' => 'announcement',
            'title' => 'Rapat Pengurus',
            'content' => 'Rapat dilaksanakan setelah Isya.',
            'priority' => 'important',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->actingAs($petugas)
            ->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertSee('Rapat Pengurus')
            ->assertSee('Berita Pondok')
            ->assertSee('To Do List');

        $this->actingAs($petugas)
            ->get(route('petugas.finance-dashboard'))
            ->assertForbidden();
    }

    public function test_petugas_with_finance_permission_can_view_moved_finance_dashboard(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);
        $petugas->assignRole(Role::findOrCreate('petugas'));
        $petugas->givePermissionTo(Permission::findOrCreate('petugas.finance.dashboard'));

        $this->actingAs($petugas)
            ->get(route('petugas.finance-dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Keuangan Petugas');
    }

    public function test_admin_cannot_remove_information_dashboard_access_from_petugas(): void
    {
        $admin = $this->admin();
        $admin->givePermissionTo(Permission::findOrCreate('admin.access.manage'));
        $petugas = User::factory()->create(['role' => 'petugas']);
        $petugas->assignRole(Role::findOrCreate('petugas'));

        $this->actingAs($admin)
            ->put(route('admin.access.update', $petugas), ['permissions' => []])
            ->assertRedirect();

        $this->assertTrue($petugas->fresh()->hasPermissionTo('petugas.dashboard.view'));
        $this->assertFalse($petugas->fresh()->hasPermissionTo('petugas.finance.dashboard'));
    }

    public function test_admin_can_create_dashboard_content_and_drafts_stay_hidden_from_petugas(): void
    {
        $admin = $this->admin();
        $petugas = User::factory()->create(['role' => 'petugas']);
        $petugas->assignRole(Role::findOrCreate('petugas'));
        $petugas->givePermissionTo(Permission::findOrCreate('petugas.dashboard.view'));

        $this->actingAs($admin)->post(route('admin.dashboard-content.store'), [
            'type' => 'news',
            'title' => 'Kegiatan Muhadharah',
            'summary' => 'Kegiatan pekanan pondok.',
            'content' => 'Muhadharah berlangsung dengan lancar.',
            'priority' => 'normal',
            'event_date' => today()->toDateString(),
            'due_date' => null,
            'is_published' => '1',
            'thumbnail_url' => 'https://example.com/muhadharah.jpg',
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('admin.dashboard-content.store'), [
            'type' => 'todo',
            'title' => 'Draft Agenda',
            'summary' => null,
            'content' => 'Konten ini belum diterbitkan.',
            'priority' => 'normal',
            'event_date' => null,
            'due_date' => today()->addDay()->toDateString(),
            'is_published' => '0',
            'assignment_scope' => 'all',
        ])->assertRedirect();

        $news = DashboardContent::where('title', 'Kegiatan Muhadharah')->firstOrFail();
        $this->actingAs($admin)
            ->get(route('admin.dashboard-content.index'))
            ->assertOk()
            ->assertSee('Kegiatan Muhadharah');

        $this->actingAs($admin)->put(route('admin.dashboard-content.update', $news), [
            'type' => 'news',
            'title' => 'Kegiatan Muhadharah Pekanan',
            'summary' => 'Kegiatan pekanan pondok.',
            'content' => 'Muhadharah berlangsung dengan lancar.',
            'priority' => 'important',
            'event_date' => today()->toDateString(),
            'due_date' => null,
            'is_published' => '1',
            'thumbnail_url' => 'https://example.com/muhadharah-baru.jpg',
        ])->assertRedirect();

        $this->actingAs($petugas)->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertSee('Kegiatan Muhadharah Pekanan')
            ->assertDontSee('Draft Agenda');

        $this->assertSame('https://example.com/muhadharah-baru.jpg', $news->fresh()->thumbnail_url);
    }

    public function test_todo_is_only_visible_to_assignees_and_disappears_after_completion(): void
    {
        $admin = $this->admin();
        $assigned = $this->petugas('Petugas Ditugaskan');
        $other = $this->petugas('Petugas Lain');

        $this->actingAs($admin)->post(route('admin.dashboard-content.store'), [
            'type' => 'todo',
            'title' => 'Siapkan Aula',
            'summary' => 'Persiapan rapat.',
            'content' => 'Rapikan aula sebelum rapat dimulai.',
            'priority' => 'important',
            'due_date' => today()->addDay()->toDateString(),
            'is_published' => '1',
            'assignment_scope' => 'selected',
            'assignee_ids' => [$assigned->id],
        ])->assertRedirect();

        $assignment = DashboardContentAssignment::firstOrFail();

        $this->actingAs($assigned)->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertSee('Siapkan Aula');

        $this->actingAs($other)->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertDontSee('Siapkan Aula');

        $this->actingAs($other)
            ->patch(route('petugas.dashboard.todo.complete', $assignment))
            ->assertForbidden();

        $this->actingAs($assigned)
            ->patch(route('petugas.dashboard.todo.complete', $assignment))
            ->assertRedirect();

        $assignment->refresh();
        $this->assertTrue($assignment->is_completed);
        $this->assertNotNull($assignment->completed_at);

        $this->actingAs($assigned)->get(route('petugas.dashboard'))
            ->assertOk()
            ->assertDontSee('Siapkan Aula');

        $this->actingAs($admin)->get(route('admin.dashboard-content.index'))
            ->assertOk()
            ->assertSee('1/1 selesai')
            ->assertSee('Petugas Ditugaskan');

        $task = $assignment->dashboardContent;
        $this->actingAs($admin)->put(route('admin.dashboard-content.update', $task), [
            'type' => 'todo',
            'title' => 'Siapkan Aula dan Peralatan',
            'summary' => 'Persiapan rapat.',
            'content' => 'Rapikan aula dan siapkan peralatan rapat.',
            'priority' => 'important',
            'due_date' => today()->addDays(2)->toDateString(),
            'is_published' => '1',
            'assignment_scope' => 'selected',
            'assignee_ids' => [$assigned->id, $other->id],
        ])->assertRedirect();

        $this->assertTrue($assignment->fresh()->is_completed);
        $this->assertDatabaseHas('dashboard_content_assignments', [
            'dashboard_content_id' => $task->id,
            'user_id' => $other->id,
            'is_completed' => false,
        ]);
    }

    public function test_global_todo_is_assigned_to_petugas_created_later(): void
    {
        $admin = $this->admin();
        $admin->givePermissionTo(Permission::findOrCreate('admin.petugas.manage'));
        Role::findOrCreate('petugas');
        foreach (PermissionRegistry::petugasDefaults() as $permission) {
            Permission::findOrCreate($permission);
        }

        $this->actingAs($admin)->post(route('admin.dashboard-content.store'), [
            'type' => 'todo',
            'title' => 'Baca SOP Baru',
            'content' => 'Baca SOP yang baru diterbitkan.',
            'priority' => 'normal',
            'due_date' => today()->addWeek()->toDateString(),
            'is_published' => '1',
            'assignment_scope' => 'all',
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('admin.petugas.store'), [
            'name' => 'Petugas Baru',
            'email' => 'petugas-baru@example.com',
            'nip' => 'PB-001',
            'jabatan' => 'Staff Pengurus',
            'password' => 'password',
        ])->assertRedirect();

        $petugas = User::where('email', 'petugas-baru@example.com')->firstOrFail();
        $this->assertDatabaseHas('dashboard_content_assignments', [
            'dashboard_content_id' => DashboardContent::where('title', 'Baca SOP Baru')->firstOrFail()->id,
            'user_id' => $petugas->id,
            'is_completed' => false,
        ]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole(Role::findOrCreate('admin'));
        $admin->givePermissionTo(Permission::findOrCreate('admin.dashboard-content.manage'));

        return $admin;
    }

    private function petugas(string $name): User
    {
        $petugas = User::factory()->create(['role' => 'petugas', 'name' => $name]);
        $petugas->assignRole(Role::findOrCreate('petugas'));
        $petugas->givePermissionTo(Permission::findOrCreate('petugas.dashboard.view'));

        return $petugas;
    }
}

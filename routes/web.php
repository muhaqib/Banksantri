<?php

use App\Http\Controllers\Admin\AccessController;
use App\Http\Controllers\Admin\AcademicClassController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\DashboardContentController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\KamarSantriController;
use App\Http\Controllers\Admin\KasController;
use App\Http\Controllers\Admin\LaundrySubscriptionController;
use App\Http\Controllers\Admin\PetugasController as AdminPetugasController;
use App\Http\Controllers\Admin\PrestasiSantriController as AdminPrestasiSantriController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\SantriController;
use App\Http\Controllers\Admin\SettlementController;
use App\Http\Controllers\Admin\TarbiyahSubjectController as AdminTarbiyahSubjectController;
use App\Http\Controllers\Admin\TopUpController as AdminTopUpController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\WahaScheduleController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\KitabController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use App\Http\Controllers\Petugas\HealthRecordController as PetugasHealthRecordController;
use App\Http\Controllers\Petugas\LaundryController;
use App\Http\Controllers\Petugas\ProfileController as PetugasProfileController;
use App\Http\Controllers\Petugas\RiwayatController as PetugasRiwayatController;
use App\Http\Controllers\Petugas\SecurityViolationController as PetugasSecurityViolationController;
use App\Http\Controllers\Petugas\TarbiyahGradeController as PetugasTarbiyahGradeController;
use App\Http\Controllers\Petugas\TarikTunaiController;
use App\Http\Controllers\Petugas\TransaksiController;
use App\Http\Controllers\Santri\AttendanceController as SantriAttendanceController;
use App\Http\Controllers\Santri\DashboardController as SantriDashboardController;
use App\Http\Controllers\Santri\HealthController as SantriHealthController;
use App\Http\Controllers\Santri\PermissionController as SantriPermissionHistoryController;
use App\Http\Controllers\Santri\PrestasiController as SantriPrestasiController;
use App\Http\Controllers\Santri\ProfileController as SantriProfileController;
use App\Http\Controllers\Santri\RiwayatController as SantriRiwayatController;
use App\Http\Controllers\Santri\SecurityController as SantriSecurityController;
use App\Http\Controllers\Santri\TarbiyahController as SantriTarbiyahController;
use App\Http\Controllers\Santri\TopUpController as SantriTopUpController;
use App\Http\Controllers\SantriPermissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

if (! function_exists('redirectToAuthenticatedDashboard')) {
    function redirectToAuthenticatedDashboard()
    {
        $role = auth()->user()?->role;
        if (in_array($role, ['admin', 'petugas', 'santri'])) {
            return match ($role) {
                'admin' => redirect()->route('admin.dashboard'),
                'petugas' => redirect()->route('petugas.dashboard'),
                'santri' => redirect()->route('santri.home'),
            };
        }

        // If user has an invalid role, log them out and redirect to login with an error
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->with('error', 'Peran pengguna tidak valid. Akun Anda telah dikeluarkan.');
    }
}

// Default redirect - MUST be before guest/auth middleware groups
Route::get('/', function () {
    if (auth()->check()) {
        return redirectToAuthenticatedDashboard();
    }

    return app(LoginController::class)->showRoleSelection();
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showRoleSelection'])->name('login');
    Route::get('/login/{role}', [LoginController::class, 'showLoginForm'])->name('login.role');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirectToAuthenticatedDashboard())->name('dashboard');

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->middleware('permission:admin.dashboard.view')->name('dashboard');
        Route::resource('dashboard-content', DashboardContentController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->middleware('permission:admin.dashboard-content.manage');
        Route::get('/akses', [AccessController::class, 'index'])->middleware('permission:admin.access.manage')->name('access.index');
        Route::put('/akses/{petugas}', [AccessController::class, 'update'])->middleware('permission:admin.access.manage')->name('access.update');

        Route::get('/kas', [KasController::class, 'index'])->middleware('permission:admin.finance.manage')->name('kas');
        Route::post('/kas', [KasController::class, 'store'])->middleware('permission:admin.finance.manage')->name('kas.store');

        Route::middleware('permission:admin.wa-schedules.manage')->group(function () {
            Route::get('/wa-schedules/status', [WahaScheduleController::class, 'status'])->name('wa-schedules.status');
            Route::get('/wa-schedules/groups', [WahaScheduleController::class, 'groups'])->name('wa-schedules.groups');
            Route::post('/wa-schedules/broadcast', [WahaScheduleController::class, 'broadcast'])->name('wa-schedules.broadcast');
            Route::delete('/wa-schedules/logs', [WahaScheduleController::class, 'clearLogs'])->name('wa-schedules.logs.clear');
            Route::patch('/wa-schedules/{waSchedule}/toggle', [WahaScheduleController::class, 'toggle'])->name('wa-schedules.toggle');
            Route::post('/wa-schedules/{waSchedule}/send-now', [WahaScheduleController::class, 'sendNow'])->name('wa-schedules.send-now');
            Route::resource('wa-schedules', WahaScheduleController::class)
                ->only(['index', 'store', 'update', 'destroy'])
                ->parameters(['wa-schedules' => 'waSchedule']);
        });

        // Santri Management
        Route::middleware('permission:admin.santri.manage')->group(function () {
            Route::post('santri/import', [SantriController::class, 'import'])->name('santri.import');
            Route::get('santri-export', [SantriController::class, 'export'])->name('santri.export');
            Route::get('santri-template', [SantriController::class, 'template'])->name('santri.template');
            Route::patch('santri/{santri}/graduate', [SantriController::class, 'graduate'])->name('santri.graduate');
            Route::patch('santri/{santri}/activate', [SantriController::class, 'activate'])->name('santri.activate');
            Route::resource('santri', SantriController::class)->except(['show']);
            Route::get('santri/{santri}/modal-data', [SantriController::class, 'getModalData'])->name('santri.modal-data');
            Route::get('santri/search', [SantriController::class, 'search'])->name('santri.search');
        });

        // Petugas Management
        Route::middleware('permission:admin.petugas.manage')->group(function () {
            Route::resource('petugas', AdminPetugasController::class)
                ->except(['show'])
                ->parameters(['petugas' => 'petugas']);
            Route::get('petugas/{petugas}/modal-data', [AdminPetugasController::class, 'getModalData'])->name('petugas.modal-data');
        });

        Route::get('/settlement', [SettlementController::class, 'index'])->middleware('permission:admin.finance.manage')->name('settlement');
        Route::patch('/settlement/{id}/approve', [SettlementController::class, 'approve'])->middleware('permission:admin.finance.manage')->name('settlement.approve');
        Route::patch('/settlement/{id}/reject', [SettlementController::class, 'reject'])->middleware('permission:admin.finance.manage')->name('settlement.reject');
        Route::post('/settlement/direct-withdraw', [SettlementController::class, 'directWithdraw'])->middleware('permission:admin.finance.manage')->name('settlement.direct');
        Route::middleware('permission:admin.laundry.manage')->group(function () {
            Route::get('/laundry-subscriptions', [LaundrySubscriptionController::class, 'index'])->name('laundry-subscriptions.index');
            Route::post('/laundry-subscriptions', [LaundrySubscriptionController::class, 'store'])->name('laundry-subscriptions.store');
            Route::post('/laundry-clothes', [LaundrySubscriptionController::class, 'storeCloth'])->name('laundry-clothes.store');
            Route::patch('/laundry-clothes/{cloth}', [LaundrySubscriptionController::class, 'updateCloth'])->name('laundry-clothes.update');
        });

        // Admin Transactions
        Route::middleware('permission:admin.finance.manage')->group(function () {
            Route::get('/transactions/topup', [AdminTransactionController::class, 'createTopUp'])->name('transactions.topup');
            Route::post('/transactions/topup', [AdminTransactionController::class, 'storeTopUp'])->name('transactions.topup.store');
            Route::get('/transactions/{transaction}/receipt', [AdminTransactionController::class, 'receipt'])->name('transactions.receipt');
            Route::post('/transactions/search-santri', [AdminTransactionController::class, 'searchSantri'])->name('transactions.search-santri');
            Route::get('/transactions/santri', [AdminTransactionController::class, 'santriList'])->name('transactions.santri');
            Route::get('/transactions/history', [AdminTransactionController::class, 'history'])->name('transactions.history');
        });

        // Top-Up Management
        Route::middleware('permission:admin.finance.manage')->group(function () {
            Route::get('/topup', [AdminTopUpController::class, 'index'])->name('topup');
            Route::get('/topup/{topUp}', [AdminTopUpController::class, 'show'])->name('topup.show');
            Route::get('/topup/{topUp}/modal-data', [AdminTopUpController::class, 'getModalData'])->name('topup.modal-data');
            Route::post('/topup/{topUp}/approve', [AdminTopUpController::class, 'approve'])->name('topup.approve');
            Route::post('/topup/{topUp}/reject', [AdminTopUpController::class, 'reject'])->name('topup.reject');
        });

        // Prestasi Santri Management
        Route::middleware('permission:admin.prestasi.manage')->group(function () {
            Route::resource('prestasi', AdminPrestasiSantriController::class)->except(['show']);
            Route::get('prestasi/{prestasi}/modal-data', [AdminPrestasiSantriController::class, 'getModalData'])->name('prestasi.modal-data');
            Route::post('kitab', [KitabController::class, 'store'])->name('kitab.store');
        });
        Route::middleware('permission:admin.tarbiyah.manage')->group(function () {
            Route::get('/classes/pondok', [AcademicClassController::class, 'pondokIndex'])->name('classes.pondok.index');
            Route::post('/classes/pondok', [AcademicClassController::class, 'storePondok'])->name('classes.pondok.store');
            Route::patch('/classes/pondok/{pondokClass}', [AcademicClassController::class, 'updatePondok'])->name('classes.pondok.update');
            Route::delete('/classes/pondok/{pondokClass}', [AcademicClassController::class, 'destroyPondok'])->name('classes.pondok.destroy');
            Route::get('/classes/formal', [AcademicClassController::class, 'formalIndex'])->name('classes.formal.index');
            Route::post('/classes/formal', [AcademicClassController::class, 'storeFormal'])->name('classes.formal.store');
            Route::patch('/classes/formal/{formalClass}', [AcademicClassController::class, 'updateFormal'])->name('classes.formal.update');
            Route::delete('/classes/formal/{formalClass}', [AcademicClassController::class, 'destroyFormal'])->name('classes.formal.destroy');
            Route::post('/classes/formal/promote-all', [AcademicClassController::class, 'promoteAllFormal'])->name('classes.formal.promote-all');
            Route::post('/classes/formal/{formalClass}/promote', [AcademicClassController::class, 'promoteFormal'])->name('classes.formal.promote');
            Route::get('/tarbiyah/subjects', [AdminTarbiyahSubjectController::class, 'index'])->name('tarbiyah.subjects.index');
            Route::post('/tarbiyah/subjects', [AdminTarbiyahSubjectController::class, 'store'])->name('tarbiyah.subjects.store');
            Route::patch('/tarbiyah/subjects/{subject}', [AdminTarbiyahSubjectController::class, 'update'])->name('tarbiyah.subjects.update');
            Route::delete('/tarbiyah/subjects/{subject}', [AdminTarbiyahSubjectController::class, 'destroy'])->name('tarbiyah.subjects.destroy');
            Route::post('/tarbiyah/monthly-exams', [AdminTarbiyahSubjectController::class, 'storeMonthlyExam'])->name('tarbiyah.monthly-exams.store');
            Route::patch('/tarbiyah/monthly-exams/{monthlyExam}', [AdminTarbiyahSubjectController::class, 'updateMonthlyExam'])->name('tarbiyah.monthly-exams.update');
            Route::delete('/tarbiyah/monthly-exams/{monthlyExam}', [AdminTarbiyahSubjectController::class, 'destroyMonthlyExam'])->name('tarbiyah.monthly-exams.destroy');
        });

        Route::get('/attendance', [AttendanceController::class, 'index'])->middleware('permission:admin.attendance.rfid')->name('attendance.index');
        Route::get('/attendance/rfid', [AttendanceController::class, 'rfid'])->middleware('permission:admin.attendance.rfid')->name('attendance.rfid');
        Route::post('/attendance/scan', [AttendanceController::class, 'scan'])->middleware('permission:admin.attendance.rfid')->name('attendance.scan');
        Route::get('/attendance/manual', [AttendanceController::class, 'manual'])->middleware('permission:admin.attendance.manual')->name('attendance.manual');
        Route::put('/attendance/bulk-update', [AttendanceController::class, 'bulkUpdate'])->middleware('permission:admin.attendance.manual')->name('attendance.bulk-update');
        Route::put('/attendance/{santri}', [AttendanceController::class, 'update'])->middleware('permission:admin.attendance.manual')->name('attendance.update');
        Route::get('/attendance-dashboard', [AttendanceController::class, 'dashboard'])->middleware('permission:admin.attendance.dashboard')->name('attendance.dashboard');
        Route::get('/attendance-monthly', [AttendanceController::class, 'monthly'])->middleware('permission:admin.attendance.monthly')->name('attendance.monthly');
        Route::get('/attendance/{santri}/detail', [AttendanceController::class, 'detail'])->middleware('permission:admin.attendance.monthly')->name('attendance.detail');
        Route::middleware('permission:admin.permissions.manage')->group(function () {
            Route::resource('permissions', SantriPermissionController::class)->except(['show']);
            Route::get('/permissions/{permission}/print', [SantriPermissionController::class, 'print'])->name('permissions.print');
            Route::post('/permissions/{permission}/arrived', [SantriPermissionController::class, 'arrived'])->name('permissions.arrived');
        });

        // Blog Management
        Route::middleware('permission:admin.blog.manage')->group(function () {
            Route::resource('blog', AdminBlogController::class)->except(['show']);
            Route::get('blog/{blog}', [AdminBlogController::class, 'show'])->name('blog.show');
            Route::post('blog/{blog}/toggle-publish', [AdminBlogController::class, 'togglePublish'])->name('blog.toggle-publish');
        });

        // Kamar Santri Management
        Route::middleware('permission:admin.kamar.manage')->group(function () {
            Route::get('/kamar', [KamarSantriController::class, 'index'])->name('kamar.index');
            Route::get('/kamar/available-santri', [KamarSantriController::class, 'getAvailableSantri'])->name('kamar.available-santri');
            Route::post('/kamar', [KamarSantriController::class, 'store'])->name('kamar.store');
            Route::get('/kamar/{kamar}', [KamarSantriController::class, 'show'])->name('kamar.show');
            Route::delete('/kamar/{id}', [KamarSantriController::class, 'destroy'])->name('kamar.destroy');
        });

        // Profile Management
        Route::get('/profile', [AdminProfileController::class, 'index'])->middleware('permission:admin.profile.manage')->name('profile');
        Route::post('/profile/email', [AdminProfileController::class, 'updateEmail'])->middleware('permission:admin.profile.manage')->name('profile.email');
        Route::post('/profile/password', [AdminProfileController::class, 'updatePassword'])->middleware('permission:admin.profile.manage')->name('profile.password');
    });

    // Petugas Routes
    Route::prefix('petugas')->name('petugas.')->middleware('role:petugas')->group(function () {
        Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->middleware('permission:petugas.dashboard.view')->name('dashboard');
        Route::patch('/dashboard/todo/{assignment}/complete', [PetugasDashboardController::class, 'completeTodo'])->middleware('permission:petugas.dashboard.view')->name('dashboard.todo.complete');
        Route::get('/blog/{blog}/lihat', [AdminBlogController::class, 'show'])->middleware('permission:petugas.dashboard.view')->name('blog.read');
        Route::get('/keuangan/dashboard', [PetugasDashboardController::class, 'finance'])->middleware('permission:petugas.finance.dashboard')->name('finance-dashboard');
        Route::get('/transaksi', [TransaksiController::class, 'index'])->middleware('permission:petugas.transactions.manage')->name('transaksi');
        Route::post('/transaksi/scan', [TransaksiController::class, 'scanRfid'])->middleware('permission:petugas.transactions.manage')->name('transaksi.scan');
        Route::post('/transaksi', [TransaksiController::class, 'store'])->middleware('permission:petugas.transactions.manage')->name('transaksi.store');
        Route::middleware('permission:petugas.santri.manage')->group(function () {
            Route::post('santri/import', [SantriController::class, 'import'])->name('santri.import');
            Route::get('santri-export', [SantriController::class, 'export'])->name('santri.export');
            Route::get('santri-template', [SantriController::class, 'template'])->name('santri.template');
            Route::patch('santri/{santri}/graduate', [SantriController::class, 'graduate'])->name('santri.graduate');
            Route::patch('santri/{santri}/activate', [SantriController::class, 'activate'])->name('santri.activate');
            Route::resource('santri', SantriController::class)->except(['show']);
            Route::get('santri/{santri}/modal-data', [SantriController::class, 'getModalData'])->name('santri.modal-data');
            Route::get('santri/search', [SantriController::class, 'search'])->name('santri.search');
        });
        Route::middleware('permission:petugas.laundry.manage')->group(function () {
            Route::get('/laundry', [LaundryController::class, 'index'])->name('laundry.index');
            Route::post('/laundry/scan', [LaundryController::class, 'scan'])->name('laundry.scan');
            Route::get('/laundry/search', [LaundryController::class, 'search'])->name('laundry.search');
            Route::post('/laundry', [LaundryController::class, 'store'])->name('laundry.store');
            Route::get('/laundry/{laundryTransaction}/receipt', [LaundryController::class, 'receipt'])->name('laundry.receipt');
        });
        Route::get('/laundry-history', [LaundryController::class, 'history'])->middleware('permission:petugas.laundry.history')->name('laundry.history');
        Route::resource('health', PetugasHealthRecordController::class)->middleware('permission:petugas.health.manage')->except(['show']);
        Route::resource('security', PetugasSecurityViolationController::class)->middleware('permission:petugas.security.manage')->except(['show']);
        Route::get('/riwayat', [PetugasRiwayatController::class, 'index'])->middleware('permission:petugas.history.view')->name('riwayat');
        Route::get('/tarik-tunai', [TarikTunaiController::class, 'index'])->middleware('permission:petugas.withdrawals.manage')->name('tarik-tunai');
        Route::post('/tarik-tunai', [TarikTunaiController::class, 'store'])->middleware('permission:petugas.withdrawals.manage')->name('tarik-tunai.store');

        Route::middleware('permission:petugas.prestasi.manage')->group(function () {
            Route::resource('prestasi', AdminPrestasiSantriController::class)->except(['show']);
            Route::get('prestasi/{prestasi}/modal-data', [AdminPrestasiSantriController::class, 'getModalData'])->name('prestasi.modal-data');
            Route::post('kitab', [KitabController::class, 'store'])->name('kitab.store');
        });
        Route::middleware('permission:petugas.tarbiyah.manage')->group(function () {
            Route::get('/tarbiyah/dashboard', [PetugasTarbiyahGradeController::class, 'dashboard'])->name('tarbiyah.dashboard');
            Route::get('/tarbiyah', [PetugasTarbiyahGradeController::class, 'index'])->name('tarbiyah.index');
            Route::post('/tarbiyah', [PetugasTarbiyahGradeController::class, 'store'])->name('tarbiyah.store');
            Route::post('/tarbiyah/import', [PetugasTarbiyahGradeController::class, 'import'])->name('tarbiyah.import');
            Route::get('/tarbiyah/template', [PetugasTarbiyahGradeController::class, 'template'])->name('tarbiyah.template');
            Route::post('/tarbiyah/monthly', [PetugasTarbiyahGradeController::class, 'storeMonthly'])->name('tarbiyah.monthly.store');
            Route::post('/tarbiyah/monthly/import', [PetugasTarbiyahGradeController::class, 'importMonthly'])->name('tarbiyah.monthly.import');
            Route::get('/tarbiyah/monthly/export', [PetugasTarbiyahGradeController::class, 'exportMonthly'])->name('tarbiyah.monthly.export');
            Route::post('/tarbiyah/promote', [PetugasTarbiyahGradeController::class, 'promote'])->name('tarbiyah.promote');
        });

        Route::get('/attendance', [AttendanceController::class, 'index'])->middleware('permission:petugas.attendance.rfid')->name('attendance.index');
        Route::get('/attendance/rfid', [AttendanceController::class, 'rfid'])->middleware('permission:petugas.attendance.rfid')->name('attendance.rfid');
        Route::post('/attendance/scan', [AttendanceController::class, 'scan'])->middleware('permission:petugas.attendance.rfid')->name('attendance.scan');
        Route::get('/attendance/manual', [AttendanceController::class, 'manual'])->middleware('permission:petugas.attendance.manual')->name('attendance.manual');
        Route::put('/attendance/bulk-update', [AttendanceController::class, 'bulkUpdate'])->middleware('permission:petugas.attendance.manual')->name('attendance.bulk-update');
        Route::put('/attendance/{santri}', [AttendanceController::class, 'update'])->middleware('permission:petugas.attendance.manual')->name('attendance.update');
        Route::get('/attendance-dashboard', [AttendanceController::class, 'dashboard'])->middleware('permission:petugas.attendance.dashboard')->name('attendance.dashboard');
        Route::get('/attendance-monthly', [AttendanceController::class, 'monthly'])->middleware('permission:petugas.attendance.monthly')->name('attendance.monthly');
        Route::get('/attendance/{santri}/detail', [AttendanceController::class, 'detail'])->middleware('permission:petugas.attendance.monthly')->name('attendance.detail');
        Route::middleware('permission:petugas.permissions.manage')->group(function () {
            Route::resource('permissions', SantriPermissionController::class)->except(['show']);
            Route::get('/permissions/{permission}/print', [SantriPermissionController::class, 'print'])->name('permissions.print');
            Route::post('/permissions/{permission}/arrived', [SantriPermissionController::class, 'arrived'])->name('permissions.arrived');
        });

        Route::middleware('permission:petugas.blog.manage')->group(function () {
            Route::resource('blog', AdminBlogController::class)->except(['show']);
            Route::get('blog/{blog}', [AdminBlogController::class, 'show'])->name('blog.show');
            Route::post('blog/{blog}/toggle-publish', [AdminBlogController::class, 'togglePublish'])->name('blog.toggle-publish');
        });

        // Profile Management
        Route::get('/profile', [PetugasProfileController::class, 'index'])->middleware('permission:petugas.profile.manage')->name('profile');
        Route::post('/profile/email', [PetugasProfileController::class, 'updateEmail'])->middleware('permission:petugas.profile.manage')->name('profile.email');
        Route::post('/profile/password', [PetugasProfileController::class, 'updatePassword'])->middleware('permission:petugas.profile.manage')->name('profile.password');
    });

    // Santri Routes
    Route::prefix('santri')->name('santri.')->middleware('role:santri')->group(function () {
        Route::get('/home', [SantriDashboardController::class, 'index'])->middleware('permission:santri.dashboard.view')->name('home');
        Route::get('/attendance', [SantriAttendanceController::class, 'index'])->middleware('permission:santri.dashboard.view')->name('attendance.index');
        Route::get('/riwayat', [SantriRiwayatController::class, 'index'])->middleware('permission:santri.history.view')->name('riwayat');
        Route::get('/permissions', [SantriPermissionHistoryController::class, 'index'])->middleware('permission:santri.dashboard.view')->name('permissions.index');
        Route::get('/health', [SantriHealthController::class, 'index'])->middleware('permission:santri.health.view')->name('health.index');
        Route::get('/security', [SantriSecurityController::class, 'index'])->middleware('permission:santri.security.view')->name('security.index');
        Route::get('/tarbiyah', [SantriTarbiyahController::class, 'index'])->middleware('permission:santri.tarbiyah.view')->name('tarbiyah.index');
        Route::get('/profile', [SantriProfileController::class, 'index'])->middleware('permission:santri.profile.manage')->name('profile');
        Route::post('/change-pin', [SantriProfileController::class, 'changePin'])->middleware(['permission:santri.profile.manage', 'santri.active'])->name('change-pin');
        Route::post('/profile/email', [SantriProfileController::class, 'updateEmail'])->middleware(['permission:santri.profile.manage', 'santri.active'])->name('profile.email');
        Route::post('/profile/password', [SantriProfileController::class, 'updatePassword'])->middleware(['permission:santri.profile.manage', 'santri.active'])->name('profile.password');
        Route::get('/topup', [SantriTopUpController::class, 'create'])->middleware('permission:santri.topup.manage')->name('topup');
        Route::post('/topup', [SantriTopUpController::class, 'store'])->middleware(['permission:santri.topup.manage', 'santri.active'])->name('topup.store');
        Route::get('/topup/status', [SantriTopUpController::class, 'getStatus'])->middleware('permission:santri.topup.manage')->name('topup.status');

        // Prestasi Routes
        Route::get('/prestasi', [SantriPrestasiController::class, 'index'])->middleware('permission:santri.prestasi.view')->name('prestasi');
        Route::get('/prestasi/{prestasi}', [SantriPrestasiController::class, 'show'])->middleware('permission:santri.prestasi.view')->name('prestasi.show');
    });
});

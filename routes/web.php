<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\KasController;
use App\Http\Controllers\Admin\PetugasController as AdminPetugasController;
use App\Http\Controllers\Admin\SantriController;
use App\Http\Controllers\Admin\SettlementController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboardController;
use App\Http\Controllers\Petugas\TransaksiController;
use App\Http\Controllers\Petugas\RiwayatController as PetugasRiwayatController;
use App\Http\Controllers\Petugas\TarikTunaiController;
use App\Http\Controllers\Santri\DashboardController as SantriDashboardController;
use App\Http\Controllers\Santri\RiwayatController as SantriRiwayatController;
use App\Http\Controllers\Santri\ProfileController as SantriProfileController;
use App\Http\Controllers\Santri\TopUpController as SantriTopUpController;
use App\Http\Controllers\Santri\PrestasiController as SantriPrestasiController;
use App\Http\Controllers\Admin\TopUpController as AdminTopUpController;
use App\Http\Controllers\Admin\PrestasiSantriController as AdminPrestasiSantriController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\KamarSantriController;
use App\Http\Controllers\Petugas\ProfileController as PetugasProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Default redirect - MUST be before guest/auth middleware groups
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif (auth()->user()->role === 'petugas') {
            return redirect()->route('petugas.dashboard');
        } else {
            return redirect()->route('santri.home');
        }
    }
    
    return redirect()->route('login');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/kas', [KasController::class, 'index'])->name('kas');
        Route::post('/kas', [KasController::class, 'store'])->name('kas.store');
        
        // Santri Management
        Route::resource('santri', SantriController::class)->except(['show']);
        Route::get('santri/{santri}/modal-data', [SantriController::class, 'getModalData'])->name('santri.modal-data');
        Route::get('santri/search', [SantriController::class, 'search'])->name('santri.search');

        // Petugas Management
        Route::resource('petugas', AdminPetugasController::class)
            ->except(['show'])
            ->parameters(['petugas' => 'petugas']);
        Route::get('petugas/{petugas}/modal-data', [AdminPetugasController::class, 'getModalData'])->name('petugas.modal-data');
        
        Route::get('/settlement', [SettlementController::class, 'index'])->name('settlement');
        Route::patch('/settlement/{id}/approve', [SettlementController::class, 'approve'])->name('settlement.approve');
        Route::patch('/settlement/{id}/reject', [SettlementController::class, 'reject'])->name('settlement.reject');
        
        // Admin Transactions
        Route::get('/transactions/topup', [AdminTransactionController::class, 'createTopUp'])->name('transactions.topup');
        Route::post('/transactions/topup', [AdminTransactionController::class, 'storeTopUp'])->name('transactions.topup.store');
        Route::post('/transactions/search-santri', [AdminTransactionController::class, 'searchSantri'])->name('transactions.search-santri');
        Route::get('/transactions/santri', [AdminTransactionController::class, 'santriList'])->name('transactions.santri');
        Route::get('/transactions/history', [AdminTransactionController::class, 'history'])->name('transactions.history');

        // Top-Up Management
        Route::get('/topup', [AdminTopUpController::class, 'index'])->name('topup');
        Route::get('/topup/{topUp}', [AdminTopUpController::class, 'show'])->name('topup.show');
        Route::get('/topup/{topUp}/modal-data', [AdminTopUpController::class, 'getModalData'])->name('topup.modal-data');
        Route::post('/topup/{topUp}/approve', [AdminTopUpController::class, 'approve'])->name('topup.approve');
        Route::post('/topup/{topUp}/reject', [AdminTopUpController::class, 'reject'])->name('topup.reject');

        // Prestasi Santri Management
        Route::resource('prestasi', AdminPrestasiSantriController::class)->except(['show']);
        Route::get('prestasi/{prestasi}/modal-data', [AdminPrestasiSantriController::class, 'getModalData'])->name('prestasi.modal-data');

        // Blog Management
        Route::resource('blog', AdminBlogController::class)->except(['show']);
        Route::get('blog/{blog}', [AdminBlogController::class, 'show'])->name('blog.show');
        Route::post('blog/{blog}/toggle-publish', [AdminBlogController::class, 'togglePublish'])->name('blog.toggle-publish');

        // Kamar Santri Management
        Route::get('/kamar', [KamarSantriController::class, 'index'])->name('kamar.index');
        Route::get('/kamar/available-santri', [KamarSantriController::class, 'getAvailableSantri'])->name('kamar.available-santri');
        Route::post('/kamar', [KamarSantriController::class, 'store'])->name('kamar.store');
        Route::get('/kamar/{kamar}', [KamarSantriController::class, 'show'])->name('kamar.show');
        Route::delete('/kamar/{id}', [KamarSantriController::class, 'destroy'])->name('kamar.destroy');

        // Profile Management
        Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile');
        Route::post('/profile/email', [AdminProfileController::class, 'updateEmail'])->name('profile.email');
        Route::post('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');
    });

    // Petugas Routes
    Route::prefix('petugas')->name('petugas.')->middleware('role:petugas')->group(function () {
        Route::get('/dashboard', [PetugasDashboardController::class, 'index'])->name('dashboard');
        Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi');
        Route::post('/transaksi/scan', [TransaksiController::class, 'scanRfid'])->name('transaksi.scan');
        Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');
        Route::get('/riwayat', [PetugasRiwayatController::class, 'index'])->name('riwayat');
        Route::get('/tarik-tunai', [TarikTunaiController::class, 'index'])->name('tarik-tunai');
        Route::post('/tarik-tunai', [TarikTunaiController::class, 'store'])->name('tarik-tunai.store');

        // Profile Management
        Route::get('/profile', [PetugasProfileController::class, 'index'])->name('profile');
        Route::post('/profile/email', [PetugasProfileController::class, 'updateEmail'])->name('profile.email');
        Route::post('/profile/password', [PetugasProfileController::class, 'updatePassword'])->name('profile.password');
    });

    // Santri Routes
    Route::prefix('santri')->name('santri.')->middleware('role:santri')->group(function () {
        Route::get('/home', [SantriDashboardController::class, 'index'])->name('home');
        Route::get('/riwayat', [SantriRiwayatController::class, 'index'])->name('riwayat');
        Route::get('/profile', [SantriProfileController::class, 'index'])->name('profile');
        Route::post('/change-pin', [SantriProfileController::class, 'changePin'])->name('change-pin');
        Route::post('/profile/email', [SantriProfileController::class, 'updateEmail'])->name('profile.email');
        Route::post('/profile/password', [SantriProfileController::class, 'updatePassword'])->name('profile.password');
        Route::get('/topup', [SantriTopUpController::class, 'create'])->name('topup');
        Route::post('/topup', [SantriTopUpController::class, 'store'])->name('topup.store');
        Route::get('/topup/status', [SantriTopUpController::class, 'getStatus'])->name('topup.status');
        
        // Prestasi Routes
        Route::get('/prestasi', [SantriPrestasiController::class, 'index'])->name('prestasi');
        Route::get('/prestasi/{prestasi}', [SantriPrestasiController::class, 'show'])->name('prestasi.show');
    });

    // Default redirect
    Route::get('/', function () {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif (auth()->user()->role === 'petugas') {
            return redirect()->route('petugas.dashboard');
        } else {
            return redirect()->route('santri.home');
        }
    });
});

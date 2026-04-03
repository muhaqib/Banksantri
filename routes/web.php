<?php

use App\Http\Controllers\Auth\LoginController;
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
use App\Http\Controllers\Santri\ProfileController;
use App\Http\Controllers\Santri\TopUpController as SantriTopUpController;
use App\Http\Controllers\Admin\TopUpController as AdminTopUpController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
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
        Route::get('santri/{santri}', function() {
            return redirect()->route('admin.santri.index');
        })->name('santri.redirect');
        Route::get('santri/{santri}/modal-data', [SantriController::class, 'getModalData'])->name('santri.modal-data');

        // Petugas Management
        Route::resource('petugas', AdminPetugasController::class)->except(['show']);
        Route::get('petugas/{petugas}', function() {
            return redirect()->route('admin.petugas.index');
        })->name('petugas.redirect');
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
    });

    // Santri Routes
    Route::prefix('santri')->name('santri.')->middleware('role:santri')->group(function () {
        Route::get('/home', [SantriDashboardController::class, 'index'])->name('home');
        Route::get('/riwayat', [SantriRiwayatController::class, 'index'])->name('riwayat');
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::post('/change-pin', [ProfileController::class, 'changePin'])->name('change-pin');
        Route::get('/topup', [SantriTopUpController::class, 'create'])->name('topup');
        Route::post('/topup', [SantriTopUpController::class, 'store'])->name('topup.store');
        Route::get('/topup/status', [SantriTopUpController::class, 'getStatus'])->name('topup.status');
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

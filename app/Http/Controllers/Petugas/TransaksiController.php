<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransaksiController extends Controller
{
    public function index()
    {
        // Get current user's jabatan and determine category
        $jabatan = auth()->user()->jabatan ?? '';

        // Map jabatan to kategori
        $kategoriMapping = $this->getKategoriFromJabatan($jabatan);

        return view('pages.petugas.transaksi', [
            'activeRole' => 'petugas',
            'kategoriLabel' => $kategoriMapping['label'],
            'kategoriValue' => $kategoriMapping['value'],
            'kategoriIcon' => $kategoriMapping['icon'],
        ]);
    }

    /**
     * Map petugas jabatan to transaction category
     */
    private function getKategoriFromJabatan($jabatan)
    {
        // Map jabatan to kategori
        if ($jabatan === 'Kepala Unit') {
            return [
                'label' => 'Mengambil Tabungan',
                'value' => 'tarik uang',
                'icon' => 'account_balance_wallet',
            ];
        } elseif ($jabatan === 'Staff Pengurus') {
            return [
                'label' => 'Mengambil Tabungan',
                'value' => 'tarik uang',
                'icon' => 'account_balance_wallet',
            ];
        } elseif ($jabatan === 'Petugas Laundry') {
            return [
                'label' => 'Laundry',
                'value' => 'laundry',
                'icon' => 'dry_cleaning',
            ];
        } elseif ($jabatan === 'Petugas Syirkah') {
            return [
                'label' => 'Syirkah',
                'value' => 'syirkah',
                'icon' => 'store',
            ];
        } elseif ($jabatan === 'Koperasi Kitab') {
            return [
                'label' => 'Kitab',
                'value' => 'beli kitab',
                'icon' => 'menu_book',
            ];
        } elseif ($jabatan === 'Petugas Mart') {
            return [
                'label' => 'Mart',
                'value' => 'mart',
                'icon' => 'storefront',
            ];
        }

        // Default category
        return [
            'label' => 'Transaksi',
            'value' => 'lainnya',
            'icon' => 'point_of_sale',
        ];
    }

    public function scanRfid(Request $request)
    {
        return $this->cariSantri($request);
    }

    public function cariSantri(Request $request)
    {
        \Log::info('Cari Santri - RFID Code: '.$request->rfid_code);

        $request->validate([
            'rfid_code' => 'required|string',
        ]);

        $santri = User::activeSantri()
            ->where('rfid_code', $request->rfid_code)
            ->first();

        \Log::info('Santri found: '.($santri ? $santri->name : 'null'));

        if (! $santri) {
            return response()->json([
                'success' => false,
                'message' => 'Santri tidak ditemukan',
            ], 404);
        }

        // Get recent transactions
        $riwayat = Transaction::where('santri_id', $santri->id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $santri->id,
                'nis' => $santri->nis,
                'nama' => $santri->name,
                'saldo' => $santri->saldo,
                'foto_url' => $santri->foto ? asset('storage/'.$santri->foto) : null,
                'riwayat' => $riwayat,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'santri_id' => ['required', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'santri')->where('santri_status', 'aktif'))],
            'nominal' => 'required|numeric|min:1000',
            'kategori' => 'required|in:kantin,koperasi,laundry,fotokopi,lainnya,tarik uang,syirkah,beli kitab,mart',
            'keterangan' => 'nullable|string|max:500',
            'pin' => 'required|string|size:6',
        ], [
            'santri_id.required' => 'Data santri belum dipilih.',
            'santri_id.exists' => 'Data santri tidak ditemukan.',
            'nominal.required' => 'Nominal transaksi wajib diisi.',
            'nominal.numeric' => 'Nominal transaksi harus berupa angka.',
            'nominal.min' => 'Nominal transaksi minimal Rp 1.000.',
            'kategori.required' => 'Kategori transaksi wajib diisi.',
            'kategori.in' => 'Kategori transaksi tidak valid.',
            'keterangan.max' => 'Catatan maksimal 500 karakter.',
            'pin.required' => 'PIN santri wajib diisi.',
            'pin.size' => 'PIN santri harus 6 digit.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->except('pin'));
        }

        $validated = $validator->validated();
        try {
            DB::transaction(function () use ($validated) {
                $santri = User::activeSantri()
                    ->whereKey($validated['santri_id'])
                    ->lockForUpdate()
                    ->firstOrFail();
                $petugas = User::whereKey(Auth::id())
                    ->where('role', 'petugas')
                    ->lockForUpdate()
                    ->firstOrFail();

                // Verify hashed PIN while transparently migrating legacy plaintext PINs.
                if (! $santri->verifyPin($validated['pin'])) {
                    throw ValidationException::withMessages([
                        'pin' => 'PIN salah. Silakan periksa kembali PIN santri.',
                    ]);
                }

                $nominal = (int) $validated['nominal'];
                $saldoSebelum = (int) $santri->saldo;

                if ($saldoSebelum < $nominal) {
                    throw ValidationException::withMessages([
                        'nominal' => 'Saldo santri tidak mencukupi untuk nominal transaksi ini.',
                    ]);
                }

                $saldoSetelah = $saldoSebelum - $nominal;

                Transaction::create([
                    'santri_id' => $santri->id,
                    'petugas_id' => $petugas->id,
                    'jenis' => 'keluar',
                    'nominal' => $nominal,
                    'kategori' => $validated['kategori'],
                    'keterangan' => $validated['keterangan'] ?? null,
                    'saldo_sebelum' => $saldoSebelum,
                    'saldo_setelah' => $saldoSetelah,
                ]);

                $santri->update(['saldo' => $saldoSetelah]);
                $petugas->update(['saldo' => (int) $petugas->saldo + $nominal]);
            });

            return redirect()->route('petugas.transaksi')->with('success', 'Transaksi berhasil diproses');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput($request->except('pin'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan saat memproses transaksi: '.$e->getMessage()])->withInput($request->except('pin'));
        }
    }
}

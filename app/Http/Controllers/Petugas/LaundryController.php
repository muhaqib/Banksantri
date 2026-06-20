<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\LaundryCloth;
use App\Models\LaundrySubscription;
use App\Models\LaundryTransaction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LaundryController extends Controller
{
    public function index()
    {
        return view('pages.petugas.laundry.index', [
            'activeRole' => 'petugas',
            'clothes' => $this->clothes(),
            'today' => today(),
            'defaultPricePerKg' => 7000,
            'defaultQuotaKg' => 12,
        ]);
    }

    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rfid_code' => ['required', 'string'],
            'date' => ['nullable', 'date'],
        ]);
        $date = isset($validated['date']) ? \Carbon\Carbon::parse($validated['date']) : today();

        $santri = User::activeSantri()
            ->where('rfid_code', $validated['rfid_code'])
            ->first();

        if (! $santri) {
            return response()->json([
                'success' => false,
                'message' => 'Santri tidak ditemukan.',
            ], 404);
        }

        $subscription = LaundrySubscription::query()
            ->where('santri_id', $santri->id)
            ->where('month', $date->month)
            ->where('year', $date->year)
            ->where('status', 'active')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $santri->id,
                'nis' => $santri->nis,
                'nama' => $santri->name,
                'rfid_code' => $santri->rfid_code,
                'foto_url' => $santri->foto ? asset('storage/'.$santri->foto) : null,
                'subscription' => $subscription ? [
                    'id' => $subscription->id,
                    'month' => $subscription->month,
                    'year' => $subscription->year,
                    'quota_kg' => (float) $subscription->quota_kg,
                    'used_kg' => (float) $subscription->used_kg,
                    'remaining_kg' => $subscription->remaining_kg,
                    'monthly_fee' => $subscription->monthly_fee,
                ] : null,
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['nullable', 'string', 'max:100'],
            'payment_type' => ['required', Rule::in(['tunai', 'bulanan'])],
            'date' => ['nullable', 'date'],
        ]);

        $date = isset($validated['date']) ? \Carbon\Carbon::parse($validated['date']) : today();
        $term = trim($validated['query'] ?? '');

        $query = User::activeSantri()
            ->when($term !== '', function ($builder) use ($term): void {
                $builder->where(function ($nested) use ($term): void {
                    $nested->where('name', 'like', "%{$term}%")
                        ->orWhere('nis', 'like', "%{$term}%")
                        ->orWhere('rfid_code', 'like', "%{$term}%");
                });
            })
            ->when($validated['payment_type'] === 'bulanan', function ($builder) use ($date): void {
                $builder->whereHas('laundrySubscriptions', function ($subscription) use ($date): void {
                    $subscription->where('month', $date->month)
                        ->where('year', $date->year)
                        ->where('status', 'active');
                });
            })
            ->orderBy('name')
            ->limit(8);

        return response()->json([
            'success' => true,
            'data' => $query->get()->map(fn (User $santri) => [
                'id' => $santri->id,
                'nis' => $santri->nis,
                'nama' => $santri->name,
                'rfid_code' => $santri->rfid_code,
                'saldo' => (int) $santri->saldo,
                'foto_url' => $santri->foto ? asset('storage/'.$santri->foto) : null,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'santri_id' => ['required', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'santri')->where('santri_status', 'aktif'))],
            'payment_type' => ['required', Rule::in(['tunai', 'bulanan'])],
            'payment_method' => ['required_if:payment_type,tunai', Rule::in(['cash', 'saldo_tabungan', 'quota_bulanan'])],
            'laundry_date' => ['required', 'date'],
            'weight_kg' => ['required', 'numeric', 'min:0.1'],
            'price_per_kg' => ['required', 'integer', 'min:0'],
            'clothes' => ['required', 'array'],
            'clothes.*' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'pin' => ['nullable', 'required_if:payment_method,saldo_tabungan', 'string', 'size:6'],
        ], [
            'santri_id.required' => 'Data santri belum dipilih.',
            'weight_kg.required' => 'Berat laundry wajib diisi.',
            'weight_kg.min' => 'Berat laundry minimal 0,1 Kg.',
            'pin.required_if' => 'PIN santri wajib diisi untuk pembayaran saldo tabungan.',
            'pin.size' => 'PIN santri harus 6 digit.',
        ]);

        $laundryTransaction = null;

        try {
            $laundryTransaction = DB::transaction(function () use ($validated): LaundryTransaction {
                $santri = User::activeSantri()
                    ->whereKey($validated['santri_id'])
                    ->lockForUpdate()
                    ->firstOrFail();
                $petugas = User::whereKey(Auth::id())
                    ->where('role', 'petugas')
                    ->lockForUpdate()
                    ->firstOrFail();

                $paymentType = $validated['payment_type'];
                $paymentMethod = $paymentType === 'tunai' ? $validated['payment_method'] : 'quota_bulanan';

                if ($paymentMethod === 'saldo_tabungan' && ! $santri->verifyPin($validated['pin'])) {
                    throw ValidationException::withMessages([
                        'pin' => 'PIN salah. Silakan periksa kembali PIN santri.',
                    ]);
                }

                $weightKg = (float) $validated['weight_kg'];
                $pricePerKg = (int) $validated['price_per_kg'];
                $totalPrice = (int) round($weightKg * $pricePerKg);
                $clothesDetail = $this->normalizeClothes($validated['clothes']);
                $totalClothes = array_sum(array_column($clothesDetail, 'quantity'));
                $subscription = null;
                $transaction = null;

                if ($paymentType === 'bulanan') {
                    $date = \Carbon\Carbon::parse($validated['laundry_date']);
                    $subscription = LaundrySubscription::query()
                        ->where('santri_id', $santri->id)
                        ->where('month', $date->month)
                        ->where('year', $date->year)
                        ->where('status', 'active')
                        ->lockForUpdate()
                        ->first();

                    if (! $subscription) {
                        throw ValidationException::withMessages([
                            'payment_type' => 'Santri belum terdaftar laundry bulanan pada bulan ini.',
                        ]);
                    }

                    if (! $subscription->canUse($weightKg)) {
                        throw ValidationException::withMessages([
                            'weight_kg' => 'Kuota laundry bulanan tidak mencukupi. Sisa kuota '.$subscription->remaining_kg.' Kg.',
                        ]);
                    }

                    $subscription->increment('used_kg', $weightKg);
                } elseif ($paymentMethod === 'saldo_tabungan') {
                    $saldoSebelum = (int) $santri->saldo;

                    if ($saldoSebelum < $totalPrice) {
                        throw ValidationException::withMessages([
                            'price_per_kg' => 'Saldo santri tidak mencukupi untuk pembayaran laundry ini.',
                        ]);
                    }

                    $saldoSetelah = $saldoSebelum - $totalPrice;
                    $transaction = Transaction::create([
                        'santri_id' => $santri->id,
                        'petugas_id' => $petugas->id,
                        'jenis' => 'keluar',
                        'nominal' => $totalPrice,
                        'kategori' => 'laundry',
                        'keterangan' => 'Laundry '.$weightKg.' Kg',
                        'saldo_sebelum' => $saldoSebelum,
                        'saldo_setelah' => $saldoSetelah,
                    ]);

                    $santri->update(['saldo' => $saldoSetelah]);
                    $petugas->update(['saldo' => (int) $petugas->saldo + $totalPrice]);
                }

                return LaundryTransaction::create([
                    'santri_id' => $santri->id,
                    'petugas_id' => $petugas->id,
                    'laundry_subscription_id' => $subscription?->id,
                    'transaction_id' => $transaction?->id,
                    'payment_type' => $paymentType,
                    'payment_method' => $paymentMethod,
                    'laundry_date' => $validated['laundry_date'],
                    'weight_kg' => $weightKg,
                    'price_per_kg' => $pricePerKg,
                    'total_price' => $totalPrice,
                    'total_clothes' => $totalClothes,
                    'clothes_detail' => $clothesDetail,
                    'notes' => $validated['notes'] ?? null,
                ]);
            });

            return redirect()->route('petugas.laundry.receipt', $laundryTransaction)->with('success', 'Transaksi laundry berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput($request->except('pin'));
        }
    }

    public function history(Request $request)
    {
        $petugas = Auth::user();
        $date = \Carbon\Carbon::parse($request->input('date', today()->toDateString()));

        $base = LaundryTransaction::query()
            ->with(['santri', 'petugas', 'subscription'])
            ->where('petugas_id', $petugas->id)
            ->whereDate('laundry_date', $date);

        return view('pages.petugas.laundry.history', [
            'activeRole' => 'petugas',
            'date' => $date,
            'transactions' => (clone $base)->latest()->paginate(15)->withQueryString(),
            'totalTransactions' => (clone $base)->count(),
            'totalWeight' => (float) (clone $base)->sum('weight_kg'),
            'totalCash' => (int) (clone $base)->where('payment_method', 'cash')->sum('total_price'),
            'totalSaldo' => (int) (clone $base)->where('payment_method', 'saldo_tabungan')->sum('total_price'),
        ]);
    }

    public function receipt(LaundryTransaction $laundryTransaction)
    {
        abort_unless($laundryTransaction->petugas_id === Auth::id(), 403);

        return view('pages.petugas.laundry.receipt', [
            'activeRole' => 'petugas',
            'transaction' => $laundryTransaction->load(['santri', 'petugas', 'subscription', 'transaction']),
        ]);
    }

    private function normalizeClothes(array $clothes): array
    {
        $items = [];
        foreach ($this->clothes() as $key => $meta) {
            $quantity = max(0, (int) ($clothes[$key] ?? 0));
            if ($quantity === 0) {
                continue;
            }

            $items[] = [
                'key' => $key,
                'label' => $meta['label'],
                'quantity' => $quantity,
            ];
        }

        if ($items === []) {
            throw ValidationException::withMessages([
                'clothes' => 'Rincian baju wajib diisi minimal satu item.',
            ]);
        }

        return $items;
    }

    private function clothes(): array
    {
        return LaundryCloth::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->mapWithKeys(fn (LaundryCloth $cloth) => [
                $cloth->key => ['label' => $cloth->label, 'icon' => $cloth->icon],
            ])
            ->all();
    }
}

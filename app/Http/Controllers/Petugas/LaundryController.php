<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\LaundrySubscription;
use App\Models\LaundryTransaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LaundryController extends Controller
{
    private const CLOTHES = [
        'kemeja' => ['label' => 'Kemeja', 'icon' => 'checkroom'],
        'celana' => ['label' => 'Celana', 'icon' => 'apparel'],
        'sarung' => ['label' => 'Sarung', 'icon' => 'texture'],
        'jaket' => ['label' => 'Jaket', 'icon' => 'dry_cleaning'],
        'kaos' => ['label' => 'Kaos', 'icon' => 'styler'],
        'mukena' => ['label' => 'Mukena', 'icon' => 'woman'],
        'jilbab' => ['label' => 'Jilbab', 'icon' => 'face_3'],
        'handuk' => ['label' => 'Handuk', 'icon' => 'layers'],
    ];

    public function index()
    {
        return view('pages.petugas.laundry.index', [
            'activeRole' => 'petugas',
            'clothes' => self::CLOTHES,
            'today' => today(),
            'defaultPricePerKg' => 7000,
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'santri_id' => ['required', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'santri')->where('santri_status', 'aktif'))],
            'payment_type' => ['required', Rule::in(['tunai', 'bulanan'])],
            'laundry_date' => ['required', 'date'],
            'weight_kg' => ['required', 'numeric', 'min:0.1'],
            'price_per_kg' => ['required', 'integer', 'min:0'],
            'clothes' => ['required', 'array'],
            'clothes.*' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'pin' => ['required', 'string', 'size:6'],
        ], [
            'santri_id.required' => 'Data santri belum dipilih.',
            'weight_kg.required' => 'Berat laundry wajib diisi.',
            'weight_kg.min' => 'Berat laundry minimal 0,1 Kg.',
            'pin.size' => 'PIN santri harus 6 digit.',
        ]);

        try {
            DB::transaction(function () use ($validated): void {
                $santri = User::activeSantri()
                    ->whereKey($validated['santri_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $santri->verifyPin($validated['pin'])) {
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

                if ($validated['payment_type'] === 'bulanan') {
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
                }

                LaundryTransaction::create([
                    'santri_id' => $santri->id,
                    'petugas_id' => Auth::id(),
                    'laundry_subscription_id' => $subscription?->id,
                    'payment_type' => $validated['payment_type'],
                    'laundry_date' => $validated['laundry_date'],
                    'weight_kg' => $weightKg,
                    'price_per_kg' => $pricePerKg,
                    'total_price' => $totalPrice,
                    'total_clothes' => $totalClothes,
                    'clothes_detail' => $clothesDetail,
                    'notes' => $validated['notes'] ?? null,
                ]);
            });

            return redirect()->route('petugas.laundry.index')->with('success', 'Transaksi laundry berhasil disimpan.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput($request->except('pin'));
        }
    }

    private function normalizeClothes(array $clothes): array
    {
        $items = [];
        foreach (self::CLOTHES as $key => $meta) {
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
}

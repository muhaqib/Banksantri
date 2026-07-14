@extends('layouts.app')

@section('header-title', 'Penarikan Tunai Petugas')

@section('content')
<div x-data="settlementManager()" class="space-y-6">
    <!-- Header Title with Direct Withdraw Trigger Button -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-2">
        <div>
            <h1 class="font-headline text-2xl font-bold text-primary">Penarikan Tunai Petugas</h1>
            <p class="mt-0.5 text-xs text-on-surface-variant">Kelola penarikan saldo digital dari transaksi petugas ke kas pondok.</p>
        </div>
        <button @click="isWithdrawModalOpen = true" class="btn-primary py-2.5 px-4 rounded-lg flex items-center justify-center gap-2 cursor-pointer shadow-sm">
            <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
            Tarik Saldo Petugas
        </button>
    </div>

    <!-- Pending Requests -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-4 sm:p-5 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Permintaan Penarikan Tunai Pending</h3>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($pendingRequests ?? [] as $request)
                <div class="p-4 sm:p-5 hover:bg-gray-50">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $request->petugas->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $request->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900">Rp {{ number_format($request->nominal, 0, ',', '.') }}</p>
                            <span class="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded-full">Pending</span>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3">
                        <form action="{{ route('admin.settlement.approve', $request->id) }}" method="POST" class="flex-1">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="w-full bg-emerald-600 text-white font-semibold py-2 rounded-lg hover:bg-emerald-700 transition-colors flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Setujui & Bayar
                            </button>
                        </form>
                        <form action="{{ route('admin.settlement.reject', $request->id) }}" method="POST" class="flex-1">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="w-full bg-gray-200 text-gray-700 font-semibold py-2 rounded-lg hover:bg-gray-300 transition-colors flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Tolak
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-5 sm:p-6 text-center text-gray-500">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p>Tidak ada permintaan penarikan pending</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Approved History -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-4 sm:p-5 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Riwayat Penarikan Tunai</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Petugas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibayar Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($settlementHistory ?? [] as $settlement)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $settlement->petugas->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $settlement->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-semibold text-gray-900">Rp {{ number_format($settlement->nominal, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $settlement->status === 'approved' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                    {{ ucfirst($settlement->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $settlement->approver->name ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                Belum ada riwayat penarikan tunai
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Direct Withdraw Modal -->
    <div x-show="isWithdrawModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="isWithdrawModalOpen = false"></div>

        <!-- Modal Container -->
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl border border-gray-200 transition-all transform"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="scale-95 translate-y-4"
                 x-transition:enter-end="scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="scale-100 translate-y-0"
                 x-transition:leave-end="scale-95 translate-y-4"
                 @click.stop>
                
                <!-- Close Button -->
                <button @click="isWithdrawModalOpen = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-1.5 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer">
                    <span class="material-symbols-outlined">close</span>
                </button>

                <div class="space-y-4 text-left">
                    <div>
                        <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Keuangan</p>
                        <h3 class="font-headline text-xl font-bold text-gray-900 mt-1">Tarik Saldo Petugas</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Tarik saldo digital dari petugas langsung ke kas utama.</p>
                    </div>

                    <form action="{{ route('admin.settlement.direct') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="mb-1 block text-xs font-bold text-gray-700">Pilih Petugas</label>
                            <select name="petugas_id" x-model="selectedPetugasId" required class="input-field w-full">
                                <option value="">-- Pilih Petugas --</option>
                                @foreach($petugasList as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} (Saldo: Rp {{ number_format($p->saldo, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold text-gray-700">Nominal Penarikan (Rp)</label>
                            <input type="number" name="nominal" required min="1000" placeholder="Contoh: 50000" class="input-field w-full">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold text-gray-700">Catatan</label>
                            <textarea name="catatan" placeholder="Catatan opsional..." rows="3" class="input-field w-full"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="isWithdrawModalOpen = false" class="btn-secondary py-2 px-4 rounded-lg cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="btn-primary py-2 px-4 rounded-lg cursor-pointer flex items-center gap-1.5 shadow-sm">
                                <span class="material-symbols-outlined text-sm">payments</span>
                                Lakukan Penarikan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function settlementManager() {
    return {
        isWithdrawModalOpen: false,
        selectedPetugasId: '',
    }
}
</script>
@endsection

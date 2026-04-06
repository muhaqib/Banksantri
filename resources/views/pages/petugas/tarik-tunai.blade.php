@extends('layouts.app')

@section('header-title', 'Tarik Tunai')
@php $activeRole = 'petugas'; @endphp

@section('content')
<div x-data="tarikTunai()">
    <!-- Hero Layout: Balance Card -->
    <div class="relative overflow-hidden rounded-xl p-8 mb-6 bg-gradient-to-br from-primary to-primary-container text-on-primary shadow-xl shadow-primary/20">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-primary-fixed font-medium mb-1">Saldo Digital Tersedia</p>
                <h2 class="text-4xl font-extrabold font-headline tracking-tighter">Rp {{ number_format($saldoDigital ?? 0, 0, ',', '.') }}</h2>
            </div>
            <div class="w-20 h-20 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-5xl" style="font-variation-settings: 'FILL' 1;">account_balance_wallet</span>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/5">
                <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                <span class="text-xs">Verified funds ready for withdrawal</span>
            </div>
        </div>
        
        <!-- Decorative Background Element -->
        <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full bg-primary-fixed opacity-5 blur-3xl"></div>
    </div>

    <!-- Main Grid: Form + Pending -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
        <!-- Request Form -->
        <div class="lg:col-span-7 bg-surface-container-lowest rounded-xl p-8 shadow-sm">
            <div class="flex items-center gap-3 mb-6">
                <span class="material-symbols-outlined text-primary p-2 bg-primary-fixed rounded-full" style="font-variation-settings: 'FILL' 1;">download</span>
                <div>
                    <h3 class="font-headline font-bold text-xl text-primary">Request Tarik Tunai</h3>
                    <p class="text-xs text-on-surface-variant">Convert your digital balance into physical cash</p>
                </div>
            </div>

            <form action="{{ route('petugas.tarik-tunai.store') }}" method="POST" x-data="{ nominal: {{ $saldoDigital ?? 0 }} }">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">
                            Amount (IDR) <span class="text-error">*</span>
                        </label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant font-bold">Rp</span>
                            <input type="number"
                                   name="nominal"
                                   x-model="nominal"
                                   required
                                   min="10000"
                                   step="10000"
                                   max="{{ $saldoDigital ?? 0 }}"
                                   class="w-full bg-surface-container-high border-none rounded-xl py-4 pl-12 pr-4 font-headline text-lg font-bold text-primary focus:ring-0 focus:bg-surface-container-highest transition-all"
                                   placeholder="0">
                            <div class="absolute left-0 top-0 w-0.5 h-0 bg-primary group-focus-within:h-full transition-all"></div>
                        </div>
                        <p class="text-xs text-on-surface-variant mt-2">Minimum penarikan: Rp 10.000</p>
                    </div>

                    <!-- Quick Amount Buttons -->
                    <div class="grid grid-cols-4 gap-3">
                        <button type="button"
                                @click="nominal = 50000"
                                class="px-3 py-3 text-sm font-semibold bg-surface-container-low text-on-surface-variant rounded-xl hover:bg-surface-container-high transition-colors">
                            Rp 50.000
                        </button>
                        <button type="button"
                                @click="nominal = 100000"
                                class="px-3 py-3 text-sm font-semibold bg-surface-container-low text-on-surface-variant rounded-xl hover:bg-surface-container-high transition-colors">
                            Rp 100.000
                        </button>
                        <button type="button"
                                @click="nominal = 200000"
                                class="px-3 py-3 text-sm font-semibold bg-surface-container-low text-on-surface-variant rounded-xl hover:bg-surface-container-high transition-colors">
                            Rp 200.000
                        </button>
                        <button type="button"
                                @click="nominal = {{ $saldoDigital ?? 0 }}"
                                class="px-3 py-3 text-sm font-semibold bg-primary text-on-primary rounded-xl hover:bg-primary-container transition-colors">
                            Max
                        </button>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant mb-2 uppercase">
                            Catatan <span class="text-outline">Opsional</span>
                        </label>
                        <textarea name="catatan"
                                  rows="3"
                                  class="w-full bg-surface-container-high border-none rounded-xl p-4 text-sm focus:bg-surface-container-highest focus:ring-0 transition-all resize-none"
                                  placeholder="Contoh: Untuk keperluan operasional kantor"></textarea>
                    </div>

                    <button type="submit"
                            class="w-full bg-primary text-on-primary font-headline font-bold py-4 rounded-xl shadow-lg shadow-primary/10 hover:shadow-primary/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">account_balance_wallet</span>
                        <span>Submit Request</span>
                    </button>
                    
                    <p class="text-[10px] text-center text-on-surface-variant uppercase tracking-widest font-bold">
                        Funds will be processed by finance department
                    </p>
                </div>
            </form>
        </div>

        <!-- Pending Requests -->
        <div class="lg:col-span-5 bg-surface-container-lowest rounded-xl shadow-sm">
            <div class="p-6 border-b border-surface-container">
                <h3 class="font-headline font-bold text-lg text-primary">Pending Requests</h3>
                <p class="text-xs text-on-surface-variant">Permintaan anda menunggu persetujuan</p>
            </div>
            <div class="p-4 space-y-3">
                @forelse($pendingRequests ?? [] as $request)
                    <div class="p-4 rounded-xl border-l-4 border-tertiary bg-tertiary/5">
                        <div class="flex justify-between items-start mb-2">
                            <p class="text-[10px] font-bold text-tertiary uppercase tracking-wider">Pending Approval</p>
                            <span class="text-[10px] text-on-surface-variant">{{ $request->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-lg font-headline font-bold text-on-surface">Rp {{ number_format($request->nominal, 0, ',', '.') }}</p>
                        @if($request->catatan)
                            <p class="text-xs text-on-surface-variant mt-2">{{ $request->catatan }}</p>
                        @endif
                        <div class="flex items-center gap-2 mt-3">
                            <span class="material-symbols-outlined text-sm text-tertiary">schedule</span>
                            <span class="text-xs text-on-surface-variant">Processing time: ~2-4 hours</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <span class="material-symbols-outlined text-4xl text-primary mb-3" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                        <p class="text-sm text-on-surface-variant">No pending requests</p>
                        <p class="text-xs text-outline mt-1">All your withdrawals have been processed</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-surface-container-low p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">trending_up</span>
                <p class="text-xs text-on-surface-variant font-medium">Total Settled (Month)</p>
            </div>
            <p class="text-2xl font-bold text-on-surface">Rp {{ number_format($totalSettled ?? 0, 0, ',', '.') }}</p>
        </div>
        
        <div class="bg-surface-container-low p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">pending_actions</span>
                <p class="text-xs text-on-surface-variant font-medium">Pending Requests</p>
            </div>
            <p class="text-2xl font-bold text-on-surface">{{ count($pendingRequests ?? []) }}</p>
        </div>
        
        <div class="bg-surface-container-low p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">schedule</span>
                <p class="text-xs text-on-surface-variant font-medium">Avg Processing</p>
            </div>
            <p class="text-2xl font-bold text-on-surface">2.4 Hrs</p>
        </div>
    </div>

    <!-- History -->
    <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm">
        <div class="p-6 border-b border-surface-container flex items-center justify-between">
            <h3 class="font-headline font-bold text-xl text-primary">Settlement History</h3>
            <div class="flex gap-2">
                <button class="px-4 py-2 text-sm font-medium bg-surface-container-high rounded-lg hover:bg-surface-container-highest transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">filter_list</span>
                    Filter
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-surface-container-high/50 text-on-surface-variant">
                        <th class="px-6 py-5 text-xs font-bold uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-5 text-xs font-bold uppercase tracking-wider">Reference ID</th>
                        <th class="px-6 py-5 text-xs font-bold uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-5 text-xs font-bold uppercase tracking-wider">Catatan</th>
                        <th class="px-6 py-5 text-xs font-bold uppercase tracking-wider">Status</th>
                        <th class="px-6 py-5 text-xs font-bold uppercase tracking-wider text-right">Approved By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10 bg-surface-container-lowest">
                    @forelse($riwayatPenarikan ?? [] as $riwayat)
                        <tr class="group hover:bg-surface transition-colors">
                            <td class="px-6 py-6">
                                <p class="font-semibold text-on-surface">{{ $riwayat->created_at->format('M d, Y') }}</p>
                                <p class="text-xs text-outline">{{ $riwayat->created_at->format('H:i') }}</p>
                            </td>
                            <td class="px-6 py-6">
                                <code class="bg-surface-container px-3 py-1 rounded text-xs text-primary font-medium">ST-{{ str_pad($riwayat->id, 5, '0', STR_PAD_LEFT) }}</code>
                            </td>
                            <td class="px-6 py-6 font-bold text-on-surface">Rp {{ number_format($riwayat->nominal, 0, ',', '.') }}</td>
                            <td class="px-6 py-6 text-sm text-on-surface-variant">
                                {{ $riwayat->catatan ?? '-' }}
                            </td>
                            <td class="px-6 py-6">
                                @if($riwayat->status === 'approved')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-fixed text-on-primary-fixed-variant text-xs font-bold">
                                        <span class="w-2 h-2 rounded-full bg-primary"></span>
                                        Approved
                                    </span>
                                @elseif($riwayat->status === 'rejected')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-error-container text-on-error-container text-xs font-bold">
                                        <span class="w-2 h-2 rounded-full bg-error"></span>
                                        Rejected
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-fixed text-on-secondary-fixed-variant text-xs font-bold">
                                        <span class="w-2 h-2 rounded-full bg-secondary"></span>
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-6 text-right">
                                <span class="text-sm font-medium text-on-surface-variant">
                                    {{ $riwayat->approver?->name ?? '-' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-4xl text-outline mb-3">history</span>
                                <p class="text-sm text-on-surface-variant">Belum ada riwayat penarikan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function tarikTunai() {
    return {
    }
}
</script>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection

@extends('layouts.app')

@section('header-title', 'Verifikasi Top Up')
@php $activeRole = 'admin'; @endphp

@section('content')
<div x-data="topUpAdmin()">
    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">Verifikasi Top Up</h2>
            <p class="text-on-surface-variant text-sm mt-1">Kelola permintaan top up saldo dari santri.</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-warning text-sm" style="font-variation-settings: 'FILL' 1;">pending</span>
                <p class="text-xs text-on-surface-variant font-medium">Menunggu Verifikasi</p>
            </div>
            <p class="text-3xl font-bold text-on-surface">{{ $pendingTopUps->total() }}</p>
        </div>

        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                <p class="text-xs text-on-surface-variant font-medium">Total Terverifikasi</p>
            </div>
            <p class="text-3xl font-bold text-primary">{{ $recentTopUps->where('status', 'approved')->count() }}</p>
        </div>

        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-error text-sm" style="font-variation-settings: 'FILL' 1;">cancel</span>
                <p class="text-xs text-on-surface-variant font-medium">Total Ditolak</p>
            </div>
            <p class="text-3xl font-bold text-error">{{ $recentTopUps->where('status', 'rejected')->count() }}</p>
        </div>
    </div>

    <!-- Pending Top Ups -->
    <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm mb-8">
        <div class="p-6 border-b border-surface-container flex items-center justify-between">
            <h3 class="font-headline font-bold text-xl text-warning flex items-center gap-2">
                <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">pending</span>
                <span>Menunggu Verifikasi</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-high/50 text-[10px] uppercase font-black text-on-surface-variant tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Santri</th>
                        <th class="px-6 py-4">Nominal</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Bukti</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-outline-variant/10">
                    @forelse($pendingTopUps as $topUp)
                        <tr class="hover:bg-surface transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-warning/10 flex items-center justify-center text-warning font-bold overflow-hidden flex-shrink-0">
                                        @if($topUp->santri->foto)
                                            <img src="{{ Storage::url($topUp->santri->foto) }}" alt="{{ $topUp->santri->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($topUp->santri->name, 0, 2) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-on-surface truncate">{{ $topUp->santri->name }}</div>
                                        <div class="text-xs text-on-surface-variant truncate">NIS: {{ $topUp->santri->nis ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-headline font-bold text-on-surface">Rp {{ number_format($topUp->nominal, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant text-xs">{{ $topUp->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-4 text-center">
                                <button @click="viewProof('{{ Storage::url($topUp->bukti_pembayaran) }}', {{ json_encode([
                                    'id' => $topUp->id,
                                    'santri_name' => $topUp->santri->name,
                                    'santri_nis' => $topUp->santri->nis ?? '-',
                                    'nominal' => $topUp->nominal,
                                    'created_at' => $topUp->created_at,
                                    'status' => $topUp->status
                                ]) }})" class="px-4 py-1.5 rounded-lg text-primary bg-primary-fixed-dim/20 hover:bg-primary-fixed-dim/40 font-semibold transition-all">
                                    Lihat Bukti
                                </button>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="viewDetail({{ $topUp->id }})" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Detail">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                    </button>
                                    <button @click="approveTopUp({{ $topUp->id }})" class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Verifikasi">
                                        <span class="material-symbols-outlined text-sm">check_circle</span>
                                    </button>
                                    <button @click="rejectTopUp({{ $topUp->id }})" class="p-2 text-error hover:bg-error/10 rounded-lg transition-colors" title="Tolak">
                                        <span class="material-symbols-outlined text-sm">cancel</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-4xl text-outline mb-3">check_circle</span>
                                <p class="text-sm text-on-surface-variant">Tidak ada permintaan top up pending</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pendingTopUps->hasPages())
            <div class="p-6 border-t border-surface-container">
                {{ $pendingTopUps->links() }}
            </div>
        @endif
    </div>

    <!-- Recent Top Ups -->
    <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm">
        <div class="p-6 border-b border-surface-container flex items-center justify-between">
            <h3 class="font-headline font-bold text-xl text-primary">Riwayat Top Up</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-high/50 text-[10px] uppercase font-black text-on-surface-variant tracking-widest">
                    <tr>
                        <th class="px-6 py-4">Santri</th>
                        <th class="px-6 py-4">Nominal</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Bukti</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Admin</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-outline-variant/10">
                    @forelse($recentTopUps as $topUp)
                        <tr class="hover:bg-surface transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full {{ $topUp->status === 'approved' ? 'bg-primary/10' : 'bg-error/10' }} flex items-center justify-center font-bold overflow-hidden flex-shrink-0">
                                        @if($topUp->santri->foto)
                                            <img src="{{ Storage::url($topUp->santri->foto) }}" alt="{{ $topUp->santri->name }}" class="w-full h-full object-cover">
                                        @else
                                            {{ substr($topUp->santri->name, 0, 2) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-on-surface truncate">{{ $topUp->santri->name }}</div>
                                        <div class="text-xs text-on-surface-variant truncate">NIS: {{ $topUp->santri->nis ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-headline font-bold text-on-surface">Rp {{ number_format($topUp->nominal, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant text-xs">{{ $topUp->updated_at->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-4 text-center">
                                <button @click="viewProof('{{ Storage::url($topUp->bukti_pembayaran) }}', {{ json_encode([
                                    'id' => $topUp->id,
                                    'santri_name' => $topUp->santri->name,
                                    'santri_nis' => $topUp->santri->nis ?? '-',
                                    'nominal' => $topUp->nominal,
                                    'created_at' => $topUp->created_at,
                                    'status' => $topUp->status
                                ]) }})" class="px-4 py-1.5 rounded-lg text-primary bg-primary-fixed-dim/20 hover:bg-primary-fixed-dim/40 font-semibold transition-all">
                                    Lihat Bukti
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold px-3 py-1 rounded-full {{ $topUp->status === 'approved' ? 'bg-primary/10 text-primary' : 'bg-error/10 text-error' }}">
                                    {{ $topUp->status_text }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant text-xs">{{ $topUp->admin?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <span class="material-symbols-outlined text-4xl text-outline mb-3">history</span>
                                <p class="text-sm text-on-surface-variant">Belum ada riwayat top up</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Proof Image Modal -->
    <div x-show="showProofModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-on-surface/40 transition-opacity" @click="showProofModal = false"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="bg-surface-container-lowest w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col"
                 @click.stop>
                <!-- Header -->
                <div class="px-8 py-6 flex items-center justify-between bg-surface-container-low/20">
                    <h3 class="text-2xl font-headline font-extrabold text-primary">Verifikasi Bukti Transfer</h3>
                    <button @click="showProofModal = false" class="p-2 rounded-full hover:bg-surface-container-high transition-colors">
                        <span class="material-symbols-outlined text-on-surface">close</span>
                    </button>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto px-8 py-6">
                    <!-- Info Section -->
                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div>
                            <p class="text-xs uppercase tracking-widest font-bold text-on-surface-variant mb-1">Informasi Santri</p>
                            <p class="text-lg font-headline font-bold text-on-surface" x-text="proofData?.santri_name || '-'"></p>
                            <p class="text-sm font-medium text-primary tracking-tight" x-text="'NIS: ' + (proofData?.santri_nis || '-')"></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest font-bold text-on-surface-variant mb-1">Nominal Top-Up</p>
                            <p class="text-2xl font-headline font-black text-primary" x-text="'Rp ' + formatNumber(proofData?.nominal || 0)"></p>
                            <p class="text-xs text-on-surface-variant">Metode: Bank Transfer</p>
                        </div>
                    </div>

                    <!-- Proof Image -->
                    <div class="mb-6" x-data="{ zoomed: false }">
                        <p class="text-xs uppercase tracking-widest font-bold text-on-surface-variant mb-3">Bukti Pembayaran</p>
                        <div class="relative group aspect-[3/4] md:aspect-[16/9] w-full rounded-xl overflow-hidden bg-surface-container-high border border-outline-variant/20 shadow-inner">
                            <img :src="proofImageUrl"
                                 class="w-full h-full object-contain p-4 transition-transform duration-300"
                                 :class="zoomed ? 'scale-150 cursor-zoom-out' : 'scale-100 cursor-zoom-in'"
                                 @click="zoomed = !zoomed"
                                 alt="Bukti Transfer">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center pointer-events-none">
                                <span class="material-symbols-outlined text-white opacity-0 group-hover:opacity-100 scale-150 transition-all"
                                      :class="zoomed ? 'opacity-0' : ''">zoom_in</span>
                            </div>
                        </div>
                        <p class="text-center text-xs text-on-surface-variant mt-3 italic">Klik gambar untuk memperbesar</p>
                    </div>

                    <!-- Transaction Details -->
                    <div class="bg-surface-container-low/50 p-4 rounded-xl space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-on-surface-variant">ID Transaksi</span>
                            <span class="font-mono font-medium text-on-surface" x-text="'TXN-' + (proofData?.id || '-')"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-on-surface-variant">Waktu Upload</span>
                            <span class="font-medium text-on-surface" x-text="formatDate(proofData?.created_at)"></span>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div x-show="proofData?.status === 'pending'" class="px-8 py-6 bg-surface-container-low/30 flex gap-4">
                    <button @click="rejectTopUp(proofData?.id)"
                            class="flex-1 py-3.5 px-6 rounded-xl text-error bg-error-container/20 hover:bg-error-container/40 font-bold transition-all active:scale-95 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-lg">cancel</span>
                        Tolak
                    </button>
                    <button @click="approveTopUp(proofData?.id)"
                            class="flex-[2] py-3.5 px-6 rounded-xl bg-primary text-on-primary hover:bg-primary-container font-bold shadow-lg shadow-primary/10 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-lg">check_circle</span>
                        Konfirmasi Top-Up
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div x-show="showDetailModal" x-cloak class="fixed inset-0 overflow-y-auto z-50">
        <div class="fixed inset-0 bg-black/50 transition-opacity" @click="showDetailModal = false"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-surface rounded-2xl shadow-2xl max-w-md w-full p-6 animate-scale-in" @click.stop>
                <div x-show="loading" class="flex items-center justify-center py-12">
                    <span class="material-symbols-outlined text-primary animate-spin">progress_activity</span>
                </div>

                <div x-show="!loading && selectedTopUp" class="space-y-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-headline font-bold text-xl text-on-surface">Detail Top Up</h3>
                        <button @click="showDetailModal = false" class="p-2 hover:bg-surface-container-high rounded-full transition-colors">
                            <span class="material-symbols-outlined text-on-surface-variant">close</span>
                        </button>
                    </div>

                    <div class="p-4 bg-surface-container-lowest rounded-xl space-y-3">
                        <div class="flex justify-between">
                            <span class="text-xs text-on-surface-variant">Santri</span>
                            <span class="text-sm font-bold text-on-surface" x-text="selectedTopUp.santri?.name"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-on-surface-variant">NIS</span>
                            <span class="text-sm text-on-surface" x-text="selectedTopUp.santri?.nis || '-'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-on-surface-variant">Nominal</span>
                            <span class="text-sm font-bold text-primary" x-text="'Rp ' + formatNumber(selectedTopUp.nominal)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-on-surface-variant">Tanggal</span>
                            <span class="text-sm text-on-surface" x-text="formatDate(selectedTopUp.created_at)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-on-surface-variant">Status</span>
                            <span class="text-xs font-bold px-3 py-1 rounded-full" :class="statusClass(selectedTopUp.status)" x-text="statusText(selectedTopUp.status)"></span>
                        </div>
                    </div>

                    <button @click="viewProof(selectedTopUp.bukti_pembayaran)" class="w-full bg-surface-container-high text-on-surface font-bold py-3 px-4 rounded-xl hover:bg-surface-container transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">image</span>
                        <span>Lihat Bukti Pembayaran</span>
                    </button>

                    <div x-show="selectedTopUp.status === 'pending'" class="flex gap-3">
                        <button @click="approveTopUp(selectedTopUp.id)" class="flex-1 bg-primary text-on-primary font-bold py-3 px-4 rounded-xl hover:shadow-lg transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            <span>Verifikasi</span>
                        </button>
                        <button @click="rejectTopUp(selectedTopUp.id)" class="flex-1 bg-error-container text-on-error-container font-bold py-3 px-4 rounded-xl hover:bg-error/10 transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-sm">cancel</span>
                            <span>Tolak</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="showRejectModal = false"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-surface rounded-2xl shadow-2xl max-w-md w-full p-6 animate-scale-in" @click.stop>
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-error-container rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-error text-3xl">cancel</span>
                    </div>
                    <h3 class="font-headline font-bold text-xl text-on-surface">Tolak Top Up</h3>
                    <p class="text-sm text-on-surface-variant mt-1">Berikan alasan penolakan (opsional)</p>
                </div>

                <div class="mb-6">
                    <textarea x-model="rejectNote" rows="3" class="input-field w-full" placeholder="Alasan penolakan..."></textarea>
                </div>

                <div class="flex gap-3">
                    <button @click="showRejectModal = false" class="flex-1 bg-surface-container-high text-on-surface font-bold py-3 px-4 rounded-xl hover:bg-surface-container transition-all">
                        Batal
                    </button>
                    <button @click="confirmReject" class="flex-1 bg-error text-on-error font-bold py-3 px-4 rounded-xl hover:shadow-lg transition-all">
                        Tolak Top Up
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>

<script>
function topUpAdmin() {
    return {
        showProofModal: false,
        showDetailModal: false,
        showRejectModal: false,
        loading: false,
        proofImageUrl: '',
        proofData: null,
        selectedTopUp: null,
        rejectTopUpId: null,
        rejectNote: '',

        viewProof(url, topUpData = null) {
            this.proofImageUrl = url;
            this.proofData = topUpData;
            this.showProofModal = true;
        },

        async viewDetail(id) {
            this.loading = true;
            this.showDetailModal = true;
            try {
                const response = await fetch(`/admin/topup/${id}/modal-data`, {
                    credentials: 'same-origin'
                });
                const data = await response.json();
                this.selectedTopUp = data.top_up;
            } catch (error) {
                console.error('Error loading top-up data:', error);
                alert('Gagal memuat data top up');
            } finally {
                this.loading = false;
            }
        },

        async approveTopUp(id) {
            if (!confirm('Verifikasi top-up ini? Saldo santri akan bertambah.')) return;
            
            try {
                const response = await fetch(`/admin/topup/${id}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal memverifikasi top-up');
                }
            } catch (error) {
                console.error('Error approving top-up:', error);
                alert('Terjadi kesalahan saat memverifikasi top-up');
            }
        },

        async approveTopUp(id) {
            if (!confirm('Verifikasi top-up ini? Saldo santri akan bertambah.')) return;
            
            try {
                const response = await fetch(`/admin/topup/${id}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal memverifikasi top-up');
                }
            } catch (error) {
                console.error('Error approving top-up:', error);
                alert('Terjadi kesalahan saat memverifikasi top-up');
            }
        },

        rejectTopUp(id) {
            this.rejectTopUpId = id;
            this.rejectNote = '';
            this.showRejectModal = true;
            this.showDetailModal = false;
        },

        async confirmReject() {
            try {
                const response = await fetch(`/admin/topup/${this.rejectTopUpId}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        admin_note: this.rejectNote
                    })
                });
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal menolak top-up');
                }
            } catch (error) {
                console.error('Error rejecting top-up:', error);
                alert('Terjadi kesalahan saat menolak top-up');
            }
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        },

        statusClass(status) {
            return status === 'approved' ? 'bg-primary/10 text-primary' : 
                   status === 'rejected' ? 'bg-error/10 text-error' : 
                   'bg-warning/10 text-warning';
        },

        statusText(status) {
            return status === 'approved' ? 'Terverifikasi' : 
                   status === 'rejected' ? 'Ditolak' : 
                   'Menunggu Verifikasi';
        }
    }
}
</script>
@endsection

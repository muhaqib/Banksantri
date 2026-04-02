@extends('layouts.app')

@section('header-title', 'Riwayat Transaksi')

@section('content')
<div x-data="riwayatTransaksi()">
    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                <select x-model="filterPeriode" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    <option value="hari">Hari Ini</option>
                    <option value="minggu">Minggu Ini</option>
                    <option value="bulan">Bulan Ini</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                <select x-model="filterKategori" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    <option value="">Semua Kategori</option>
                    <option value="kantin">Kantin</option>
                    <option value="koperasi">Koperasi</option>
                    <option value="laundry">Laundry</option>
                    <option value="fotokopi">Fotokopi</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-2">Cari Santri</label>
                <input type="text" 
                       x-model="searchQuery"
                       placeholder="Nama atau NIS..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
            </div>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-500 mb-1">Total Transaksi</p>
            <p class="text-3xl font-bold text-gray-900">{{ count($transaksiList ?? []) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-500 mb-1">Total Nominal Keluar</p>
            <p class="text-3xl font-bold text-red-600">Rp {{ number_format($totalKeluar ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-500 mb-1">Total Nominal Masuk</p>
            <p class="text-3xl font-bold text-green-600">Rp {{ number_format($totalMasuk ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Transaction List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Daftar Transaksi</h3>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($transaksiList ?? [] as $transaksi)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4 flex-1">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center
                                {{ $transaksi->jenis === 'masuk' ? 'bg-green-100' : 'bg-red-100' }}">
                                <svg class="w-6 h-6 {{ $transaksi->jenis === 'masuk' ? 'text-green-600' : 'text-red-600' }}" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="{{ $transaksi->jenis === 'masuk' ? 'M7 11l5-5m0 0l5 5m-5-5v12' : 'M17 13l-5 5m0 0l-5-5m5 5V6' }}">
                                    </path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <h4 class="font-semibold text-gray-900">{{ $transaksi->santri_name ?? 'Santri' }}</h4>
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full 
                                        {{ $transaksi->kategori === 'kantin' ? 'bg-orange-100 text-orange-600' : 
                                          $transaksi->kategori === 'koperasi' ? 'bg-blue-100 text-blue-600' :
                                          $transaksi->kategori === 'laundry' ? 'bg-purple-100 text-purple-600' :
                                          'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($transaksi->kategori) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $transaksi->keterangan ?? '-' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $transaksi->created_at->format('d M Y, H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold {{ $transaksi->jenis === 'masuk' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaksi->jenis === 'masuk' ? '+' : '-' }} Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Saldo: Rp {{ number_format($transaksi->saldo_sebelum ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p>Belum ada transaksi</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function riwayatTransaksi() {
    return {
        filterPeriode: 'bulan',
        filterKategori: '',
        searchQuery: ''
    }
}
</script>
@endsection

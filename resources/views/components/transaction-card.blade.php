@props([
    'transaction' => null,
    'type' => 'default' // default, compact
])

@if($type === 'compact')
    <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-full flex items-center justify-center
                {{ $transaction->jenis === 'masuk' ? 'bg-green-100' : 'bg-red-100' }}">
                <svg class="w-5 h-5 {{ $transaction->jenis === 'masuk' ? 'text-green-600' : 'text-red-600' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="{{ $transaction->jenis === 'masuk' ? 'M7 11l5-5m0 0l5 5m-5-5v12' : 'M17 13l-5 5m0 0l-5-5m5 5V6' }}">
                    </path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-800">{{ $transaction->kategori ?? 'Transaksi' }}</p>
                <p class="text-xs text-gray-500">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
            </div>
        </div>
        <span class="text-sm font-bold {{ $transaction->jenis === 'masuk' ? 'text-green-600' : 'text-red-600' }}">
            {{ $transaction->jenis === 'masuk' ? '+' : '-' }} Rp {{ number_format($transaction->nominal, 0, ',', '.') }}
        </span>
    </div>
@else
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center
                        {{ $transaction->jenis === 'masuk' ? 'bg-green-100' : 'bg-red-100' }}">
                        <svg class="w-6 h-6 {{ $transaction->jenis === 'masuk' ? 'text-green-600' : 'text-red-600' }}" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="{{ $transaction->jenis === 'masuk' ? 'M7 11l5-5m0 0l5 5m-5-5v12' : 'M17 13l-5 5m0 0l-5-5m5 5V6' }}">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800">{{ $transaction->kategori ?? 'Transaksi' }}</h4>
                        <p class="text-sm text-gray-500">{{ $transaction->keterangan ?? '-' }}</p>
                    </div>
                </div>
                <span class="text-lg font-bold {{ $transaction->jenis === 'masuk' ? 'text-green-600' : 'text-red-600' }}">
                    {{ $transaction->jenis === 'masuk' ? '+' : '-' }} Rp {{ number_format($transaction->nominal, 0, ',', '.') }}
                </span>
            </div>
            
            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ $transaction->created_at->format('d M Y, H:i') }}
                </div>
                @if($transaction->petugas_name ?? null)
                    <span>Petugas: {{ $transaction->petugas_name }}</span>
                @endif
            </div>
        </div>
    </div>
@endif

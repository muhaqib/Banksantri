@extends('layouts.santri')

@section('title', 'Riwayat Transaksi')

@section('content')
<div x-data="riwayatSantri({{ json_encode([
    'currentFilter' => $currentFilter ?? 'all',
    'currentPeriode' => $currentPeriode ?? 'bulan',
    'currentKategori' => $currentKategori ?? 'all'
]) }})" class="pb-24">
    <!-- Header -->
    <header class="w-full pt-12 pb-6 px-5 sticky top-0 z-40 bg-surface/80 backdrop-blur-md">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('santri.home') }}" class="p-2 hover:bg-surface-container-low rounded-full transition-colors">
                    <span class="material-symbols-outlined text-primary">arrow_back</span>
                </a>
                <h1 class="font-headline font-bold text-xl text-primary">Riwayat Transaksi</h1>
            </div>
            <button @click="showFilter = !showFilter" class="p-2 hover:bg-surface-container-low rounded-full transition-colors">
                <span class="material-symbols-outlined text-primary">filter_list</span>
            </button>
        </div>
    </header>

    <main class="px-5">
        <!-- Monthly Chart with Filter -->
        <section class="mt-4 mb-6">
            <!-- Month Filter Dropdown -->
            <div class="relative mb-4">
                <button @click="showMonthFilter = !showMonthFilter" 
                        class="w-full flex items-center justify-between p-4 bg-surface-container-lowest rounded-2xl">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">calendar_month</span>
                        <p class="text-on-surface font-semibold text-sm" x-text="selectedMonthLabel"></p>
                    </div>
                    <span class="material-symbols-outlined text-on-surface-variant transition-transform"
                          :class="{ 'rotate-180': showMonthFilter }">expand_more</span>
                </button>
                
                <!-- Month Dropdown -->
                <div x-show="showMonthFilter" 
                     x-cloak 
                     @click.away="showMonthFilter = false"
                     class="absolute z-50 w-full mt-2 bg-surface-container-lowest rounded-2xl shadow-xl p-3">
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="(month, index) in months" :key="index">
                            <button @click="selectMonth(index + 1)"
                                    class="px-3 py-2 rounded-lg text-xs font-medium text-center transition-colors"
                                    :class="selectedMonth === (index + 1) ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'"
                                    x-text="month">
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Line Chart -->
            <div class="bg-surface-container-lowest p-4 rounded-2xl overflow-x-auto">
                <h3 class="text-xs font-semibold text-on-surface-variant mb-3 uppercase">Grafik Transaksi Harian</h3>
                <div class="relative h-64 min-w-[600px]">
                    <canvas id="monthlyChart"></canvas>
                </div>
                
                <!-- Summary Totals Below Chart -->
                <div class="flex justify-between gap-4 mt-4 pt-4 border-t border-surface-container">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-6 h-6 rounded-full bg-error-container flex items-center justify-center">
                                <span class="material-symbols-outlined text-error text-sm">trending_down</span>
                            </div>
                            <span class="text-xs font-medium text-on-surface-variant">Total Pengeluaran</span>
                        </div>
                        <p class="text-error font-headline font-bold text-sm">Rp <span x-text="formatNumber(pengeluaran)"></span></p>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-6 h-6 rounded-full bg-primary-fixed flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary text-sm">trending_up</span>
                            </div>
                            <span class="text-xs font-medium text-on-surface-variant">Total Pemasukan</span>
                        </div>
                        <p class="text-primary font-headline font-bold text-sm">Rp <span x-text="formatNumber(pemasukan)"></span></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filter Chips -->
        <section class="flex gap-2 overflow-x-auto pb-2 mb-4 no-scrollbar">
            <a :href="getFilterUrl('all')" 
               class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors"
               :class="filter === 'all' ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'">
                Semua
            </a>
            <a :href="getFilterUrl('keluar')" 
               class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors"
               :class="filter === 'keluar' ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'">
                Pengeluaran
            </a>
            <a :href="getFilterUrl('masuk')" 
               class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors"
               :class="filter === 'masuk' ? 'bg-primary text-white' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high'">
                Pemasukan
            </a>
        </section>

        <!-- Period Filter Dropdown -->
        <div x-show="showFilter" x-cloak class="mb-4 p-3 bg-surface-container-low rounded-xl">
            <p class="text-xs font-semibold text-on-surface-variant mb-2 uppercase">Periode</p>
            <div class="grid grid-cols-2 gap-2">
                <a :href="getPeriodeUrl('hari')" 
                   class="px-3 py-2 rounded-lg text-xs font-medium text-center transition-colors"
                   :class="periode === 'hari' ? 'bg-primary text-white' : 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container-high'">
                    Hari Ini
                </a>
                <a :href="getPeriodeUrl('minggu')" 
                   class="px-3 py-2 rounded-lg text-xs font-medium text-center transition-colors"
                   :class="periode === 'minggu' ? 'bg-primary text-white' : 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container-high'">
                    Minggu Ini
                </a>
                <a :href="getPeriodeUrl('bulan')" 
                   class="px-3 py-2 rounded-lg text-xs font-medium text-center transition-colors"
                   :class="periode === 'bulan' ? 'bg-primary text-white' : 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container-high'">
                    Bulan Ini
                </a>
                <a :href="getPeriodeUrl('tahun')" 
                   class="px-3 py-2 rounded-lg text-xs font-medium text-center transition-colors"
                   :class="periode === 'tahun' ? 'bg-primary text-white' : 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container-high'">
                    Tahun Ini
                </a>
            </div>
        </div>

        <!-- Transaction List -->
        <div class="space-y-6">
            @php
                $groupedTransactions = $transaksiList->groupBy(function($item) {
                    $today = \Carbon\Carbon::today();
                    $yesterday = \Carbon\Carbon::yesterday();
                    
                    if ($item->created_at->isSameDay($today)) {
                        return 'Hari Ini';
                    } elseif ($item->created_at->isSameDay($yesterday)) {
                        return 'Kemarin';
                    } else {
                        return $item->created_at->locale('id')->isoFormat('DD MMM YYYY');
                    }
                });
            @endphp

            @forelse($groupedTransactions as $groupName => $transactions)
                <section>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-outline mb-3">{{ $groupName }}</h3>
                    <div class="space-y-3">
                        @foreach($transactions as $transaksi)
                            <!-- Transaction Item -->
                            <div class="flex items-center justify-between p-4 bg-surface-container-lowest rounded-2xl {{ $transaksi->jenis === 'masuk' ? 'border-l-4 border-primary/20' : '' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl {{ $transaksi->jenis === 'masuk' ? 'bg-primary-fixed' : 'bg-surface-container' }} flex items-center justify-center">
                                        <span class="material-symbols-outlined {{ $transaksi->jenis === 'masuk' ? 'text-primary' : 'text-on-surface-variant' }}">
                                            @if($transaksi->kategori === 'top_up' || $transaksi->kategori === 'tarik uang')
                                                account_balance_wallet
                                            @elseif($transaksi->kategori === 'kantin')
                                                restaurant_menu
                                            @elseif($transaksi->kategori === 'koperasi')
                                                shopping_bag
                                            @elseif($transaksi->kategori === 'laundry')
                                                local_laundry_service
                                            @elseif($transaksi->kategori === 'fotokopi')
                                                print
                                            @elseif($transaksi->kategori === 'syirkah')
                                                store
                                            @elseif($transaksi->kategori === 'beli kitab')
                                                menu_book
                                            @elseif($transaksi->kategori === 'mart')
                                                storefront
                                            @else
                                                {{ $transaksi->jenis === 'masuk' ? 'trending_up' : 'trending_down' }}
                                            @endif
                                        </span>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-on-surface text-sm">{{ ucfirst($transaksi->kategori ?? 'Transaksi') }}</h4>
                                        <p class="text-xs text-on-surface-variant mt-0.5">
                                            {{ $transaksi->created_at->format('H:i') }} • {{ $transaksi->jenis === 'masuk' ? 'Pemasukan' : 'Pengeluaran' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold {{ $transaksi->jenis === 'masuk' ? 'text-primary' : 'text-error' }}">
                                        {{ $transaksi->jenis === 'masuk' ? '+' : '-' }} Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}
                                    </p>
                                    <p class="text-[10px] text-primary font-medium bg-primary-fixed/30 px-2 py-0.5 rounded-full inline-block mt-1">
                                        Selesai
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="text-center py-16">
                    <span class="material-symbols-outlined text-6xl text-outline mb-3">receipt_long</span>
                    <p class="text-sm text-on-surface-variant">Belum ada transaksi</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($transaksiList->hasPages())
            <div class="mt-6">
                {{ $transaksiList->links() }}
            </div>
        @endif
    </main>

    <!-- Bottom Navigation Bar -->
    <x-santri.bottom-nav />
</div>

<script>
function riwayatSantri(config) {
    return {
        filter: config.currentFilter,
        periode: config.currentPeriode,
        kategori: config.currentKategori,
        showFilter: false,
        showMonthFilter: false,
        selectedMonth: new Date().getMonth() + 1,
        selectedYear: new Date().getFullYear(),
        months: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        chart: null,
        chartData: {
            labels: [],
            pemasukan: [],
            pengeluaran: []
        },
        pemasukan: 0,
        pengeluaran: 0,

        get selectedMonthLabel() {
            const monthIndex = this.selectedMonth - 1;
            const monthName = this.months[monthIndex];
            return `${monthName} ${this.selectedYear}`;
        },

        getFilterUrl(type) {
            const url = new URL(window.location.href);
            url.searchParams.set('jenis', type);
            if (type === 'all') url.searchParams.delete('jenis');
            return url.toString();
        },

        getFilterUrlWithKategori(kat) {
            const url = new URL(window.location.href);
            if (this.kategori === kat) {
                url.searchParams.delete('kategori');
            } else {
                url.searchParams.set('kategori', kat);
            }
            url.searchParams.delete('jenis');
            return url.toString();
        },

        getPeriodeUrl(period) {
            const url = new URL(window.location.href);
            url.searchParams.set('periode', period);
            return url.toString();
        },

        async selectMonth(month) {
            this.selectedMonth = month;
            this.showMonthFilter = false;
            
            // Reload both chart and transaction list
            await this.loadMonthlyData();
            this.reloadTransactionList();
        },

        reloadTransactionList() {
            const url = new URL(window.location.href);
            url.searchParams.set('month', this.selectedMonth);
            url.searchParams.set('year', this.selectedYear);
            url.searchParams.set('periode', 'bulan');
            
            // Reload the page with new parameters
            window.location.href = url.toString();
        },

        async loadMonthlyData() {
            try {
                const url = new URL(window.location.href);
                url.searchParams.set('month', this.selectedMonth);
                url.searchParams.set('year', this.selectedYear);

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.pemasukan = data.pemasukan || 0;
                    this.pengeluaran = data.pengeluaran || 0;
                    this.chartData = data.chartData || { labels: [], pemasukan: [], pengeluaran: [] };
                    
                    console.log('Chart data received:', this.chartData);
                    console.log('Pemasukan data:', this.chartData.pemasukan);
                    console.log('Pengeluaran data:', this.chartData.pengeluaran);
                    
                    this.$nextTick(() => {
                        this.updateChart();
                    });
                }
            } catch (error) {
                console.error('Error loading monthly data:', error);
            }
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        },

        initChart() {
            // Check if Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded!');
                return;
            }

            const ctx = document.getElementById('monthlyChart');
            if (!ctx) {
                console.error('Canvas element not found');
                return;
            }

            // Destroy existing chart if exists
            if (this.chart) {
                this.chart.destroy();
            }

            console.log('Initializing chart with data:', this.chartData);

            this.chart = new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: this.chartData.labels,
                    datasets: [
                        {
                            label: 'Pemasukan',
                            data: this.chartData.pemasukan,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointHoverBackgroundColor: '#10b981',
                            pointHoverBorderColor: '#ffffff',
                            pointHoverBorderWidth: 2
                        },
                        {
                            label: 'Pengeluaran',
                            data: this.chartData.pengeluaran,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#ef4444',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointHoverBackgroundColor: '#ef4444',
                            pointHoverBorderColor: '#ffffff',
                            pointHoverBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    aspectRatio: 2,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                padding: 15,
                                font: {
                                    size: 12,
                                    weight: '600'
                                },
                                color: '#374151'
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#111827',
                            bodyColor: '#374151',
                            borderColor: '#e5e7eb',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 9
                                },
                                color: '#6b7280',
                                maxRotation: 0,
                                minRotation: 0,
                                autoSkip: false,
                                padding: 2,
                                callback: function(value, index, values) {
                                    // Show all date labels
                                    return this.getLabelForValue(value);
                                }
                            },
                            offset: true
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                color: '#6b7280',
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return (value / 1000000).toFixed(1) + 'jt';
                                    } else if (value >= 1000) {
                                        return (value / 1000) + 'k';
                                    }
                                    return value;
                                }
                            },
                            border: {
                                display: false
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    },
                    animation: {
                        duration: 800,
                        easing: 'easeInOutQuart'
                    }
                }
            });

            console.log('Chart initialized successfully');
        },

        updateChart() {
            if (!this.chart) {
                console.log('Chart not initialized yet, calling initChart()');
                this.initChart();
                return;
            }

            console.log('Updating chart with data:', {
                labels: this.chartData.labels,
                pemasukan: this.chartData.pemasukan,
                pengeluaran: this.chartData.pengeluaran
            });

            // Update labels
            this.chart.data.labels = this.chartData.labels;
            
            // Update datasets
            if (this.chart.data.datasets && this.chart.data.datasets.length >= 2) {
                this.chart.data.datasets[0].data = [...this.chartData.pemasukan];
                this.chart.data.datasets[1].data = [...this.chartData.pengeluaran];
            }
            
            // Update chart with animation
            this.chart.update('active');
            
            console.log('Chart updated successfully');
        },

        init() {
            // Set initial month/year from URL params if available
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('month')) {
                this.selectedMonth = parseInt(urlParams.get('month'));
            }
            if (urlParams.has('year')) {
                this.selectedYear = parseInt(urlParams.get('year'));
            }

            // Wait for Chart.js to load, then initialize
            this.waitForChartAndInit();
        },

        waitForChartAndInit() {
            let attempts = 0;
            const maxAttempts = 50; // 5 seconds max
            const interval = setInterval(() => {
                attempts++;

                if (typeof Chart !== 'undefined') {
                    clearInterval(interval);
                    console.log('Chart.js loaded, loading chart data first...');
                    
                    // Load data first, then initialize chart
                    this.$nextTick(async () => {
                        await this.loadMonthlyData();
                    });
                } else if (attempts >= maxAttempts) {
                    clearInterval(interval);
                    console.error('Chart.js failed to load after 5 seconds');
                }
            }, 100);
        }
    }
}
</script>

<style>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
<script>
window.addEventListener('load', function() {
    console.log('Page loaded, checking for Chart.js...');
    setTimeout(() => {
        if (typeof Chart !== 'undefined') {
            console.log('Chart.js is available!');
        } else {
            console.log('Chart.js not loaded, will use fallback');
        }
    }, 500);
});
</script>
@endpush

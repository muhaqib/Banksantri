@extends('components.sidebar', ['activeRole' => 'admin'])

@section('title', $kamarLabel)

@section('content')
@php $activeRole = 'admin'; @endphp

<div class="max-w-6xl mx-auto" x-data="kamarPage()">
    <!-- Header with Back Button -->
    <div class="mb-8 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.kamar.index') }}"
               class="p-2 hover:bg-surface-container-low rounded-lg transition-colors">
                <span class="material-symbols-outlined text-on-surface">arrow_back</span>
            </a>
            <div>
                <h1 class="text-3xl font-headline font-bold text-on-surface">{{ $kamarLabel }}</h1>
                <p class="text-on-surface-variant mt-2">Daftar anggota kamar</p>
            </div>
        </div>
        <button @click="openModal()"
                class="bg-primary-container text-on-primary-container px-6 py-3 rounded-xl font-body font-bold hover:opacity-90 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined">person_add</span>
            <span>Tambah Anggota</span>
        </button>
    </div>

    <!-- Members Table -->
    <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/10 overflow-hidden">
        @if($kamarSantris->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-surface-container-low border-b border-outline-variant/10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-headline font-bold text-on-surface-variant uppercase tracking-wider">No</th>
                        <th class="px-6 py-4 text-left text-xs font-headline font-bold text-on-surface-variant uppercase tracking-wider">Foto</th>
                        <th class="px-6 py-4 text-left text-xs font-headline font-bold text-on-surface-variant uppercase tracking-wider">Nama Santri</th>
                        <th class="px-6 py-4 text-left text-xs font-headline font-bold text-on-surface-variant uppercase tracking-wider">NIS</th>
                        <th class="px-6 py-4 text-left text-xs font-headline font-bold text-on-surface-variant uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-4 text-center text-xs font-headline font-bold text-on-surface-variant uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    @foreach($kamarSantris as $index => $kamarSantri)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-6 py-4 text-sm text-on-surface-variant">{{ $kamarSantris->firstItem() + $index }}</td>
                        <td class="px-6 py-4">
                            <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center overflow-hidden">
                                @if($kamarSantri->user->foto)
                                    <img src="{{ Storage::url($kamarSantri->user->foto) }}"
                                         alt="{{ $kamarSantri->user->name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <span class="material-symbols-outlined text-primary">account_circle</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-body font-medium text-on-surface">{{ $kamarSantri->user->name }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-on-surface-variant">{{ $kamarSantri->user->nis ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-on-surface-variant">{{ $kamarSantri->user->kelas ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <form action="{{ route('admin.kamar.destroy', $kamarSantri->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus {{ $kamarSantri->user->name }} dari {{ $kamarLabel }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-error hover:text-error-container transition-colors p-2 hover:bg-error/10 rounded-lg">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-outline-variant/10">
            {{ $kamarSantris->links() }}
        </div>
        @else
        <div class="py-16 text-center">
            <span class="material-symbols-outlined text-6xl text-on-surface-variant/30 mb-4">group_off</span>
            <p class="text-on-surface-variant font-medium">Belum ada anggota di kamar ini</p>
            <button @click="openModal()"
                    class="mt-4 text-primary font-bold hover:underline">
                Tambah anggota pertama
            </button>
        </div>
        @endif
    </div>

    <!-- Add Member Modal -->
    <template x-teleport="body">
        <div x-show="showModal"
             x-cloak
             @keydown.escape.window="closeModal()"
             class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeModal()"
                 class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

            <!-- Modal Content -->
            <div x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="bg-surface-container-lowest rounded-2xl shadow-2xl w-full max-w-lg relative z-10 max-h-[80vh] flex flex-col">

                <div class="p-6 border-b border-outline-variant/10 flex-shrink-0">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-headline font-bold text-on-surface">Tambah Anggota ke {{ $kamarLabel }}</h3>
                        <button @click="closeModal()" class="p-2 hover:bg-surface-container-low rounded-lg transition-colors">
                            <span class="material-symbols-outlined text-on-surface">close</span>
                        </button>
                    </div>
                </div>

                <div class="p-6 overflow-y-auto flex-1">
                    <p x-show="loading" class="text-center text-on-surface-variant py-8">
                        <span class="material-symbols-outlined animate-spin inline-block text-lg">progress_activity</span>
                        <span class="block mt-2">Memuat data santri...</span>
                    </p>

                    <div x-show="!loading && santri.length === 0" class="text-center py-8 text-on-surface-variant">
                        Semua santri sudah terdaftar di kamar
                    </div>

                    <div x-show="!loading && santri.length > 0" class="space-y-2">
                        <template x-for="s in paginatedSantri" :key="s.id">
                            <div class="flex items-center justify-between p-4 bg-surface-container-low rounded-xl hover:bg-surface-container-low/80 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <p class="font-body font-medium text-on-surface truncate" x-text="s.name"></p>
                                    <p class="text-sm text-on-surface-variant" x-text="s.nis ? 'NIS: ' + s.nis : ''"></p>
                                </div>
                                <form action="{{ route('admin.kamar.store') }}" method="POST" class="ml-4 flex-shrink-0">
                                    @csrf
                                    <input type="hidden" name="kamar" value="{{ $kamar }}">
                                    <input type="hidden" name="user_id" :value="s.id">
                                    <button type="submit"
                                            class="bg-primary-container text-on-primary-container px-4 py-2 rounded-lg font-body font-bold text-sm hover:opacity-90 transition-all flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">person_add</span>
                                        <span>Tambah</span>
                                    </button>
                                </form>
                            </div>
                        </template>
                    </div>
                    
                    <!-- Pagination -->
                    <div x-show="!loading && totalPages > 1" class="mt-4 pt-4 border-t border-outline-variant/10">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-on-surface-variant">
                                Menampilkan <span x-text="paginatedSantri.length"></span> dari <span x-text="santri.length"></span> santri
                            </p>
                            <div class="flex items-center gap-2">
                                <button @click="prevPage"
                                        :disabled="currentPage === 1"
                                        :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-surface-container-low'"
                                        class="p-2 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                                </button>
                                
                                <template x-for="page in visiblePages" :key="page">
                                    <button @click="goToPage(page)"
                                            :class="page === currentPage ? 'bg-primary-container text-on-primary-container' : 'hover:bg-surface-container-low text-on-surface-variant'"
                                            class="w-8 h-8 rounded-lg font-body text-sm transition-colors"
                                            x-text="page">
                                    </button>
                                </template>
                                
                                <button @click="nextPage"
                                        :disabled="currentPage === totalPages"
                                        :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-surface-container-low'"
                                        class="p-2 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
    function kamarPage() {
        return {
            showModal: false,
            santri: [],
            loading: false,
            currentPage: 1,
            perPage: 5,
            
            get totalPages() {
                return Math.ceil(this.santri.length / this.perPage);
            },
            
            get paginatedSantri() {
                const start = (this.currentPage - 1) * this.perPage;
                const end = start + this.perPage;
                return this.santri.slice(start, end);
            },
            
            get visiblePages() {
                const pages = [];
                const total = this.totalPages;
                const current = this.currentPage;
                
                if (total <= 7) {
                    for (let i = 1; i <= total; i++) {
                        pages.push(i);
                    }
                } else {
                    if (current <= 4) {
                        for (let i = 1; i <= 5; i++) pages.push(i);
                        pages.push('...');
                        pages.push(total);
                    } else if (current >= total - 3) {
                        pages.push(1);
                        pages.push('...');
                        for (let i = total - 4; i <= total; i++) pages.push(i);
                    } else {
                        pages.push(1);
                        pages.push('...');
                        for (let i = current - 1; i <= current + 1; i++) pages.push(i);
                        pages.push('...');
                        pages.push(total);
                    }
                }
                return pages;
            },
            
            prevPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                }
            },
            
            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                }
            },
            
            goToPage(page) {
                if (page !== '...') {
                    this.currentPage = page;
                }
            },
            
            openModal() {
                this.showModal = true;
                this.santri = [];
                this.currentPage = 1;
                this.fetchSantri();
            },
            closeModal() {
                this.showModal = false;
                this.santri = [];
                this.currentPage = 1;
            },
            async fetchSantri() {
                this.loading = true;
                try {
                    const res = await fetch('{{ route("admin.kamar.available-santri") }}');
                    const json = await res.json();
                    this.santri = json.data || [];
                } catch (e) {
                    console.error('Error:', e);
                } finally {
                    this.loading = false;
                }
            }
        };
    }
</script>
@endpush
@endsection

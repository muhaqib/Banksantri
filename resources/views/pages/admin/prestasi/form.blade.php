@php
    $isEdit = isset($prestasi) && $prestasi;
    $selectedKitabId = (string) old('kitab_id', $prestasi?->kitab_id ?? '');
    $selectedPredikat = old('nilai', $prestasi?->nilai ?? 'Mumtaz');
@endphp

<div class="max-w-5xl mx-auto" x-data="prestasiForm()">
    <div class="mb-8">
        <h2 class="font-headline font-extrabold text-3xl text-primary tracking-tight">
            {{ $isEdit ? 'Edit Prestasi Hafalan Kitab' : 'Input Prestasi Hafalan Kitab' }}
        </h2>
        <p class="text-on-surface-variant mt-2">Catat pencapaian hafalan santri secara cepat dan akurat untuk memantau progres mereka.</p>
    </div>

    <form action="{{ $isEdit ? route($prestasiRoutePrefix . '.prestasi.update', $prestasi) : route($prestasiRoutePrefix . '.prestasi.store') }}"
          method="POST" class="bg-surface-container-lowest p-6 md:p-9 rounded-2xl shadow-sm space-y-7">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div>
            <label class="flex items-center gap-2 font-bold text-on-surface mb-3">
                <span class="material-symbols-outlined text-primary text-lg">group</span>
                Pilih Santri
            </label>
            <select name="santri_id" required class="w-full bg-surface-container-high border-none rounded-xl py-4 px-4 text-on-surface focus:ring-2 focus:ring-primary/30">
                <option value="">Cari Nama atau NIS</option>
                @foreach($santriList as $santri)
                    <option value="{{ $santri->id }}" @selected(old('santri_id', $prestasi?->santri_id) == $santri->id)>
                        {{ $santri->name }} - {{ $santri->nis ?? 'Tanpa NIS' }}
                    </option>
                @endforeach
            </select>
            @error('santri_id') <p class="text-error text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="flex items-center gap-2 font-bold text-on-surface mb-3">
                <span class="material-symbols-outlined text-primary text-lg">menu_book</span>
                Pilih Kitab
            </label>
            <select name="kitab_id" x-model="selectedKitabId" required class="w-full bg-surface-container-high border-none rounded-xl py-4 px-4 text-on-surface focus:ring-2 focus:ring-primary/30">
                <option value="">Cari Nama Kitab</option>
                <template x-for="kitab in kitabs" :key="kitab.id">
                    <option :value="String(kitab.id)" x-text="`${kitab.nama} - ${kitab.kategori}`"></option>
                </template>
            </select>
            @error('kitab_id') <p class="text-error text-xs mt-2">{{ $message }}</p> @enderror
            <button type="button" @click="kitabModalOpen = true" class="mt-3 inline-flex items-center gap-2 bg-primary text-on-primary px-5 py-3 rounded-xl font-bold hover:bg-primary-container transition-colors">
                <span class="material-symbols-outlined text-lg">add_circle</span>
                Kitab Baru
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="flex items-center gap-2 font-bold text-on-surface mb-3">
                    <span class="material-symbols-outlined text-primary text-lg">calendar_month</span>
                    Tanggal Pencapaian
                </label>
                <input type="date" name="tanggal_selesai" required value="{{ old('tanggal_selesai', $prestasi?->tanggal_selesai?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                       class="w-full bg-surface-container-high border-none rounded-xl py-4 px-4 text-on-surface focus:ring-2 focus:ring-primary/30">
                @error('tanggal_selesai') <p class="text-error text-xs mt-2">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="flex items-center gap-2 font-bold text-on-surface mb-3">
                    <span class="material-symbols-outlined text-primary text-lg">star</span>
                    Progress (per 100%)
                </label>
                <input type="number" name="progress" min="0" max="100" required value="{{ old('progress', $prestasi?->progress ?? 0) }}"
                       class="w-full bg-surface-container-high border-none rounded-xl py-4 px-4 text-on-surface focus:ring-2 focus:ring-primary/30" placeholder="Contoh: 95">
                <p class="text-xs text-on-surface-variant mt-2">Progress 100% otomatis berstatus selesai.</p>
                @error('progress') <p class="text-error text-xs mt-2">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block font-bold text-on-surface mb-3">Predikat Pencapaian</label>
            <div class="grid grid-cols-2 md:grid-cols-4 bg-surface-container-high rounded-xl p-1 gap-1">
                @foreach($predikatList as $predikat => $nilai)
                    <label class="cursor-pointer">
                        <input type="radio" name="nilai" value="{{ $predikat }}" class="peer sr-only" required
                               x-model="selectedPredikat" @checked($selectedPredikat === $predikat)>
                        <span class="block text-center rounded-lg px-3 py-3 text-sm font-semibold peer-checked:bg-primary peer-checked:text-on-primary peer-checked:shadow-md transition-all">
                            {{ $predikat }}
                        </span>
                    </label>
                @endforeach
            </div>
            <p class="text-xs text-on-surface-variant mt-2" x-text="predikatInfo"></p>
            @error('nilai') <p class="text-error text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="flex items-center gap-2 font-bold text-on-surface mb-3">
                <span class="material-symbols-outlined text-primary text-lg">notes</span>
                Catatan Pembimbing
            </label>
            <textarea name="catatan_ustadz" rows="4" class="w-full bg-surface-container-high border-none rounded-xl py-4 px-4 text-on-surface focus:ring-2 focus:ring-primary/30 resize-none"
                      placeholder="Berikan masukan atau catatan khusus untuk santri...">{{ old('catatan_ustadz', $prestasi?->catatan_ustadz) }}</textarea>
            <p class="text-xs text-on-surface-variant mt-2">Ustadz pembimbing: <strong>{{ auth()->user()->name }}</strong></p>
            @error('catatan_ustadz') <p class="text-error text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end items-center gap-4 pt-5">
            <a href="{{ route($prestasiRoutePrefix . '.prestasi.index') }}" class="px-6 py-3 font-bold text-on-surface hover:text-primary">Batal</a>
            <button type="submit" class="px-8 py-3 bg-primary text-on-primary rounded-xl font-bold shadow-lg shadow-primary/20 hover:bg-primary-container transition-colors">
                {{ $isEdit ? 'Update Prestasi' : 'Simpan Prestasi' }}
            </button>
        </div>
    </form>

    <div x-show="kitabModalOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div @click.outside="kitabModalOpen = false" class="bg-surface-container-lowest rounded-2xl shadow-2xl max-w-2xl w-full p-6 md:p-8">
            <div class="flex justify-between items-center mb-7">
                <h3 class="font-headline font-extrabold text-3xl text-primary">Tambah Kitab</h3>
                <button type="button" @click="kitabModalOpen = false"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form @submit.prevent="storeKitab" class="space-y-6">
                <div>
                    <label class="block font-bold mb-3">Nama Kitab Baru</label>
                    <input type="text" x-model="newKitab.nama" required class="w-full bg-surface-container-high border-none rounded-xl py-4 px-4" placeholder="Contoh: Arbain Annawawi">
                </div>
                <div>
                    <label class="block font-bold mb-3">Kategori Kitab</label>
                    <select x-model="newKitab.kategori" required class="w-full bg-surface-container-high border-none rounded-xl py-4 px-4">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach(['Hafalan', 'Tajwid', "Qira'at", 'Hadits', 'Fiqih', 'Bahasa Arab'] as $kategori)
                            <option value="{{ $kategori }}">{{ $kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-bold mb-3">Upload Gambar</label>
                    <input type="file" x-ref="gambarKitab" accept="image/*" class="w-full bg-surface-container-high border-none rounded-xl py-4 px-4">
                </div>
                <p x-show="kitabError" x-text="kitabError" class="text-error text-sm"></p>
                <div class="flex justify-end gap-4 pt-2">
                    <button type="button" @click="kitabModalOpen = false" class="px-6 py-3 font-bold">Batal</button>
                    <button type="submit" :disabled="savingKitab" class="px-7 py-3 bg-primary text-on-primary rounded-xl font-bold disabled:opacity-50">
                        <span x-text="savingKitab ? 'Menyimpan...' : 'Simpan Kitab'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function prestasiForm() {
    return {
        kitabs: @json($kitabList->map->only(['id', 'nama', 'kategori'])->values()),
        selectedKitabId: @js($selectedKitabId),
        selectedPredikat: @js($selectedPredikat),
        predikat: @json($predikatList),
        kitabModalOpen: false,
        savingKitab: false,
        kitabError: '',
        newKitab: { nama: '', kategori: '' },

        get predikatInfo() {
            const nilai = this.predikat[this.selectedPredikat];
            return nilai ? `Skor ${nilai.skor} dan ${nilai.poin} poin` : '';
        },

        async storeKitab() {
            this.savingKitab = true;
            this.kitabError = '';
            const formData = new FormData();
            formData.append('nama', this.newKitab.nama);
            formData.append('kategori', this.newKitab.kategori);
            if (this.$refs.gambarKitab.files[0]) formData.append('gambar', this.$refs.gambarKitab.files[0]);

            try {
                const response = await fetch(@js(route($prestasiRoutePrefix . '.kitab.store')), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData,
                });
                const data = await response.json();
                if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message);

                this.kitabs.push(data.kitab);
                this.kitabs.sort((a, b) => a.nama.localeCompare(b.nama));
                this.selectedKitabId = String(data.kitab.id);
                this.newKitab = { nama: '', kategori: '' };
                this.$refs.gambarKitab.value = '';
                this.kitabModalOpen = false;
            } catch (error) {
                this.kitabError = error.message || 'Kitab gagal disimpan.';
            } finally {
                this.savingKitab = false;
            }
        }
    }
}
</script>
@endpush

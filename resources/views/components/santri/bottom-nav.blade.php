<nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-6 py-4 bg-surface/80 backdrop-blur-xl shadow-[0_-4px_24px_-4px_rgba(25,28,29,0.06)] rounded-t-[1.5rem]">
    <a href="{{ route('santri.home') }}" class="flex flex-col items-center justify-center transition-all duration-300 ease-out p-2 rounded-xl {{ request()->routeIs('santri.home') ? 'bg-primary text-on-primary scale-105 shadow-lg' : 'text-on-surface opacity-60 hover:bg-surface-container-low hover:opacity-100 hover:scale-105' }}">
        <span class="material-symbols-outlined text-lg transition-transform duration-300" style="font-variation-settings: 'FILL' 1;">home</span>
        <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Beranda</span>
    </a>
    <a href="{{ route('santri.riwayat') }}" class="flex flex-col items-center justify-center transition-all duration-300 ease-out p-2 rounded-xl {{ request()->routeIs('santri.riwayat') ? 'bg-primary text-on-primary scale-105 shadow-lg' : 'text-on-surface opacity-60 hover:bg-surface-container-low hover:opacity-100 hover:scale-105' }}">
        <span class="material-symbols-outlined text-lg transition-transform duration-300" style="font-variation-settings: 'FILL' 1;">history</span>
        <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Riwayat</span>
    </a>
    <a href="{{ route('santri.prestasi') }}" class="flex flex-col items-center justify-center transition-all duration-300 ease-out p-2 rounded-xl {{ request()->routeIs('santri.prestasi', 'santri.prestasi.show') ? 'bg-primary text-on-primary scale-105 shadow-lg' : 'text-on-surface opacity-60 hover:bg-surface-container-low hover:opacity-100 hover:scale-105' }}">
        <span class="material-symbols-outlined text-lg transition-transform duration-300" style="font-variation-settings: 'FILL' 1;">military_tech</span>
        <span class="font-manrope text-[10px] font-semibold uppercase tracking-widest mt-1">Kompetensi</span>
    </a>
    <a href="{{ route('santri.profile') }}" class="flex flex-col items-center justify-center transition-all duration-300 ease-out p-2 rounded-xl {{ request()->routeIs('santri.profile', 'santri.profile.settings') ? 'bg-primary text-on-primary scale-105 shadow-lg' : 'text-on-surface opacity-60 hover:bg-surface-container-low hover:opacity-100 hover:scale-105' }}">
        <span class="material-symbols-outlined text-lg transition-transform duration-300">person</span>
        <span class="font-body text-[10px] font-medium uppercase tracking-widest mt-0.5">Profil</span>
    </a>
</nav>

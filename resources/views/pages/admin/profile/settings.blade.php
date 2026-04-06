@extends('components.sidebar')

@section('title', 'Profile Settings')

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ activeTab: 'email' }">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-headline font-bold text-on-surface">Pengaturan Akun</h1>
        <p class="text-on-surface-variant mt-1">Kelola email dan password akun Anda</p>
    </div>

    <!-- Tabs -->
    <div class="bg-surface-container-low rounded-2xl shadow-sm mb-6">
        <div class="border-b border-outline-variant/10 px-6 pt-4">
            <div class="flex gap-4">
                <button @click="activeTab = 'email'"
                        :class="activeTab === 'email' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-on-surface'"
                        class="px-4 py-3 font-body text-sm font-medium border-b-2 transition-colors">
                    <span class="material-symbols-outlined text-sm mr-2 align-middle">email</span>
                    Ganti Email
                </button>
                <button @click="activeTab = 'password'"
                        :class="activeTab === 'password' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant hover:text-on-surface'"
                        class="px-4 py-3 font-body text-sm font-medium border-b-2 transition-colors">
                    <span class="material-symbols-outlined text-sm mr-2 align-middle">lock</span>
                    Ganti Password
                </button>
            </div>
        </div>

        <!-- Email Change Form -->
        <div x-show="activeTab === 'email'" class="p-6">
            <form action="{{ route('admin.profile.email') }}" method="POST">
                @csrf
                <div class="space-y-6">
                    <!-- Current Email -->
                    <div>
                        <label class="block text-sm font-medium text-on-surface-variant mb-2">Email Saat Ini</label>
                        <div class="px-4 py-3 bg-surface-container-low/50 border border-outline-variant/20 rounded-xl text-on-surface">
                            {{ $user->email }}
                        </div>
                    </div>

                    <!-- New Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-on-surface-variant mb-2">
                            Email Baru <span class="text-error">*</span>
                        </label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email') }}"
                               required 
                               class="w-full px-4 py-3 bg-surface border border-outline-variant/30 rounded-xl text-on-surface placeholder-on-surface-variant/50 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all @error('email') border-error @enderror"
                               placeholder="Masukkan email baru">
                        @error('email')
                            <p class="mt-2 text-sm text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center gap-3 pt-4">
                        <button type="submit" 
                                class="px-6 py-3 bg-primary text-on-primary font-medium rounded-xl hover:bg-primary-container transition-colors flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">save</span>
                            Simpan Email
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Password Change Form -->
        <div x-show="activeTab === 'password'" class="p-6">
            <form action="{{ route('admin.profile.password') }}" method="POST">
                @csrf
                <div class="space-y-6">
                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-on-surface-variant mb-2">
                            Password Saat Ini <span class="text-error">*</span>
                        </label>
                        <div x-data="{ show: false }" class="relative">
                            <input :type="show ? 'text' : 'password'" 
                                   name="current_password" 
                                   id="current_password" 
                                   required 
                                   class="w-full px-4 py-3 pr-12 bg-surface border border-outline-variant/30 rounded-xl text-on-surface placeholder-on-surface-variant/50 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all @error('current_password') border-error @enderror"
                                   placeholder="Masukkan password saat ini">
                            <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface">
                                <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="mt-2 text-sm text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-on-surface-variant mb-2">
                            Password Baru <span class="text-error">*</span>
                        </label>
                        <div x-data="{ show: false }" class="relative">
                            <input :type="show ? 'text' : 'password'" 
                                   name="password" 
                                   id="password" 
                                   required 
                                   class="w-full px-4 py-3 pr-12 bg-surface border border-outline-variant/30 rounded-xl text-on-surface placeholder-on-surface-variant/50 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all @error('password') border-error @enderror"
                                   placeholder="Masukkan password baru">
                            <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface">
                                <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-error">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-on-surface-variant">Minimal 6 karakter</p>
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-on-surface-variant mb-2">
                            Konfirmasi Password Baru <span class="text-error">*</span>
                        </label>
                        <div x-data="{ show: false }" class="relative">
                            <input :type="show ? 'text' : 'password'" 
                                   name="password_confirmation" 
                                   id="password_confirmation" 
                                   required 
                                   class="w-full px-4 py-3 pr-12 bg-surface border border-outline-variant/30 rounded-xl text-on-surface placeholder-on-surface-variant/50 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                                   placeholder="Ulangi password baru">
                            <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface">
                                <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center gap-3 pt-4">
                        <button type="submit" 
                                class="px-6 py-3 bg-primary text-on-primary font-medium rounded-xl hover:bg-primary-container transition-colors flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">lock_reset</span>
                            Simpan Password
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Account Info Card -->
    <div class="bg-surface-container-low rounded-2xl p-6">
        <h3 class="text-lg font-headline font-bold text-on-surface mb-4">Informasi Akun</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3 border-b border-outline-variant/10">
                <div>
                    <p class="text-sm text-on-surface-variant">Nama Lengkap</p>
                    <p class="font-medium text-on-surface">{{ $user->name }}</p>
                </div>
            </div>
            <div class="flex items-center justify-between py-3 border-b border-outline-variant/10">
                <div>
                    <p class="text-sm text-on-surface-variant">Email</p>
                    <p class="font-medium text-on-surface">{{ $user->email }}</p>
                </div>
            </div>
            <div class="flex items-center justify-between py-3">
                <div>
                    <p class="text-sm text-on-surface-variant">Role</p>
                    <p class="font-medium text-on-surface capitalize">{{ $user->role }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

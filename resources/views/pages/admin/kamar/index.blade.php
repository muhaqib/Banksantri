@extends('components.sidebar', ['activeRole' => 'admin'])

@section('title', 'Data Kamar Santri')

@section('content')
@php $activeRole = 'admin'; @endphp

<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-headline font-bold text-on-surface">Data Kamar Santri</h1>
        <p class="text-on-surface-variant mt-2">Pondok Pesantren Mambaul Hikmah</p>
    </div>

    <!-- Kamar Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($kamarData as $kamar)
        <a href="{{ route('admin.kamar.show', $kamar['name']) }}" 
           class="block bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant/10 hover:border-primary/50 hover:shadow-lg hover:shadow-primary/10 transition-all group">
            <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-primary/20 transition-colors">
                    <span class="material-symbols-outlined text-primary text-3xl">meeting_room</span>
                </div>
                <h3 class="font-headline font-bold text-lg text-on-surface mb-2">{{ $kamar['label'] }}</h3>
                <div class="flex items-center gap-2 text-on-surface-variant">
                    <span class="material-symbols-outlined text-sm">group</span>
                    <span class="text-sm font-medium">{{ $kamar['count'] }} santri</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    <!-- Summary Section -->
    <div class="mt-12 bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant/10">
        <h2 class="font-headline font-bold text-xl text-on-surface mb-4">Ringkasan</h2>
       
        <div class="mt-6 pt-6 border-t border-outline-variant/10">
            <div class="flex items-center justify-between">
                <span class="font-headline font-bold text-on-surface">Total Santri di Kamar</span>
                <span class="text-2xl font-bold text-primary">{{ array_sum(array_column($kamarData, 'count')) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection

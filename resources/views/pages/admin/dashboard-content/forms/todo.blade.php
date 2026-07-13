@php $editing = $editing ?? false; @endphp
<input type="hidden" name="type" value="todo">
<div class="space-y-4">
    <div><label class="form-label">Judul Tugas</label><input name="title" required maxlength="255" @if($editing) x-model="selected.title" @endif class="input-field w-full"></div>
    <div><label class="form-label">Ringkasan Tugas</label><textarea name="summary" rows="2" maxlength="500" @if($editing) x-model="selected.summary" @endif class="input-field w-full"></textarea></div>
    <div><label class="form-label">Instruksi Lengkap</label><textarea name="content" required rows="6" @if($editing) x-model="selected.content" @endif class="input-field w-full"></textarea></div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div><label class="form-label">Prioritas</label><select name="priority" required @if($editing) x-model="selected.priority" @endif class="input-field w-full">@foreach($priorities as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div>
        <div><label class="form-label">Tenggat Tugas</label><input type="date" name="due_date" required @if($editing) x-model="selected.due_date" @endif class="input-field w-full"></div>
        @include('pages.admin.dashboard-content.forms.status')
    </div>
    <div class="rounded-xl bg-surface-container-low p-4">
        <label class="form-label">Penerima Tugas</label>
        <div class="flex flex-wrap gap-4 mb-4">
            <label class="flex items-center gap-2 text-sm font-bold"><input type="radio" name="assignment_scope" value="all" @if($editing) x-model="selected.assignment_scope" @else x-model="todoScope" @endif> Semua Petugas</label>
            <label class="flex items-center gap-2 text-sm font-bold"><input type="radio" name="assignment_scope" value="selected" @if($editing) x-model="selected.assignment_scope" @else x-model="todoScope" @endif> Pilih Petugas</label>
        </div>
        <div x-show="{{ $editing ? "selected.assignment_scope === 'selected'" : "todoScope === 'selected'" }}" class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-52 overflow-y-auto">
            @foreach($petugasList as $petugas)
                <label class="flex items-center gap-3 rounded-xl bg-surface-container-lowest p-3 text-sm">
                    <input type="checkbox" name="assignee_ids[]" value="{{ $petugas->id }}" @if($editing) :checked="selected.assignee_ids?.includes({{ $petugas->id }})" @endif>
                    <span><strong>{{ $petugas->name }}</strong><small class="block text-on-surface-variant">{{ $petugas->jabatan ?: 'Petugas' }}</small></span>
                </label>
            @endforeach
        </div>
    </div>
</div>
@include('pages.admin.dashboard-content.forms.submit')

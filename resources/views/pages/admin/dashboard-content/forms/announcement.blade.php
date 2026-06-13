@php $editing = $editing ?? false; @endphp
<input type="hidden" name="type" value="announcement">
<div class="space-y-4">
    <div><label class="form-label">Judul Pengumuman</label><input name="title" required maxlength="255" @if($editing) x-model="selected.title" @endif class="input-field w-full"></div>
    <div><label class="form-label">Isi Pengumuman</label><textarea name="content" required rows="7" @if($editing) x-model="selected.content" @endif class="input-field w-full"></textarea></div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div><label class="form-label">Prioritas</label><select name="priority" required @if($editing) x-model="selected.priority" @endif class="input-field w-full">@foreach($priorities as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div>
        <div><label class="form-label">Tanggal Kegiatan</label><input type="date" name="event_date" @if($editing) x-model="selected.event_date" @endif class="input-field w-full"></div>
        @include('pages.admin.dashboard-content.forms.status')
    </div>
</div>
@include('pages.admin.dashboard-content.forms.submit')

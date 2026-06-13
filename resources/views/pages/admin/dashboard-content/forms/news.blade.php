@php $editing = $editing ?? false; @endphp
<input type="hidden" name="type" value="news">
<input type="hidden" name="priority" value="normal">
<div class="space-y-4">
    <div><label class="form-label">Judul Berita</label><input name="title" required maxlength="255" @if($editing) x-model="selected.title" @endif class="input-field w-full"></div>
    <div><label class="form-label">Ringkasan Berita</label><textarea name="summary" required rows="2" maxlength="500" @if($editing) x-model="selected.summary" @endif class="input-field w-full"></textarea></div>
    <div><label class="form-label">Isi Berita Lengkap</label><textarea name="content" required rows="7" @if($editing) x-model="selected.content" @endif class="input-field w-full"></textarea></div>
    <div>
        <label class="form-label">Link Thumbnail</label>
        <input type="url" name="thumbnail_url" required placeholder="https://contoh.com/gambar.jpg" @if($editing) x-model="selected.thumbnail_url" @else x-model="newsThumbnail" @endif class="input-field w-full">
        <div class="mt-3 h-40 rounded-xl overflow-hidden bg-surface-container-high flex items-center justify-center">
            <template x-if="{{ $editing ? 'selected.thumbnail_url' : 'newsThumbnail' }}"><img :src="{{ $editing ? 'selected.thumbnail_url' : 'newsThumbnail' }}" class="w-full h-full object-cover" x-on:error="$el.style.display='none'"></template>
            <span class="material-symbols-outlined text-4xl text-on-surface-variant">image</span>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div><label class="form-label">Tanggal Kegiatan</label><input type="date" name="event_date" @if($editing) x-model="selected.event_date" @endif class="input-field w-full"></div>
        @include('pages.admin.dashboard-content.forms.status')
    </div>
</div>
@include('pages.admin.dashboard-content.forms.submit')

@props(['entry'])
{{-- An entry's cover: the uploaded picture, or one drawn for it by App\Support\CoverArt. --}}
<div {{ $attributes->class(['cover', 'cover-'.$entry->type->coverShape()]) }}>
    @if ($entry->cover_path)
        <img src="{{ $entry->coverUrl() }}" alt="Cover of {{ $entry->title }}" loading="lazy">
    @else
        {!! \App\Support\CoverArt::render($entry) !!}
    @endif
</div>

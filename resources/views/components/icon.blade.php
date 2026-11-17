@props(['name', 'size' => 'h-5 w-5'])
{{-- Hand-drawn line icons. <x-icon name="book" /> --}}
<svg {{ $attributes->class([$size, 'flex-none']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
    @case('book')<path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>@break
    @case('play')<path d="M7 4.5v15l12-7.5-12-7.5Z" fill="currentColor"/>@break
    @case('pause')<path d="M8 5v14M16 5v14" stroke-width="3.4"/>@break
    @case('headphones')<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><path d="M4 14h3v6H5a1 1 0 0 1-1-1v-5ZM20 14h-3v6h2a1 1 0 0 0 1-1v-5Z"/>@break
    @case('pen')<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5Z"/>@break
    @case('search')<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>@break
    @case('chat')<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12Z"/>@break
    @case('upload')<path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M4 20h16"/>@break
    @case('download')<path d="M12 4v12"/><path d="m7 11 5 5 5-5"/><path d="M4 20h16"/>@break
    @case('user')<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>@break
    @case('users')<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M18 14.2a6.5 6.5 0 0 1 3.5 5.8"/>@break
    @case('menu')<path d="M4 7h16M4 12h16M4 17h16"/>@break
    @case('close')<path d="M6 6l12 12M18 6 6 18"/>@break
    @case('sun')<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>@break
    @case('moon')<path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5Z"/>@break
    @case('external')<path d="M14 4h6v6"/><path d="M20 4 10 14"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>@break
    @case('trash')<path d="M4 7h16"/><path d="M9 7V4h6v3"/><path d="M6 7l1 13h10l1-13"/>@break
    @case('lock')<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>@break
    @case('plus')<path d="M12 5v14M5 12h14"/>@break
    @case('arrow-right')<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>@break
    @case('arrow-left')<path d="M19 12H5"/><path d="m11 6-6 6 6 6"/>@break
    @case('check')<path d="m5 12 5 5 9-10"/>@break
    @case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
    @case('reply')<path d="M9 14 4 9l5-5"/><path d="M4 9h10a6 6 0 0 1 6 6v5"/>@break
    @case('shield')<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.500-1 8-4 8-9V6l-8-3Z"/>@break
    @case('grid')<rect x="4" y="4" width="6.5" height="6.5" rx="1.2"/><rect x="13.5" y="4" width="6.5" height="6.5" rx="1.2"/><rect x="4" y="13.5" width="6.5" height="6.5" rx="1.2"/><rect x="13.5" y="13.5" width="6.5" height="6.5" rx="1.2"/>@break
    @case('logout')<path d="M9 4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>@break
    @case('file')<path d="M6 3h8l4 4v14H6V3Z"/><path d="M14 3v4h4"/>@break
    @case('image')<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9.5" r="1.6"/><path d="m21 16-5-5-9 9"/>@break
    @case('back')<path d="m11 7-5 5 5 5"/><path d="m18 7-5 5 5 5"/>@break
    @case('forward')<path d="m13 7 5 5-5 5"/><path d="m6 7 5 5-5 5"/>@break
    @case('tag')<path d="M3 12V4h8l10 10-8 8L3 12Z"/><circle cx="7.5" cy="8.5" r="1.3"/>@break
    @case('star')<path d="M12 2.8l2.83 5.74 6.34.92-4.59 4.47 1.08 6.31L12 17.26l-5.66 2.98 1.08-6.31L2.83 9.46l6.34-.92L12 2.8z"/>@break
    @case('basket')<path d="M3 9h18l-1.600 10a1.500 1.500 0 0 1-1.500 1.300H6.100a1.500 1.500 0 0 1-1.500-1.300L3 9Z"/><path d="M8 9l3-6M16 9l-3-6M9.500 13v3.500M14.500 13v3.500"/>@break
    @case('info')<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5v.5"/>@break
@endswitch
</svg>

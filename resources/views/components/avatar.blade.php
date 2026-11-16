@props(['user', 'small' => false])
{{-- A round badge with the member's initials, in "their" colour. --}}
<span {{ $attributes->class(['avatar', 'avatar-sm' => $small, 'tone-'.$user->colour()]) }} aria-hidden="true">{{ $user->initials() }}</span>

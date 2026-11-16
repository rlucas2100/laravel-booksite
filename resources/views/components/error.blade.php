@props(['for', 'bag' => 'default'])
{{-- The validation message for one field. <x-error for="title" /> --}}
@if ($errors->getBag($bag)->has($for))
    <p {{ $attributes->class('field-error') }} role="alert">{{ $errors->getBag($bag)->first($for) }}</p>
@endif

@props([
    'href' => null,
    'size' => 32,
    'class' => 'fs-24',
    'textClass' => 'text-dark',
    'accentClass' => 'text-primary',
    'accentStyle' => null,
])

@php
    $accentAttr = $accentStyle ? ' style="'.$accentStyle.'"' : '';
    $tag = $href ? 'a' : 'span';
    $linkAttr = $href ? ' href="'.$href.'"' : '';
@endphp

<{{ $tag }} class="fw-bold {{ $class }} {{ $textClass }} text-decoration-none d-inline-flex align-items-center"{!! $linkAttr !!}>
    <img src="{{ asset('favicon-32.png') }}" height="{{ $size }}" class="me-2 align-middle" alt="{{ config('app.name') }}">
    <span class="{{ $textClass }}">Rande</span><span class="{{ $accentClass }}"{!! $accentAttr !!}>vall</span>
</{{ $tag }}>

<!DOCTYPE html>
<html lang="tr">

@php
    $isPanelPwa = isset($business) && $business->exists && request()->routeIs('panel.*');
@endphp

<head>
    @include('layouts.partials.title-meta', ['title' => $title ?? 'Panel'])
    @if($isPanelPwa)
        @include('layouts.partials.panel-pwa-meta', ['business' => $business])
    @endif
    @include('layouts.partials.head-css')
</head>

<body
    @if($isPanelPwa)
        class="panel-pwa-body"
        data-pwa-scope="{{ url($business->slug.'/panel/') }}"
        data-pwa-sw-url="{{ route('panel.pwa.sw', $business) }}"
    @endif
>

<div class="wrapper">

    @include('layouts.partials.topbar')
    @include('layouts.partials.main-nav')

    <div class="page-content">

        <div class="container-fluid">

            @include('layouts.partials.page-title', ['title' => $title ?? '', 'subTitle' => $subTitle ?? null])

            @include('layouts.partials.alerts')

            @yield('content')

        </div>

        @include('layouts.partials.footer')

        @yield('modal')
    </div>

</div>

@if($isPanelPwa)
    @include('layouts.partials.panel-mobile-nav', ['business' => $business])
@endif

@include('layouts.partials.right-sidebar')
@include('layouts.partials.footer-scripts')

@if($isPanelPwa)
    @vite(['resources/js/panel-pwa.js'])
@endif

</body>

</html>

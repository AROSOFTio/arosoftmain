@props([
    'slot',
    'format' => 'auto',
    'layout' => null,
    'layoutKey' => null,
    'style' => 'display:block',
    'insClass' => '',
    'wrapperClass' => 'info-card',
    'fullWidthResponsive' => null,
    'label' => 'Sponsored',
])

@php
    $publisherId = 'ca-pub-6208436737131241';
    $resolvedWrapperClass = trim($wrapperClass.' adsense-unit');
@endphp

<div class="{{ $resolvedWrapperClass }}">
    @if($label !== '')
        <p class="mb-2 text-[0.62rem] font-semibold uppercase tracking-[0.14em] muted-faint">{{ $label }}</p>
    @endif

    <ins
        class="adsbygoogle adsense-slot {{ $insClass }}"
        style="{{ $style }}"
        data-ad-client="{{ $publisherId }}"
        data-ad-slot="{{ $slot }}"
        @if($format !== '') data-ad-format="{{ $format }}" @endif
        @if($layout) data-ad-layout="{{ $layout }}" @endif
        @if($layoutKey) data-ad-layout-key="{{ $layoutKey }}" @endif
        @if(!is_null($fullWidthResponsive)) data-full-width-responsive="{{ $fullWidthResponsive ? 'true' : 'false' }}" @endif
    ></ins>
</div>

<script>
    (adsbygoogle = window.adsbygoogle || []).push({});
</script>

@extends('layouts.app')

@section('title', "AROSOFT | {$title}")
@section('meta_description', $copy)
@section('canonical', url()->current())

@section('content')
    <section class="hero-surface p-8 sm:p-10 lg:p-12">
        <p class="page-kicker">{{ $title }}</p>
        <h1 class="page-title mt-4">{{ $heading }}</h1>
        <p class="section-copy mt-5 max-w-3xl">{{ $copy }}</p>
        <div class="mt-7 flex flex-wrap gap-3">
            <a href="{{ route('contact') }}" class="btn-solid">Contact us</a>
            <a href="{{ route('home') }}" class="btn-outline">Back to home</a>
        </div>
    </section>

    <section class="content-section grid gap-5 md:grid-cols-3">
        <article class="info-card">
            <p class="page-kicker">Quick link</p>
            <h2 class="font-heading mt-2 text-xl">Services</h2>
            <p class="mt-3 text-sm leading-7 muted-copy">See available service lines and how we can support your organization.</p>
            <a href="{{ route('services') }}" class="nav-link-sm mt-3 inline-flex">View services</a>
        </article>
        <article class="info-card">
            <p class="page-kicker">Quick link</p>
            <h2 class="font-heading mt-2 text-xl">Tutorials</h2>
            <p class="mt-3 text-sm leading-7 muted-copy">Read guides and watch practical videos from our implementation team.</p>
            <a href="{{ route('tutorials') }}" class="nav-link-sm mt-3 inline-flex">View tutorials</a>
        </article>
        <article class="info-card">
            <p class="page-kicker">Quick link</p>
            <h2 class="font-heading mt-2 text-xl">About</h2>
            <p class="mt-3 text-sm leading-7 muted-copy">Learn about Arosoft Innovations Ltd and the values behind our work.</p>
            <a href="{{ route('about') }}" class="nav-link-sm mt-3 inline-flex">Read about us</a>
        </article>
    </section>
@endsection

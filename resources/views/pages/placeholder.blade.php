@extends('layouts.app')

@section('title', "AROSOFT | {$title}")

@section('content')
    <section class="shell-card rounded-3xl p-8 sm:p-10 lg:p-12">
        <p class="text-[0.7rem] uppercase tracking-[0.24em] muted-faint">{{ $title }}</p>
        <h1 class="mt-4 text-3xl sm:text-5xl">{{ $heading }}</h1>
        <p class="mt-4 max-w-3xl text-sm sm:text-base muted-copy">{{ $copy }}</p>
    </section>
@endsection

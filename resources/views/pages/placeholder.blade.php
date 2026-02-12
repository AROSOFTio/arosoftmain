@extends('layouts.app')

@section('title', "AROSOFT | {$title}")
@section('meta_description', $copy)
@section('canonical', url()->current())

@section('content')
    <section class="shell-card rounded-3xl p-8 sm:p-10 lg:p-12">
        <p class="page-kicker">{{ $title }}</p>
        <h1 class="page-title mt-4">{{ $heading }}</h1>
        <p class="section-copy mt-4 max-w-3xl">{{ $copy }}</p>
    </section>
@endsection

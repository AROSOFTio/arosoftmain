@extends('admin.layouts.app')

@section('title', 'Create Post')

@section('content')
    <div class="mb-6">
        <p class="page-kicker">Blog management</p>
        <h1 class="font-heading text-3xl">Create post</h1>
    </div>

    @include('admin.blog.posts._form')
@endsection


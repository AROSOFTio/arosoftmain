@extends('admin.layouts.app')

@section('title', 'Edit Post')

@section('content')
    <div class="mb-6">
        <p class="page-kicker">Blog management</p>
        <h1 class="font-heading text-3xl">Edit post</h1>
        <p class="mt-2 text-sm muted-copy">{{ $post->title }}</p>
    </div>

    @include('admin.blog.posts._form')
@endsection


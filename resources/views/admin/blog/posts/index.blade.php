@extends('admin.layouts.app')

@section('title', 'Posts')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="page-kicker">Blog management</p>
            <h1 class="font-heading text-3xl">Posts</h1>
        </div>
        <a href="{{ route('admin.blog.posts.create') }}" class="btn-solid !w-auto !px-4 !py-2 !text-[0.68rem]">Create Post</a>
    </div>

    <section class="admin-card p-5">
        <form method="get" class="grid gap-3 md:grid-cols-5">
            <div>
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-field">
                    <option value="">All</option>
                    <option value="draft" @selected($filters['status'] === 'draft')>Draft</option>
                    <option value="published" @selected($filters['status'] === 'published')>Published</option>
                    <option value="scheduled" @selected($filters['status'] === 'scheduled')>Scheduled</option>
                </select>
            </div>
            <div>
                <label for="category" class="form-label">Category</label>
                <select id="category" name="category" class="form-field">
                    <option value="">All</option>
                    @foreach($categories as $category)
                        @php $depth = (int) ($category->depth ?? 0); @endphp
                        <option value="{{ $category->id }}" @selected((string) $category->id === $filters['category'])>
                            {{ str_repeat('-- ', $depth) }}{{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="author" class="form-label">Author</label>
                <select id="author" name="author" class="form-field">
                    <option value="">All</option>
                    @foreach($authors as $author)
                        <option value="{{ $author->id }}" @selected((string) $author->id === $filters['author'])>
                            {{ $author->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label for="search" class="form-label">Search</label>
                <div class="flex gap-2">
                    <input
                        id="search"
                        type="search"
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="Title, slug, excerpt"
                        class="form-field"
                    >
                    <button type="submit" class="btn-outline !w-auto !px-4 !py-2 !text-[0.68rem]">Filter</button>
                </div>
            </div>
        </form>
    </section>

    <section class="admin-card mt-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-[color:rgba(17,24,39,0.03)] text-left">
                        <th class="px-5 py-3 font-semibold">Title</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold">Category</th>
                        <th class="px-5 py-3 font-semibold">Author</th>
                        <th class="px-5 py-3 font-semibold">Published</th>
                        <th class="px-5 py-3 font-semibold">Views</th>
                        <th class="px-5 py-3 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                        <tr class="border-t border-[color:rgba(17,24,39,0.08)]">
                            <td class="px-5 py-3">
                                <div class="font-semibold">{{ $post->title }}</div>
                                @if($post->is_featured)
                                    <span class="mt-1 inline-flex rounded-full border border-[color:rgba(0,157,49,0.42)] px-2 py-0.5 text-[0.62rem] font-semibold uppercase tracking-[0.08em] text-[color:rgba(0,157,49,0.98)]">
                                        Featured
                                    </span>
                                @endif
                                <div class="text-xs muted-faint">{{ $post->slug }}</div>
                            </td>
                            <td class="px-5 py-3">{{ ucfirst($post->status) }}</td>
                            <td class="px-5 py-3">{{ $post->category?->name ?: '-' }}</td>
                            <td class="px-5 py-3">{{ $post->author?->name ?: '-' }}</td>
                            <td class="px-5 py-3">{{ optional($post->published_at)->format('M d, Y H:i') ?: '-' }}</td>
                            <td class="px-5 py-3">{{ number_format((int) $post->view_count) }}</td>
                            <td class="px-5 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('admin.blog.posts.edit', $post) }}" class="nav-link-sm">Edit</a>
                                    <a href="{{ route('admin.blog.posts.preview', $post) }}" class="nav-link-sm" target="_blank">Preview</a>
                                    <form action="{{ route('admin.blog.posts.destroy', $post) }}" method="post" onsubmit="return confirm('Delete this post?');">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="nav-link-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-5 muted-faint">No posts found for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-[color:rgba(17,24,39,0.08)] px-5 py-4">
            {{ $posts->links() }}
        </div>
    </section>
@endsection

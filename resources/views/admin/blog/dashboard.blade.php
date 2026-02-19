@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="page-kicker">Blog management</p>
            <h1 class="font-heading text-3xl">Dashboard</h1>
        </div>
        <a href="{{ route('admin.blog.posts.create') }}" class="btn-solid !w-auto !px-4 !py-2 !text-[0.68rem]">New Post</a>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <article class="admin-card p-5">
            <p class="text-xs uppercase tracking-[0.12em] muted-faint">Total posts</p>
            <p class="mt-2 font-heading text-3xl">{{ number_format($metrics['total_posts']) }}</p>
        </article>
        <article class="admin-card p-5">
            <p class="text-xs uppercase tracking-[0.12em] muted-faint">Published live</p>
            <p class="mt-2 font-heading text-3xl">{{ number_format($metrics['published_posts']) }}</p>
        </article>
        <article class="admin-card p-5">
            <p class="text-xs uppercase tracking-[0.12em] muted-faint">Drafts</p>
            <p class="mt-2 font-heading text-3xl">{{ number_format($metrics['draft_posts']) }}</p>
        </article>
        <article class="admin-card p-5">
            <p class="text-xs uppercase tracking-[0.12em] muted-faint">Queued publish</p>
            <p class="mt-2 font-heading text-3xl">{{ number_format($metrics['scheduled_posts']) }}</p>
        </article>
        <article class="admin-card p-5">
            <p class="text-xs uppercase tracking-[0.12em] muted-faint">Total views</p>
            <p class="mt-2 font-heading text-3xl">{{ number_format($metrics['total_views']) }}</p>
        </article>
    </section>

    <section class="admin-card mt-6 overflow-hidden">
        <div class="border-b border-[color:rgba(17,24,39,0.1)] px-5 py-4">
            <h2 class="font-heading text-xl">Recent posts</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-[color:rgba(17,24,39,0.03)] text-left">
                        <th class="px-5 py-3 font-semibold">Title</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold">Author</th>
                        <th class="px-5 py-3 font-semibold">Published</th>
                        <th class="px-5 py-3 font-semibold">Views</th>
                        <th class="px-5 py-3 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPosts as $post)
                        <tr class="border-t border-[color:rgba(17,24,39,0.08)]">
                            <td class="px-5 py-3">
                                <div class="font-semibold">{{ $post->title }}</div>
                                <div class="text-xs muted-faint">{{ $post->slug }}</div>
                            </td>
                            <td class="px-5 py-3">{{ $post->status === 'draft' ? 'Draft' : 'Published' }}</td>
                            <td class="px-5 py-3">{{ $post->author?->name ?? '-' }}</td>
                            <td class="px-5 py-3">{{ optional($post->published_at)->format('M d, Y H:i') ?: '-' }}</td>
                            <td class="px-5 py-3">{{ number_format((int) $post->view_count) }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.blog.posts.edit', $post) }}" class="nav-link-sm">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-5 muted-faint">No posts created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

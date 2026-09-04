@extends('layouts.app')

@section('title', 'AROMOTION Cloud Account | AROSOFT Labs')
@section('meta_description', 'Create or sign in to your AROMOTION Cloud account to download AROMOTION Studio and manage connected devices and projects.')
@section('canonical', route('aromotion.account'))

@section('content')
    <section class="mx-auto max-w-6xl overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-xl">
        <div class="grid lg:grid-cols-[.85fr_1.15fr]">
            <div class="bg-[#070b16] p-8 text-white sm:p-10 lg:p-12">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-violet-500 to-sky-400 font-black">AM</div>
                <p class="mt-8 text-xs font-bold uppercase tracking-[0.18em] text-sky-300">AROMOTION Cloud</p>
                <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">One account for your recorder, devices and projects.</h1>
                <p class="mt-4 text-sm leading-7 text-slate-300">Sign in on the web, activate the Windows app, download updates and keep your project metadata connected to AROSOFT Labs.</p>
                <div class="mt-8 space-y-3 text-sm text-slate-300">
                    @foreach(['Secure desktop activation','Windows build downloads','Connected-device visibility','Project metadata sync','Central version and support status'] as $item)
                        <div class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-emerald-400"></span>{{ $item }}</div>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-8 p-8 sm:p-10 lg:grid-cols-2 lg:p-12">
                <div>
                    <h2 class="text-2xl font-black text-slate-950">Sign in</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Continue to your AROMOTION workspace.</p>
                    <form method="POST" action="{{ route('aromotion.login') }}" class="mt-6 space-y-4">
                        @csrf
                        <div><label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600">Email</label><input name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100"></div>
                        <div><label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600">Password</label><input name="password" type="password" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100"></div>
                        <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remember" value="1" class="rounded"> Keep me signed in</label>
                        @error('email')<p class="text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                        <button class="w-full rounded-xl bg-slate-950 px-5 py-3 text-sm font-black text-white transition hover:bg-violet-700">Sign in to AROMOTION</button>
                    </form>
                </div>

                <div class="border-t border-slate-200 pt-8 lg:border-l lg:border-t-0 lg:pl-8 lg:pt-0">
                    <h2 class="text-2xl font-black text-slate-950">Create account</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Create your AROSOFT Labs product account and get access to the Windows download.</p>
                    <form method="POST" action="{{ route('aromotion.register') }}" class="mt-6 space-y-4">
                        @csrf
                        <div><label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600">Full name</label><input name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100">@error('name')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror</div>
                        <div><label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600">Email</label><input name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100"></div>
                        <div><label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600">Password</label><input name="password" type="password" minlength="8" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100"></div>
                        <div><label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-600">Confirm password</label><input name="password_confirmation" type="password" minlength="8" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-100"></div>
                        <button class="w-full rounded-xl bg-gradient-to-r from-violet-600 to-sky-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-violet-200 transition hover:-translate-y-0.5">Create AROMOTION account</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

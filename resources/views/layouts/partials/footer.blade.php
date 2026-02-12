<footer class="footer-texture mt-24 border-t border-[color:rgba(10,102,255,0.2)]">
    <div class="mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-10 md:grid-cols-2 xl:grid-cols-4">
            <section>
                <p class="font-heading text-2xl tracking-[0.2em]">AROSOFT</p>
                <p class="mt-3 max-w-xs text-sm muted-copy">
                    Arosoft Innovations builds practical digital products, websites, and technical systems for modern businesses.
                </p>

                <div class="mt-6">
                    <p class="text-[0.64rem] uppercase tracking-[0.2em] muted-faint">Newsletter</p>
                    <div class="mt-2 flex items-center gap-2">
                        <input type="email" class="search-input !px-3 !py-2 text-sm" placeholder="Email address (mock)">
                        <button type="button" class="btn-solid">Join</button>
                    </div>
                </div>
            </section>

            <section>
                <p class="text-[0.68rem] uppercase tracking-[0.2em] muted-faint">Services</p>
                <div class="mt-4 space-y-2">
                    <a href="{{ route('services.printing') }}" class="nav-link-sm inline-flex">Printing</a><br>
                    <a href="{{ route('services.website-design') }}" class="nav-link-sm inline-flex">Website Design</a><br>
                    <a href="{{ route('services.web-development') }}" class="nav-link-sm inline-flex">Web Development</a><br>
                    <a href="{{ route('services.training-courses') }}" class="nav-link-sm inline-flex">Training/Courses</a>
                </div>
            </section>

            <section>
                <p class="text-[0.68rem] uppercase tracking-[0.2em] muted-faint">Resources</p>
                <div class="mt-4 space-y-2">
                    <a href="{{ route('blog') }}" class="nav-link-sm inline-flex">Blog</a><br>
                    <a href="{{ route('tutorials') }}" class="nav-link-sm inline-flex">Tutorials/Videos</a><br>
                    <a href="{{ route('tools') }}" class="nav-link-sm inline-flex">IT Tools</a><br>
                    <a href="{{ route('privacy') }}" class="nav-link-sm inline-flex">Privacy</a>
                </div>
            </section>

            <section>
                <p class="text-[0.68rem] uppercase tracking-[0.2em] muted-faint">Contact</p>
                <div class="mt-4 space-y-2 text-sm muted-copy">
                    <p>Arosoft Innovations Ltd</p>
                    <p>Innovation District, Lagos, Nigeria</p>
                    <p>contact@arosoft.io</p>
                </div>

                <div class="mt-5">
                    @include('layouts.partials.social-icons', [
                        'containerClass' => 'flex items-center gap-2',
                        'iconClass' => 'icon-button',
                    ])
                </div>
            </section>
        </div>
    </div>

    <div class="border-t border-[color:rgba(10,102,255,0.16)]">
        <div class="mx-auto flex w-full max-w-7xl flex-col gap-2 px-4 py-5 text-[0.66rem] uppercase tracking-[0.14em] muted-faint sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p>&copy; {{ date('Y') }} Arosoft Innovations. All rights reserved.</p>
            <p>Built by Arosoft Innovations Ltd</p>
        </div>
    </div>
</footer>


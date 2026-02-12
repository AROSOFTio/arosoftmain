<footer class="footer-texture mt-24 border-t border-[color:rgba(19,242,198,0.24)]">
    <div class="mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-10 md:grid-cols-2 xl:grid-cols-4">
            <section>
                <p class="font-heading text-2xl tracking-[0.2em] text-[var(--ink)]">AROSOFT</p>
                <p class="mt-4 max-w-xs text-base leading-8 muted-copy">
                    Arosoft Innovations Ltd delivers dependable IT and creative services for businesses and institutions in Kampala and beyond.
                </p>

                <div class="mt-6">
                    <p class="text-[0.68rem] uppercase tracking-[0.2em] muted-faint">Newsletter</p>
                    <div class="mt-3 flex items-center gap-2">
                        <input type="email" class="search-input !px-3 !py-2.5 text-sm" placeholder="Email address (mock)">
                        <button type="button" class="btn-solid">Join</button>
                    </div>
                </div>
            </section>

            <section>
                <p class="text-[0.7rem] uppercase tracking-[0.2em] muted-faint">Services</p>
                <div class="mt-4 space-y-2">
                    <a href="{{ route('services.printing') }}" class="nav-link-sm inline-flex">Printing</a><br>
                    <a href="{{ route('services.website-design') }}" class="nav-link-sm inline-flex">Website Design</a><br>
                    <a href="{{ route('services.web-development') }}" class="nav-link-sm inline-flex">Web Development</a><br>
                    <a href="{{ route('services.training-courses') }}" class="nav-link-sm inline-flex">Training/Courses</a><br>
                    <a href="{{ route('services') }}" class="nav-link-sm inline-flex">System Development</a>
                </div>
            </section>

            <section>
                <p class="text-[0.7rem] uppercase tracking-[0.2em] muted-faint">Resources</p>
                <div class="mt-4 space-y-2">
                    <a href="{{ route('blog') }}" class="nav-link-sm inline-flex">Blog</a><br>
                    <a href="{{ route('tutorials') }}" class="nav-link-sm inline-flex">Tutorials/Videos</a><br>
                    <a href="{{ route('tools') }}" class="nav-link-sm inline-flex">IT Tools</a><br>
                    <a href="{{ route('about') }}" class="nav-link-sm inline-flex">About</a><br>
                    <a href="{{ route('privacy') }}" class="nav-link-sm inline-flex">Privacy</a>
                </div>
            </section>

            <section>
                <p class="text-[0.7rem] uppercase tracking-[0.2em] muted-faint">Contact</p>
                <div class="mt-4 space-y-2 text-sm leading-7 muted-copy">
                    <p>Arosoft Innovations Ltd</p>
                    <p>Kitintale Road, Opposite St. Johns C.O.U, next to Sir Appolo Kaggwa St. School</p>
                    <p><a href="mailto:info@arosoft.io" class="underline decoration-transparent transition hover:decoration-[var(--accent)]">info@arosoft.io</a></p>
                    <p><a href="https://wa.me/256787726388" class="underline decoration-transparent transition hover:decoration-[var(--accent)]">+256787726388</a></p>
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

    <div class="border-t border-[color:rgba(19,242,198,0.2)]">
        <div class="mx-auto flex w-full max-w-7xl flex-col gap-2 px-4 py-5 text-[0.66rem] uppercase tracking-[0.14em] muted-faint sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p>&copy; {{ date('Y') }} Arosoft Innovations Ltd. All rights reserved.</p>
            <p>Built by Arosoft Innovations Ltd</p>
        </div>
    </div>
</footer>


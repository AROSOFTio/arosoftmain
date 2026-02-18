<footer class="mt-24">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="footer-panel footer-texture rounded-3xl px-6 py-14 sm:px-10 sm:py-16">
            <div class="grid gap-10 md:grid-cols-2 xl:grid-cols-4">
                <section>
                    <a href="{{ route('home') }}" class="inline-flex brand-logo-wrap footer-brand-logo-wrap">
                        <img src="{{ asset('brand/logo-full.svg') }}" alt="Arosoft Innovations Ltd" class="brand-logo-full footer-brand-logo-full">
                    </a>
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
                    <div class="mt-4 space-y-1.5">
                        <a href="{{ route('services.printing') }}" class="inline-block text-sm muted-copy transition hover:text-white">Printing</a><br>
                        <a href="{{ route('services.website-design') }}" class="inline-block text-sm muted-copy transition hover:text-white">Website Design</a><br>
                        <a href="{{ route('services.web-development') }}" class="inline-block text-sm muted-copy transition hover:text-white">Web Development</a><br>
                        <a href="{{ route('services.training-courses') }}" class="inline-block text-sm muted-copy transition hover:text-white">Training/Courses</a><br>
                        <a href="{{ route('services') }}" class="inline-block text-sm muted-copy transition hover:text-white">System Development</a>
                    </div>
                </section>

                <section>
                    <p class="text-[0.7rem] uppercase tracking-[0.2em] muted-faint">Resources</p>
                    <div class="mt-4 space-y-1.5">
                        <a href="{{ route('blog') }}" class="inline-block text-sm muted-copy transition hover:text-white">Blog</a><br>
                        <a href="{{ route('tutorials') }}" class="inline-block text-sm muted-copy transition hover:text-white">Tutorials</a><br>
                        <a href="{{ route('tools') }}" class="inline-block text-sm muted-copy transition hover:text-white">Tools</a><br>
                        <a href="{{ route('about') }}" class="inline-block text-sm muted-copy transition hover:text-white">About</a><br>
                        <a href="{{ route('privacy') }}" class="inline-block text-sm muted-copy transition hover:text-white">Privacy</a>
                    </div>
                </section>

                <section>
                    <p class="text-[0.7rem] uppercase tracking-[0.2em] muted-faint">Contact</p>
                    <div class="mt-4 space-y-2 text-sm leading-7 muted-copy">
                        <p>Arosoft Innovations Ltd</p>
                        <p>Kitintale Road, Opposite St. Johns C.O.U, next to Sir Appolo Kaggwa St. School</p>
                        <p><a href="mailto:info@arosoft.io" class="underline decoration-transparent transition hover:decoration-white">info@arosoft.io</a></p>
                        <p><a href="https://wa.me/256787726388" class="underline decoration-transparent transition hover:decoration-white">+256787726388</a></p>
                    </div>

                    <div class="mt-5">
                        @include('layouts.partials.social-icons', [
                            'containerClass' => 'flex items-center gap-2',
                            'iconClass' => 'icon-button',
                        ])
                    </div>
                </section>
            </div>

            <div class="mt-12 border-t border-[color:rgba(255,255,255,0.18)] pt-5">
                <div class="flex flex-col gap-2 text-[0.66rem] uppercase tracking-[0.14em] muted-faint sm:flex-row sm:items-center sm:justify-between">
                    <p>&copy; {{ date('Y') }} Arosoft Innovations Ltd. All rights reserved.</p>
                    <p>Built by Arosoft Innovations Ltd</p>
                </div>
            </div>
        </div>
    </div>
</footer>


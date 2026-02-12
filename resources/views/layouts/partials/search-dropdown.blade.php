<div
    x-cloak
    x-show="showSearch"
    x-transition:enter="transition duration-150 ease-out"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition duration-120 ease-in"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    class="mega-panel absolute left-0 top-[calc(100%+0.65rem)] z-[70] w-full overflow-hidden p-3"
>
    <template x-for="section in searchSections" :key="section">
        <section class="mb-3 last:mb-0" x-show="searchResults[section].length > 0">
            <p class="mb-2 px-2 text-[0.62rem] uppercase tracking-[0.2em] muted-faint" x-text="section"></p>

            <div class="space-y-1">
                <template x-for="item in searchResults[section]" :key="item.title + item.url">
                    <a
                        :href="item.url"
                        @click="clearSearch()"
                        class="group flex items-center justify-between rounded-lg border border-[color:rgba(10,102,255,0.12)] px-3 py-2 transition duration-200 hover:border-[color:rgba(10,102,255,0.42)] hover:bg-[color:rgba(10,102,255,0.08)]"
                    >
                        <span class="text-sm text-[color:rgba(10,102,255,0.92)]" x-text="item.title"></span>
                        <span class="text-[0.64rem] uppercase tracking-[0.16em] muted-faint" x-text="item.meta"></span>
                    </a>
                </template>
            </div>
        </section>
    </template>

    <p x-show="showSearch && !hasSearchMatches" class="px-2 py-2 text-sm muted-faint">
        No results yet. Try another keyword.
    </p>
</div>


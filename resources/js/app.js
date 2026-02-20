import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.siteShell = () => ({
    offcanvasOpen: false,
    mobileSearchOpen: false,
    megaOpen: null,
    scrolled: false,
    searchQuery: '',
    showSearch: false,
    hasSearchMatches: false,
    previousActiveElement: null,
    searchSections: ['Blog', 'Services', 'Courses', 'Tutorials', 'Tools', 'Pages'],
    searchAbortController: null,
    searchRequestSequence: 0,
    videos: Array.isArray(window.siteTutorialVideos) ? window.siteTutorialVideos : [],
    playlists: Array.isArray(window.siteTutorialPlaylists) ? window.siteTutorialPlaylists : [],
    searchResults: {
        Blog: [],
        Services: [],
        Courses: [],
        Tutorials: [],
        Tools: [],
        Pages: [],
    },

    init() {
        this.syncHeaderState();

        window.addEventListener(
            'scroll',
            () => {
                this.syncHeaderState();
            },
            { passive: true }
        );

        window.addEventListener('resize', () => {
            if (window.innerWidth < 1024) {
                this.megaOpen = null;
            }
        });
    },

    syncHeaderState() {
        this.scrolled = window.scrollY > 14;
    },

    openMega(name) {
        if (window.innerWidth >= 1024) {
            this.megaOpen = name;
        }
    },

    closeMega(target = null) {
        if (!target || this.megaOpen === target) {
            this.megaOpen = null;
        }
    },

    toggleMega(name) {
        this.megaOpen = this.megaOpen === name ? null : name;
    },

    openOffcanvas() {
        this.previousActiveElement = document.activeElement;
        this.offcanvasOpen = true;
        document.body.classList.add('overflow-hidden');

        this.$nextTick(() => {
            if (this.$refs.offcanvasPanel) {
                this.$refs.offcanvasPanel.focus();
            }
        });
    },

    closeOffcanvas() {
        this.offcanvasOpen = false;
        document.body.classList.remove('overflow-hidden');

        this.$nextTick(() => {
            if (this.previousActiveElement && typeof this.previousActiveElement.focus === 'function') {
                this.previousActiveElement.focus();
            }
        });
    },

    trapOffcanvasFocus(event) {
        if (!this.offcanvasOpen || !this.$refs.offcanvasPanel) {
            return;
        }

        const focusables = Array.from(
            this.$refs.offcanvasPanel.querySelectorAll(
                'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])'
            )
        );

        if (!focusables.length) {
            return;
        }

        const currentIndex = focusables.indexOf(document.activeElement);
        if (currentIndex === -1) {
            focusables[0].focus();
            return;
        }

        let nextIndex = currentIndex + (event.shiftKey ? -1 : 1);
        if (nextIndex < 0) {
            nextIndex = focusables.length - 1;
        }
        if (nextIndex >= focusables.length) {
            nextIndex = 0;
        }

        focusables[nextIndex].focus();
    },

    toggleMobileSearch() {
        this.mobileSearchOpen = !this.mobileSearchOpen;
        if (!this.mobileSearchOpen) {
            this.closeSearch();
            return;
        }

        this.$nextTick(() => {
            if (this.$refs.mobileSearchInput) {
                this.$refs.mobileSearchInput.focus();
            }
        });
    },

    freshSearchGroups() {
        return {
            Blog: [],
            Services: [],
            Courses: [],
            Tutorials: [],
            Tools: [],
            Pages: [],
        };
    },

    cancelSearchRequest() {
        if (this.searchAbortController) {
            this.searchAbortController.abort();
            this.searchAbortController = null;
        }
    },

    focusSearch() {
        this.showSearch = this.searchQuery.trim().length > 0;
    },

    closeSearch() {
        this.showSearch = false;
    },

    clearSearch() {
        this.cancelSearchRequest();
        this.searchQuery = '';
        this.showSearch = false;
        this.searchResults = this.freshSearchGroups();
        this.hasSearchMatches = false;
    },

    async fetchSuggestions(query) {
        this.cancelSearchRequest();
        const controller = new AbortController();
        this.searchAbortController = controller;

        try {
            const response = await fetch(`/search/suggestions?q=${encodeURIComponent(query)}`, {
                headers: {
                    Accept: 'application/json',
                },
                signal: controller.signal,
            });

            if (!response.ok) {
                return [];
            }

            const payload = await response.json();
            return Array.isArray(payload.results) ? payload.results : [];
        } finally {
            if (this.searchAbortController === controller) {
                this.searchAbortController = null;
            }
        }
    },

    async updateSearch() {
        const query = this.searchQuery.trim();
        const normalizedQuery = query.toLowerCase();
        const requestId = ++this.searchRequestSequence;

        if (!normalizedQuery) {
            this.cancelSearchRequest();
            this.searchResults = this.freshSearchGroups();
            this.hasSearchMatches = false;
            this.showSearch = false;
            return;
        }

        const grouped = this.freshSearchGroups();

        try {
            const results = await this.fetchSuggestions(query);

            results.forEach((item) => {
                if (!item || !grouped[item.type]) {
                    return;
                }

                grouped[item.type].push(item);
            });
        } catch (error) {
            if (error?.name !== 'AbortError') {
                this.searchResults = this.freshSearchGroups();
                this.hasSearchMatches = false;
                this.showSearch = true;
            } else {
                return;
            }
        }

        if (requestId !== this.searchRequestSequence) {
            return;
        }

        this.searchSections.forEach((section) => {
            grouped[section] = grouped[section].slice(0, 4);
        });

        this.searchResults = grouped;
        this.hasSearchMatches = this.searchSections.some((section) => grouped[section].length > 0);
        this.showSearch = true;
    },

    handleEscape() {
        this.closeSearch();
        this.closeMega();
        this.mobileSearchOpen = false;

        if (this.offcanvasOpen) {
            this.closeOffcanvas();
        }
    },
});

const loadTinyMceForBlogEditor = async () => {
    if (!document.getElementById('body-editor')) {
        return;
    }

    if (window.tinymce) {
        document.dispatchEvent(new CustomEvent('tinymce:ready'));
        return;
    }

    try {
        const tinymceModule = await import('tinymce/tinymce');

        await Promise.all([
            import('tinymce/icons/default'),
            import('tinymce/themes/silver'),
            import('tinymce/models/dom'),
            import('tinymce/plugins/advlist'),
            import('tinymce/plugins/autolink'),
            import('tinymce/plugins/lists'),
            import('tinymce/plugins/link'),
            import('tinymce/plugins/image'),
            import('tinymce/plugins/table'),
            import('tinymce/plugins/code'),
            import('tinymce/plugins/media'),
            import('tinymce/plugins/charmap'),
            import('tinymce/plugins/searchreplace'),
            import('tinymce/plugins/visualblocks'),
            import('tinymce/plugins/wordcount'),
            import('tinymce/plugins/fullscreen'),
            import('tinymce/skins/ui/oxide/skin.min.css'),
            import('tinymce/skins/content/default/content.min.css'),
        ]);

        window.tinymce = tinymceModule.default || tinymceModule;
        document.dispatchEvent(new CustomEvent('tinymce:ready'));
    } catch (error) {
        console.error('Failed to load TinyMCE locally.', error);
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        loadTinyMceForBlogEditor();
    }, { once: true });
} else {
    loadTinyMceForBlogEditor();
}

Alpine.start();

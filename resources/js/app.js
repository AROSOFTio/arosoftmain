import './bootstrap';
import Alpine from 'alpinejs';
import { searchIndex, videos } from './mockData';

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
    searchSections: ['Services', 'Tutorials', 'Blog', 'Pages'],
    searchAbortController: null,
    searchRequestSequence: 0,
    videos,
    searchIndex,
    searchResults: {
        Services: [],
        Tutorials: [],
        Blog: [],
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
            Services: [],
            Tutorials: [],
            Blog: [],
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

    async fetchBlogResults(query) {
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
            return Array.isArray(payload.blog) ? payload.blog : [];
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

        this.searchIndex.forEach((item) => {
            const text = `${item.title} ${item.meta}`.toLowerCase();
            if (!text.includes(normalizedQuery) || !grouped[item.type]) {
                return;
            }

            grouped[item.type].push(item);
        });

        try {
            grouped.Blog = (await this.fetchBlogResults(query)).slice(0, 4);
        } catch (error) {
            if (error?.name !== 'AbortError') {
                grouped.Blog = [];
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

Alpine.start();

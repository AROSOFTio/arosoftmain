@php
    $containerClass = $containerClass ?? 'flex items-center gap-2';
    $iconClass = $iconClass ?? 'icon-button';
@endphp

<div class="{{ $containerClass }}">
    <a href="https://youtube.com" class="{{ $iconClass }}" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4" aria-hidden="true">
            <path d="M22 12.012c0-2.087-.229-3.405-.542-4.195a2.97 2.97 0 0 0-1.752-1.752C18.916 5.75 17.598 5.52 15.511 5.52h-7.02c-2.087 0-3.406.23-4.195.543a2.97 2.97 0 0 0-1.752 1.752C2.23 8.607 2 9.925 2 12.012c0 2.087.23 3.405.543 4.195a2.97 2.97 0 0 0 1.752 1.752c.79.313 2.108.543 4.195.543h7.02c2.087 0 3.405-.23 4.195-.543a2.97 2.97 0 0 0 1.752-1.752c.313-.79.543-2.108.543-4.195Z" stroke="currentColor" stroke-width="1.4"/>
            <path d="M10.2 9.15v5.72l4.86-2.86-4.86-2.86Z" fill="currentColor"/>
        </svg>
    </a>
    <a href="https://facebook.com" class="{{ $iconClass }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4" aria-hidden="true">
            <path d="M13.24 21v-7.03h2.36l.35-2.74h-2.7v-1.75c0-.79.22-1.33 1.35-1.33h1.44V5.7a19.1 19.1 0 0 0-2.1-.11c-2.07 0-3.5 1.27-3.5 3.6v2.04H8.12v2.74h2.32V21h2.8Z" fill="currentColor"/>
        </svg>
    </a>
    <a href="https://x.com" class="{{ $iconClass }}" target="_blank" rel="noopener noreferrer" aria-label="X">
        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4" aria-hidden="true">
            <path d="M18.52 3h2.94l-6.44 7.35L22.6 21h-5.93l-4.64-6.08L6.7 21H3.75l6.9-7.89L3.4 3h6.08l4.2 5.54L18.52 3Zm-1.04 16.22h1.64L8.6 4.68H6.84l10.64 14.54Z" fill="currentColor"/>
        </svg>
    </a>
    <a href="https://instagram.com" class="{{ $iconClass }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4" aria-hidden="true">
            <rect x="4" y="4" width="16" height="16" rx="4" stroke="currentColor" stroke-width="1.4"/>
            <circle cx="12" cy="12" r="3.4" stroke="currentColor" stroke-width="1.4"/>
            <circle cx="16.9" cy="7.3" r="1" fill="currentColor"/>
        </svg>
    </a>
    <a href="https://linkedin.com" class="{{ $iconClass }}" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4" aria-hidden="true">
            <path d="M7.1 9.2H3.8V20h3.3V9.2Zm.22-3.34c0-1.03-.75-1.86-1.87-1.86-1.08 0-1.87.83-1.87 1.86 0 1 .75 1.86 1.84 1.86h.03c1.13 0 1.87-.86 1.87-1.86ZM20.2 13.8c0-3.19-1.7-4.67-3.96-4.67-1.82 0-2.64 1.01-3.1 1.72v-1.47H9.85c.04.97 0 10.62 0 10.62h3.29v-5.93c0-.32.02-.64.12-.86.26-.64.86-1.3 1.86-1.3 1.31 0 1.84.98 1.84 2.42V20h3.24v-6.2Z" fill="currentColor"/>
        </svg>
    </a>
    <a href="https://wa.me/256787726388" class="{{ $iconClass }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4" aria-hidden="true">
            <path d="M12 3.2a8.8 8.8 0 0 0-7.58 13.2L3 21l4.74-1.35A8.8 8.8 0 1 0 12 3.2Z" stroke="currentColor" stroke-width="1.4"/>
            <path d="M8.9 7.8c-.24-.53-.5-.54-.74-.55-.2 0-.43 0-.66 0-.23 0-.6.08-.9.4-.31.34-1.2 1.17-1.2 2.85s1.23 3.3 1.4 3.53c.16.22 2.37 3.8 5.84 5.16 2.88 1.12 3.47.9 4.1.84.62-.06 2-.82 2.28-1.62.29-.8.29-1.48.2-1.62-.08-.14-.31-.22-.66-.4-.34-.17-2-.99-2.32-1.1-.31-.1-.54-.17-.77.18-.23.34-.88 1.1-1.08 1.32-.2.22-.4.25-.74.08-.35-.18-1.47-.55-2.8-1.76a10.28 10.28 0 0 1-1.95-2.42c-.2-.34-.02-.52.15-.7.16-.16.34-.4.51-.6.17-.2.23-.34.34-.57.12-.22.06-.42-.03-.59-.1-.17-.76-1.9-1.06-2.6Z" fill="currentColor"/>
        </svg>
    </a>
</div>


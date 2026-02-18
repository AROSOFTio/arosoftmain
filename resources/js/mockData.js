const makeThumb = (label) => {
    const svg = `
        <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 320 180'>
            <rect width='320' height='180' fill='#FFFFFF'/>
            <path d='M0 132L320 42V180H0z' fill='#009D31' fill-opacity='0.18'/>
            <circle cx='258' cy='52' r='22' fill='#009D31' fill-opacity='0.36'/>
            <text x='20' y='34' fill='#009D31' fill-opacity='0.88' font-size='18' font-family='Montserrat'>${label}</text>
        </svg>
    `;

    return `data:image/svg+xml;utf8,${encodeURIComponent(svg)}`;
};

export const videos = [
    {
        title: 'Launch a Production-Ready Company Website',
        url: '/tutorials',
        thumb: makeThumb('Video 01'),
        date: 'Jan 20, 2026',
    },
    {
        title: 'Design to Code: Tech Landing Pages',
        url: '/tutorials',
        thumb: makeThumb('Video 02'),
        date: 'Jan 13, 2026',
    },
    {
        title: 'SEO Checklist for Service Businesses',
        url: '/tutorials',
        thumb: makeThumb('Video 03'),
        date: 'Jan 06, 2026',
    },
    {
        title: 'Faster Frontends with Tailwind + Vite',
        url: '/tutorials',
        thumb: makeThumb('Video 04'),
        date: 'Dec 29, 2025',
    },
    {
        title: 'Deploying Laravel Projects on aaPanel',
        url: '/tutorials',
        thumb: makeThumb('Video 05'),
        date: 'Dec 21, 2025',
    },
    {
        title: 'From Brief to Build in 48 Hours',
        url: '/tutorials',
        thumb: makeThumb('Video 06'),
        date: 'Dec 14, 2025',
    },
];

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-menu-toggle]');
    if (!toggle) return;

    const menu = document.querySelector('[data-mobile-menu]');
    if (!menu) return;

    const isOpen = menu.classList.toggle('hidden') === false;
    toggle.setAttribute('aria-expanded', String(isOpen));
    toggle.setAttribute('aria-label', isOpen ? 'Tutup menu' : 'Buka menu');
});

const prefetchedUrls = new Set();

const prefetchPage = (anchor) => {
    if (!anchor || anchor.target === '_blank' || anchor.hasAttribute('download')) return;

    const url = new URL(anchor.href, window.location.href);
    if (url.origin !== window.location.origin || prefetchedUrls.has(url.href)) return;

    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.as = 'document';
    link.href = url.href;
    document.head.appendChild(link);
    prefetchedUrls.add(url.href);
};

document.querySelectorAll('a[href]').forEach((anchor) => {
    anchor.addEventListener('pointerenter', () => prefetchPage(anchor), { once: true });
    anchor.addEventListener('focus', () => prefetchPage(anchor), { once: true });
});

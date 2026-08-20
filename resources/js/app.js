let revealObserver = null;

function getRevealObserver() {
    if (revealObserver) {
        return revealObserver;
    }

    revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    revealObserver.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -60px 0px' },
    );

    return revealObserver;
}

function revealScan(el = document) {
    const targets = [
        ...(el.matches?.('[data-reveal]:not(.is-revealed)') ? [el] : []),
        ...el.querySelectorAll('[data-reveal]:not(.is-revealed)'),
    ];

    if (!targets.length) {
        return;
    }

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        targets.forEach((node) => node.classList.add('is-revealed'));
        return;
    }

    const observer = getRevealObserver();
    targets.forEach((node) => observer.observe(node));
}

document.addEventListener('DOMContentLoaded', () => revealScan());
document.addEventListener('livewire:navigated', () => revealScan());

// Livewire's morph step re-syncs element attributes (including class) against
// the freshly server-rendered HTML on every AJAX update — e.g. picking a
// product option, filtering the shop grid, switching a tab — which strips
// the "is-revealed" class this script adds, even on elements it merely
// patches in place (no new DOM node, so a MutationObserver alone can't see
// it). Re-scan after every morph so nothing gets stuck invisible.
document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.added', ({ el }) => revealScan(el));
    Livewire.hook('morph.updated', ({ el }) => revealScan(el));
});

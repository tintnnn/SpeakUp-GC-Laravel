/**
 * Port of the `useInView` hook in frontend/src/views/Landing.tsx and
 * frontend/src/hooks/useScrollAnimation.ts.
 *
 * Elements with [data-inview] get the `is-visible` class (CSS in app.css handles the
 * animation) once they intersect (threshold 0.08, rootMargin "0px 0px -40px 0px").
 * Elements may also declare data-inview-class="opacity-100 translate-y-0" and
 * data-inview-hidden="opacity-0 translate-y-4" to swap utility classes like the
 * React implementation does for the About card.
 */
export function installScrollAnimations() {
    const els = Array.from(document.querySelectorAll('[data-inview]'));
    if (!els.length) return;

    const reveal = (el) => {
        el.classList.add('is-visible');
        const hidden = (el.dataset.inviewHidden || '').split(/\s+/).filter(Boolean);
        const shown = (el.dataset.inviewClass || '').split(/\s+/).filter(Boolean);
        hidden.forEach((c) => el.classList.remove(c));
        shown.forEach((c) => el.classList.add(c));
    };

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReduced || typeof IntersectionObserver === 'undefined') {
        els.forEach(reveal);
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    reveal(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.08, rootMargin: '0px 0px -40px 0px' }
    );
    els.forEach((el) => observer.observe(el));
}

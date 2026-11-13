import './bootstrap';
import './activity';
import './topology';

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const motionToggle = document.querySelector('[data-motion-toggle]');
let motionPaused = false;

try {
    motionPaused = localStorage.getItem('netcalc-motion-paused') === 'true';
} catch {
    // Motion controls also work when browser storage is unavailable.
}

const syncMotion = () => {
    const paused = motionPaused || reducedMotion.matches;
    document.documentElement.classList.toggle('motion-paused', paused);
    if (motionToggle) {
        motionToggle.setAttribute('aria-pressed', String(paused));
        motionToggle.textContent = reducedMotion.matches ? 'Gerakan dikurangi oleh sistem' : (paused ? 'Aktifkan animasi' : 'Jeda animasi');
        motionToggle.disabled = reducedMotion.matches;
    }
};

motionToggle?.addEventListener('click', () => {
    motionPaused = !motionPaused;
    try {
        localStorage.setItem('netcalc-motion-paused', String(motionPaused));
    } catch {
        // Keep the current preference for this page when storage is blocked.
    }
    syncMotion();
});
reducedMotion.addEventListener('change', syncMotion);
syncMotion();

document.querySelectorAll('[data-copy-target]').forEach((button) => {
    button.addEventListener('click', async () => {
        const target = document.getElementById(button.dataset.copyTarget);
        const status = button.closest('section').querySelector('[data-copy-status]');
        if (!target) return;

        try {
            await navigator.clipboard.writeText(target.textContent);
            status.textContent = 'Perintah berhasil disalin. Tempelkan ke CLI perangkat latihan.';
        } catch {
            const selection = window.getSelection();
            const range = document.createRange();
            range.selectNodeContents(target);
            selection.removeAllRanges();
            selection.addRange(range);
            status.textContent = 'Salin otomatis tidak tersedia. Perintah sudah dipilih; tekan Ctrl+C atau gunakan menu Salin.';
        }
    });
});

const shell = document.querySelector('[data-app-shell]');

if (shell) {
    const sidebar = shell.querySelector('#app-sidebar');
    const toggle = shell.querySelector('[data-sidebar-toggle]');
    const close = shell.querySelector('[data-sidebar-close]');
    const overlay = shell.querySelector('[data-sidebar-overlay]');
    const content = shell.querySelector('.app-content');
    const desktop = window.matchMedia('(min-width: 1024px)');
    let isOpen = desktop.matches;

    const setSidebar = (open, moveFocus = false) => {
        isOpen = open;
        const isDrawer = open && !desktop.matches;

        shell.classList.toggle('sidebar-open', open);
        shell.classList.toggle('sidebar-closed', !open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
        sidebar.inert = !open;
        overlay.hidden = !isDrawer;
        document.body.classList.toggle('sidebar-scroll-lock', isDrawer);

        if (isDrawer) {
            sidebar.setAttribute('role', 'dialog');
            sidebar.setAttribute('aria-modal', 'true');
        } else {
            sidebar.removeAttribute('role');
            sidebar.removeAttribute('aria-modal');
        }

        content.inert = isDrawer;

        if (moveFocus) {
            if (isDrawer) {
                close.focus();
            } else {
                toggle.focus();
            }
        }
    };

    toggle.addEventListener('click', () => setSidebar(!isOpen, true));
    close.addEventListener('click', () => setSidebar(false, true));
    overlay.addEventListener('click', () => setSidebar(false, true));

    document.addEventListener('keydown', (event) => {
        if (!isOpen) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            setSidebar(false, true);
        }

        if (event.key === 'Tab' && !desktop.matches) {
            const focusable = [...sidebar.querySelectorAll('a[href], button:not([disabled])')];
            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    desktop.addEventListener('change', () => {
        const focusWasInSidebar = sidebar.contains(document.activeElement);
        setSidebar(desktop.matches, focusWasInSidebar);
    });

    setSidebar(isOpen);
}

/**
 * Global loading overlay for form submits, same-origin navigation,
 * and mutating fetch/axios requests.
 */
const MUTATING_METHODS = new Set(['POST', 'PUT', 'PATCH', 'DELETE']);

let pendingCount = 0;
let overlayEl = null;
let messageEl = null;

function isArabic() {
    const lang = (document.documentElement.lang || '').toLowerCase();
    return lang.startsWith('ar');
}

function loadingMessage() {
    return isArabic() ? 'يرجى الانتظار' : 'Please wait';
}

function ensureOverlay() {
    if (overlayEl) {
        return overlayEl;
    }

    overlayEl = document.createElement('div');
    overlayEl.id = 'app-loading-overlay';
    overlayEl.className = 'app-loading-overlay';
    overlayEl.setAttribute('aria-hidden', 'true');
    overlayEl.setAttribute('role', 'status');
    overlayEl.innerHTML = `
        <div class="app-loading-overlay__panel">
            <div class="app-loading-overlay__spinner" aria-hidden="true"></div>
            <p class="app-loading-overlay__text"></p>
        </div>
    `;
    messageEl = overlayEl.querySelector('.app-loading-overlay__text');
    document.body.appendChild(overlayEl);
    return overlayEl;
}

function syncVisibility() {
    const el = ensureOverlay();
    const active = pendingCount > 0;
    el.classList.toggle('is-active', active);
    el.setAttribute('aria-hidden', active ? 'false' : 'true');
    document.documentElement.classList.toggle('app-loading-active', active);
    if (messageEl) {
        messageEl.textContent = loadingMessage();
    }
}

function show() {
    pendingCount += 1;
    syncVisibility();
}

function hide() {
    pendingCount = Math.max(0, pendingCount - 1);
    syncVisibility();
}

function forceHide() {
    pendingCount = 0;
    syncVisibility();
}

function hasNoLoadingFlag(el) {
    return Boolean(el?.closest?.('[data-no-loading]'));
}

function shouldShowForForm(form) {
    if (!form || hasNoLoadingFlag(form)) {
        return false;
    }
    if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
        return false;
    }
    return true;
}

function isModifiedClick(event) {
    return event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0;
}

function isDownloadNavigation(url) {
    const path = url.pathname.toLowerCase();
    if (/(?:^|\/)(?:pdf|excel|export|download)(?:\/|$)/.test(path) || /\.(?:pdf|xls|xlsx|csv|zip|docx?)$/i.test(path)) {
        return true;
    }

    const dispositionHint = (url.searchParams.get('download') || url.searchParams.get('export') || '').toLowerCase();
    return dispositionHint === '1' || dispositionHint === 'true';
}

function shouldShowForLink(anchor, event) {
    if (!anchor || hasNoLoadingFlag(anchor)) {
        return false;
    }
    if (isModifiedClick(event)) {
        return false;
    }
    if (anchor.hasAttribute('download')) {
        return false;
    }
    const target = (anchor.getAttribute('target') || '').toLowerCase();
    if (target && target !== '_self') {
        return false;
    }

    const href = anchor.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
        return false;
    }

    let url;
    try {
        url = new URL(href, window.location.href);
    } catch {
        return false;
    }

    if (url.origin !== window.location.origin) {
        return false;
    }

    // File downloads keep the current page mounted, so the overlay would stick.
    if (isDownloadNavigation(url)) {
        return false;
    }

    // Same-page hash only
    if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) {
        return false;
    }

    return true;
}

function requestMethod(input, init = {}) {
    if (init.method) {
        return String(init.method).toUpperCase();
    }
    if (typeof Request !== 'undefined' && input instanceof Request && input.method) {
        return String(input.method).toUpperCase();
    }
    return 'GET';
}

function shouldShowForFetch(input, init = {}) {
    const method = requestMethod(input, init);
    if (!MUTATING_METHODS.has(method)) {
        return false;
    }

    let url;
    try {
        const raw = typeof input === 'string' ? input : input?.url;
        url = new URL(raw, window.location.href);
    } catch {
        return true;
    }

    return url.origin === window.location.origin;
}

function onSubmit(event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) {
        return;
    }
    if (!shouldShowForForm(form)) {
        return;
    }

    // Show immediately so classic POST navigations still paint the overlay.
    // Undo on the next tick if another handler called preventDefault.
    show();
    setTimeout(() => {
        if (event.defaultPrevented) {
            hide();
        }
    }, 0);
}

function onClick(event) {
    const anchor = event.target.closest?.('a[href]');
    if (!anchor) {
        return;
    }
    if (!shouldShowForLink(anchor, event)) {
        return;
    }

    show();
    setTimeout(() => {
        if (event.defaultPrevented) {
            hide();
        }
    }, 0);
}

function wrapFetch() {
    if (typeof window.fetch !== 'function') {
        return;
    }
    const original = window.fetch.bind(window);
    window.fetch = async function appLoadingFetch(input, init) {
        const track = shouldShowForFetch(input, init);
        if (track) {
            show();
        }
        try {
            return await original(input, init);
        } finally {
            if (track) {
                hide();
            }
        }
    };
}

function wrapAxios() {
    const axios = window.axios;
    if (!axios?.interceptors) {
        return;
    }

    axios.interceptors.request.use((config) => {
        const method = String(config.method || 'get').toUpperCase();
        if (MUTATING_METHODS.has(method) && !config.skipLoading) {
            show();
            config.__appLoading = true;
        }
        return config;
    });

    const done = (responseOrError) => {
        const config = responseOrError?.config || responseOrError;
        if (config?.__appLoading) {
            hide();
        }
        return responseOrError;
    };

    axios.interceptors.response.use(
        (response) => done(response),
        (error) => {
            done(error);
            return Promise.reject(error);
        }
    );
}

function bindLifecycle() {
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            forceHide();
        }
    });
    window.addEventListener('pagehide', forceHide);
}

function init() {
    ensureOverlay();
    document.addEventListener('submit', onSubmit, true);
    document.addEventListener('click', onClick, true);
    wrapFetch();
    wrapAxios();
    bindLifecycle();

    window.AppLoading = { show, hide, forceHide };
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

export { show, hide, forceHide };

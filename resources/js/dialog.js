/**
 * Global feedback dialog for success / error / warning results.
 */
const ICONS = {
    success: `
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    `,
    error: `
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
        </svg>
    `,
    warning: `
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    `,
};

let dialogEl = null;
let titleEl = null;
let messageEl = null;
let iconEl = null;
let okBtn = null;
let previousFocus = null;
let pendingOnClose = null;

function isArabic() {
    const lang = (document.documentElement.lang || '').toLowerCase();
    return lang.startsWith('ar');
}

function t(key) {
    const ar = {
        success: 'تمت العملية بنجاح',
        error: 'فشلت العملية',
        warning: 'تنبيه',
        ok: 'موافق',
        defaultSuccess: 'تمت العملية بنجاح.',
        defaultError: 'حدث خطأ أثناء تنفيذ العملية.',
    };
    const en = {
        success: 'Operation completed successfully',
        error: 'Operation failed',
        warning: 'Warning',
        ok: 'OK',
        defaultSuccess: 'The operation completed successfully.',
        defaultError: 'Something went wrong while processing the request.',
    };
    return (isArabic() ? ar : en)[key];
}

function ensureDialog() {
    if (dialogEl) {
        return dialogEl;
    }

    dialogEl = document.createElement('div');
    dialogEl.id = 'app-dialog';
    dialogEl.className = 'app-dialog';
    dialogEl.setAttribute('aria-hidden', 'true');
    dialogEl.innerHTML = `
        <div class="app-dialog__backdrop" data-app-dialog-dismiss></div>
        <div class="app-dialog__panel" role="alertdialog" aria-modal="true" aria-labelledby="app-dialog-title" aria-describedby="app-dialog-message">
            <div class="app-dialog__icon" data-app-dialog-icon></div>
            <h2 class="app-dialog__title" id="app-dialog-title"></h2>
            <p class="app-dialog__message" id="app-dialog-message"></p>
            <button type="button" class="app-dialog__ok btn-primary" data-app-dialog-ok></button>
        </div>
    `;

    titleEl = dialogEl.querySelector('#app-dialog-title');
    messageEl = dialogEl.querySelector('#app-dialog-message');
    iconEl = dialogEl.querySelector('[data-app-dialog-icon]');
    okBtn = dialogEl.querySelector('[data-app-dialog-ok]');

    dialogEl.querySelector('[data-app-dialog-dismiss]').addEventListener('click', hide);
    okBtn.addEventListener('click', hide);
    document.body.appendChild(dialogEl);
    return dialogEl;
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        hide();
    }
}

function show(type, message, title, onClose) {
    if (window.AppLoading && typeof window.AppLoading.forceHide === 'function') {
        window.AppLoading.forceHide();
    }

    const el = ensureDialog();
    const kind = ['success', 'error', 'warning'].includes(type) ? type : 'success';
    const text = (message && String(message).trim()) || (kind === 'error' ? t('defaultError') : t('defaultSuccess'));

    pendingOnClose = typeof onClose === 'function' ? onClose : null;
    el.dataset.type = kind;
    iconEl.innerHTML = ICONS[kind];
    titleEl.textContent = title || t(kind);
    messageEl.textContent = text;
    okBtn.textContent = t('ok');

    previousFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    el.classList.add('is-open');
    el.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('app-dialog-open');
    document.addEventListener('keydown', onKeydown);
    okBtn.focus();
}

function hide() {
    if (!dialogEl) {
        return;
    }
    dialogEl.classList.remove('is-open');
    dialogEl.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('app-dialog-open');
    document.removeEventListener('keydown', onKeydown);
    if (previousFocus && typeof previousFocus.focus === 'function') {
        previousFocus.focus();
    }
    previousFocus = null;

    const cb = pendingOnClose;
    pendingOnClose = null;
    if (cb) {
        cb();
    }
}

function normalizeArgs(message, titleOrOnClose, onClose) {
    if (typeof titleOrOnClose === 'function') {
        return { message, title: undefined, onClose: titleOrOnClose };
    }
    return { message, title: titleOrOnClose, onClose };
}

function success(message, titleOrOnClose, onClose) {
    const args = normalizeArgs(message, titleOrOnClose, onClose);
    show('success', args.message, args.title, args.onClose);
}

function error(message, titleOrOnClose, onClose) {
    const args = normalizeArgs(message, titleOrOnClose, onClose);
    show('error', args.message, args.title, args.onClose);
}

function warning(message, titleOrOnClose, onClose) {
    const args = normalizeArgs(message, titleOrOnClose, onClose);
    show('warning', args.message, args.title, args.onClose);
}

function readFlash() {
    const node = document.getElementById('app-flash-data');
    if (!node) {
        return;
    }

    let payload = null;
    try {
        payload = JSON.parse(node.textContent || '');
    } catch {
        return;
    }

    if (!payload || !payload.type || !payload.message) {
        return;
    }

    const type = payload.type === 'error' || payload.type === 'warning' ? payload.type : 'success';
    show(type, payload.message, payload.title || undefined);
}

function bridgeNativeDialogs() {
    // Route any leftover native alerts through the branded dialog.
    window.alert = (message) => {
        error(message == null ? '' : String(message));
    };
}

function init() {
    ensureDialog();
    window.AppDialog = { show, hide, success, error, warning };
    bridgeNativeDialogs();
    readFlash();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

export { show, hide, success, error, warning };

/**
 * Global feedback dialog for success / error / warning results,
 * plus confirm/cancel prompts.
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
let cancelBtn = null;
let actionsEl = null;
let previousFocus = null;
let pendingOnClose = null;
let confirmResolver = null;
let mode = 'alert';

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
        cancel: 'إلغاء',
        confirm: 'تأكيد',
        defaultSuccess: 'تمت العملية بنجاح.',
        defaultError: 'حدث خطأ أثناء تنفيذ العملية.',
        defaultConfirm: 'هل أنت متأكد من تنفيذ هذه العملية؟',
    };
    const en = {
        success: 'Operation completed successfully',
        error: 'Operation failed',
        warning: 'Warning',
        ok: 'OK',
        cancel: 'Cancel',
        confirm: 'Confirm',
        defaultSuccess: 'The operation completed successfully.',
        defaultError: 'Something went wrong while processing the request.',
        defaultConfirm: 'Are you sure you want to continue?',
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
            <div class="app-dialog__actions">
                <button type="button" class="app-dialog__cancel btn-secondary" data-app-dialog-cancel hidden></button>
                <button type="button" class="app-dialog__ok btn-primary" data-app-dialog-ok></button>
            </div>
        </div>
    `;

    titleEl = dialogEl.querySelector('#app-dialog-title');
    messageEl = dialogEl.querySelector('#app-dialog-message');
    iconEl = dialogEl.querySelector('[data-app-dialog-icon]');
    actionsEl = dialogEl.querySelector('.app-dialog__actions');
    okBtn = dialogEl.querySelector('[data-app-dialog-ok]');
    cancelBtn = dialogEl.querySelector('[data-app-dialog-cancel]');

    dialogEl.querySelector('[data-app-dialog-dismiss]').addEventListener('click', () => dismiss(false));
    okBtn.addEventListener('click', () => dismiss(true));
    cancelBtn.addEventListener('click', () => dismiss(false));
    document.body.appendChild(dialogEl);
    return dialogEl;
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        dismiss(false);
    }
}

function resolveConfirm(result) {
    if (typeof confirmResolver === 'function') {
        const resolve = confirmResolver;
        confirmResolver = null;
        resolve(result);
    }
}

function dismiss(confirmed) {
    if (!dialogEl) {
        return;
    }

    const wasConfirm = mode === 'confirm';
    hide();

    if (wasConfirm) {
        resolveConfirm(Boolean(confirmed));
    }
}

function show(type, message, title, onClose) {
    if (window.AppLoading && typeof window.AppLoading.forceHide === 'function') {
        window.AppLoading.forceHide();
    }

    // Closing a previous confirm without answer rejects it.
    if (mode === 'confirm' && confirmResolver) {
        resolveConfirm(false);
    }

    const el = ensureDialog();
    const kind = ['success', 'error', 'warning'].includes(type) ? type : 'success';
    const text = (message && String(message).trim()) || (kind === 'error' ? t('defaultError') : t('defaultSuccess'));

    mode = 'alert';
    pendingOnClose = typeof onClose === 'function' ? onClose : null;
    el.dataset.type = kind;
    el.dataset.mode = 'alert';
    iconEl.innerHTML = ICONS[kind];
    titleEl.textContent = title || t(kind);
    messageEl.textContent = text;
    okBtn.textContent = t('ok');
    cancelBtn.hidden = true;
    cancelBtn.textContent = t('cancel');

    previousFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    el.classList.add('is-open');
    el.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('app-dialog-open');
    document.addEventListener('keydown', onKeydown);
    okBtn.focus();
}

/**
 * @param {string} message
 * @param {{ title?: string, confirmText?: string, cancelText?: string }} [options]
 * @returns {Promise<boolean>}
 */
function confirm(message, options = {}) {
    if (window.AppLoading && typeof window.AppLoading.forceHide === 'function') {
        window.AppLoading.forceHide();
    }

    if (mode === 'confirm' && confirmResolver) {
        resolveConfirm(false);
    }

    const el = ensureDialog();
    const text = (message && String(message).trim()) || t('defaultConfirm');

    mode = 'confirm';
    pendingOnClose = null;
    el.dataset.type = 'warning';
    el.dataset.mode = 'confirm';
    iconEl.innerHTML = ICONS.warning;
    titleEl.textContent = options.title || t('warning');
    messageEl.textContent = text;
    okBtn.textContent = options.confirmText || t('confirm');
    cancelBtn.textContent = options.cancelText || t('cancel');
    cancelBtn.hidden = false;

    previousFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    el.classList.add('is-open');
    el.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('app-dialog-open');
    document.addEventListener('keydown', onKeydown);
    okBtn.focus();

    return new Promise((resolve) => {
        confirmResolver = resolve;
    });
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

    const wasAlert = mode === 'alert';
    mode = 'alert';
    if (cancelBtn) {
        cancelBtn.hidden = true;
    }

    const cb = pendingOnClose;
    pendingOnClose = null;
    if (wasAlert && cb) {
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
    window.AppDialog = { show, hide, success, error, warning, confirm };
    bridgeNativeDialogs();
    readFlash();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

export { show, hide, success, error, warning, confirm };

/**
 * Port of frontend/src/components/UnsupportedBrowserGate.tsx + utils/browserSupport.ts.
 * Replaces the whole document body with the same full-screen notice on IE / ancient engines.
 */
function isUnsupportedBrowser() {
    const ua = navigator.userAgent || '';
    if (/MSIE |Trident\//.test(ua)) return true;
    return (
        typeof window.fetch !== 'function' ||
        typeof window.Promise !== 'function' ||
        typeof Object.assign !== 'function' ||
        typeof URLSearchParams === 'undefined'
    );
}

const RECOMMENDED = ['Google Chrome', 'Microsoft Edge', 'Mozilla Firefox', 'Safari'];

export function installBrowserGate() {
    if (!isUnsupportedBrowser()) return;
    const items = RECOMMENDED.map(
        (name) =>
            `<li style="padding:10px 14px;font-size:14px;font-weight:600;color:#065F46;border-bottom:1px solid #D1FAE5">${name}</li>`
    ).join('');
    document.body.innerHTML = `
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:linear-gradient(160deg,#F0FDF4 0%,#FFFFFF 50%,#ECFDF5 100%);font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color:#111827">
  <div style="max-width:440px;width:100%;background:#fff;border-radius:16px;padding:32px 28px;box-shadow:0 8px 30px rgba(0,0,0,0.08);border:1px solid #D1FAE5;text-align:center">
    <div style="width:56px;height:56px;margin:0 auto 16px;border-radius:14px;background:#E8F5EE;display:flex;align-items:center;justify-content:center;font-size:28px" aria-hidden>⚠</div>
    <h1 style="font-size:22px;font-weight:700;margin:0 0 8px;color:#064E3B">Browser not supported</h1>
    <p style="font-size:14px;line-height:1.55;color:#4B5563;margin:0 0 20px">SpeakUp GC does not work on Internet Explorer or outdated browsers. Please open this site in a modern browser to continue.</p>
    <ul style="list-style:none;padding:0;margin:0 0 20px;text-align:left;background:#F0FDF4;border-radius:12px;border:1px solid #BBF7D0">${items}</ul>
    <p style="font-size:12px;color:#9CA3AF;margin:0">Tip: On Windows, use Microsoft Edge or Chrome. Internet Explorer is retired and unsafe.</p>
  </div>
</div>`;
}

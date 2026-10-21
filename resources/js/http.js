/**
 * Small fetch helper used by Alpine components to call Laravel JSON endpoints.
 * Mirrors the way the original React code awaited Firebase SDK promises and
 * surfaced `error.message` to the shadcn toaster.
 */
export function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

export class ApiError extends Error {
    constructor(message, { status, code, errors } = {}) {
        super(message);
        this.status = status;
        this.code = code;
        this.errors = errors;
    }
}

export async function api(url, { method = 'POST', body, headers = {} } = {}) {
    const res = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken(),
            ...headers,
        },
        body: body === undefined ? undefined : JSON.stringify(body),
    });

    let data = null;
    const text = await res.text();
    try {
        data = text ? JSON.parse(text) : null;
    } catch {
        data = null;
    }

    if (!res.ok) {
        // Laravel validation errors -> first message, like getAuthErrorMessage() did for Firebase codes
        let message = data?.message || `Request failed (${res.status})`;
        if (data?.errors) {
            const first = Object.values(data.errors).flat()[0];
            if (first) message = first;
        }
        if (res.status === 419) message = 'Your session expired. Please refresh the page and try again.';
        throw new ApiError(message, { status: res.status, code: data?.code, errors: data?.errors });
    }
    return data;
}

/** Port of utils/auth/firebaseErrorMessages.getAuthErrorMessage(): prefer server message, else fallback. */
export function errorMessage(error, fallback) {
    if (error instanceof ApiError) return error.message || fallback;
    if (error && typeof error.message === 'string' && error.message && !/^Failed to fetch/i.test(error.message)) {
        return error.message;
    }
    if (error && /Failed to fetch/i.test(error.message || '')) {
        return 'Network error. Please check your connection and try again.';
    }
    return fallback;
}

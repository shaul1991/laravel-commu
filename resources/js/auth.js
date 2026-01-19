/**
 * Authentication Helper Module (OAuth-only)
 * Manages auth tokens and user state
 */

const AUTH_TOKEN_KEY = 'auth_token';
const AUTH_USER_KEY = 'auth_user';

// Token refresh promise for debouncing concurrent refresh requests
let refreshPromise = null;

export const auth = {
    /**
     * Get stored auth token
     */
    getToken() {
        return localStorage.getItem(AUTH_TOKEN_KEY);
    },

    /**
     * Store auth token
     */
    setToken(token) {
        localStorage.setItem(AUTH_TOKEN_KEY, token);
    },

    /**
     * Get stored user data
     */
    getUser() {
        const user = localStorage.getItem(AUTH_USER_KEY);
        return user ? JSON.parse(user) : null;
    },

    /**
     * Store user data
     */
    setUser(user) {
        localStorage.setItem(AUTH_USER_KEY, JSON.stringify(user));
    },

    /**
     * Check if user is authenticated
     */
    isAuthenticated() {
        return !!this.getToken();
    },

    /**
     * Clear auth data
     */
    clear() {
        localStorage.removeItem(AUTH_TOKEN_KEY);
        localStorage.removeItem(AUTH_USER_KEY);
    },

    /**
     * Logout user
     */
    async logout() {
        const token = this.getToken();

        if (token) {
            try {
                await fetch('/api/auth/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });
            } catch (e) {
                // Ignore logout errors
            }
        }

        this.clear();
    },

    /**
     * Get current user from API
     */
    async fetchUser() {
        const token = this.getToken();

        if (!token) {
            return null;
        }

        try {
            const response = await fetch('/api/auth/me', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                if (response.status === 401) {
                    this.clear();
                }
                return null;
            }

            const data = await response.json();
            this.setUser(data.data);
            return data.data;
        } catch (e) {
            return null;
        }
    },

    /**
     * Get authorization headers for API requests
     */
    getAuthHeaders() {
        const token = this.getToken();
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        return headers;
    },

    /**
     * Refresh access token using refresh token cookie
     * Returns new access token or null if refresh fails
     * Uses debouncing to prevent concurrent refresh requests
     */
    async refreshToken() {
        // If already refreshing, return the existing promise
        if (refreshPromise) {
            return refreshPromise;
        }

        refreshPromise = this._doRefreshToken();

        try {
            return await refreshPromise;
        } finally {
            refreshPromise = null;
        }
    },

    /**
     * Internal method to perform the actual token refresh
     * @private
     */
    async _doRefreshToken() {
        try {
            const response = await fetch('/api/auth/refresh', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin', // Important: include cookies
            });

            if (!response.ok) {
                return null;
            }

            const data = await response.json();
            if (data.access_token) {
                this.setToken(data.access_token);
                return data.access_token;
            }

            return null;
        } catch (e) {
            return null;
        }
    },

    /**
     * Handle 401 Unauthorized response
     * First tries to refresh token, then redirects to login if refresh fails
     */
    handleUnauthorized() {
        this.clear();

        // Store current URL for redirect after login
        const currentPath = window.location.pathname;
        if (currentPath !== '/login') {
            sessionStorage.setItem('redirect_after_login', currentPath);
        }

        window.location.href = '/login?session_expired=1';
    },

    /**
     * Authenticated fetch wrapper with automatic token refresh on 401
     * Use this for all authenticated API requests
     * For FormData uploads, pass skipContentType: true in options
     */
    async fetch(url, options = {}) {
        const token = this.getToken();
        const isFormData = options.body instanceof FormData;

        const headers = {
            'Accept': 'application/json',
            ...options.headers,
        };

        // Don't set Content-Type for FormData (browser sets it automatically with boundary)
        if (!isFormData) {
            headers['Content-Type'] = 'application/json';
        }

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        let response = await fetch(url, {
            ...options,
            headers,
            credentials: 'same-origin',
        });

        // Handle 401 Unauthorized - try to refresh token first
        if (response.status === 401) {
            const newToken = await this.refreshToken();

            if (newToken) {
                // Retry the request with new token
                headers['Authorization'] = `Bearer ${newToken}`;
                response = await fetch(url, {
                    ...options,
                    headers,
                    credentials: 'same-origin',
                });

                // If still 401 after refresh, redirect to login
                if (response.status === 401) {
                    this.handleUnauthorized();
                    throw { status: 401, message: 'Session expired' };
                }
            } else {
                // Refresh failed, redirect to login
                this.handleUnauthorized();
                throw { status: 401, message: 'Session expired' };
            }
        }

        return response;
    },
};

// Make auth available globally for Alpine.js components
window.auth = auth;

// Guest-only page redirect: redirect authenticated users to home
const guestOnlyPaths = ['/login'];
if (auth.isAuthenticated() && guestOnlyPaths.includes(window.location.pathname)) {
    window.location.href = '/';
}

// Auth-required page redirect: redirect unauthenticated users to login
const authRequiredPaths = ['/write', '/settings', '/me/articles'];
const authRequiredPatterns = [/^\/articles\/[^/]+\/edit$/];
const currentPath = window.location.pathname;
const isAuthRequiredPath = authRequiredPaths.includes(currentPath) ||
    authRequiredPatterns.some(pattern => pattern.test(currentPath));

if (!auth.isAuthenticated() && isAuthRequiredPath) {
    const returnUrl = encodeURIComponent(window.location.pathname + window.location.search);
    window.location.href = `/login?redirect=${returnUrl}`;
}

export default auth;

/**
 * Authentication Helper Module
 * Manages auth tokens and user state
 */

const AUTH_TOKEN_KEY = 'auth_token';
const AUTH_USER_KEY = 'auth_user';

export const auth = {
    /**
     * Ensure CSRF token is set by calling Sanctum's csrf-cookie endpoint
     */
    async ensureCsrfToken() {
        await fetch('/sanctum/csrf-cookie', {
            credentials: 'same-origin',
        });
    },

    /**
     * Get CSRF token from cookie
     */
    getCsrfToken() {
        const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
        return match ? decodeURIComponent(match[1]) : null;
    },

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
     * Login user
     */
    async login(email, password, remember = false) {
        // Ensure CSRF token is set for Sanctum SPA authentication
        await this.ensureCsrfToken();

        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };

        const csrfToken = this.getCsrfToken();
        if (csrfToken) {
            headers['X-XSRF-TOKEN'] = csrfToken;
        }

        const response = await fetch('/api/auth/login', {
            method: 'POST',
            headers,
            credentials: 'same-origin',
            body: JSON.stringify({ email, password, remember }),
        });

        const data = await response.json();

        if (!response.ok) {
            throw { status: response.status, ...data };
        }

        this.setToken(data.data.token);
        this.setUser(data.data.user);

        return data;
    },

    /**
     * Register user
     */
    async register(userData) {
        // Ensure CSRF token is set for Sanctum SPA authentication
        await this.ensureCsrfToken();

        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };

        const csrfToken = this.getCsrfToken();
        if (csrfToken) {
            headers['X-XSRF-TOKEN'] = csrfToken;
        }

        const response = await fetch('/api/auth/register', {
            method: 'POST',
            headers,
            credentials: 'same-origin',
            body: JSON.stringify(userData),
        });

        const data = await response.json();

        if (!response.ok) {
            throw { status: response.status, ...data };
        }

        this.setToken(data.data.token);
        this.setUser(data.data.user);

        return data;
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
     * Handle 401 Unauthorized response
     * Clears auth data and redirects to login page
     */
    handleUnauthorized() {
        this.clear();

        // Store current URL for redirect after login
        const currentPath = window.location.pathname;
        if (currentPath !== '/login' && currentPath !== '/register') {
            sessionStorage.setItem('redirect_after_login', currentPath);
        }

        window.location.href = '/login?session_expired=1';
    },

    /**
     * Authenticated fetch wrapper with automatic 401 handling
     * Use this for all authenticated API requests
     */
    async fetch(url, options = {}) {
        const token = this.getToken();

        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            ...options.headers,
        };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const response = await fetch(url, {
            ...options,
            headers,
        });

        // Handle 401 Unauthorized
        if (response.status === 401) {
            this.handleUnauthorized();
            throw { status: 401, message: 'Session expired' };
        }

        return response;
    },
};

// Make auth available globally for Alpine.js components
window.auth = auth;

// Guest-only page redirect: redirect authenticated users to home
const guestOnlyPaths = ['/login', '/register', '/forgot-password'];
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

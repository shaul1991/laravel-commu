/**
 * Authentication Helper Module
 * Manages auth tokens and user state
 */

const AUTH_TOKEN_KEY = 'auth_token';
const AUTH_USER_KEY = 'auth_user';

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
     * Login user
     */
    async login(email, password, remember = false) {
        const response = await fetch('/api/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
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
        const response = await fetch('/api/auth/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
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
};

// Make auth available globally for Alpine.js components
window.auth = auth;

export default auth;

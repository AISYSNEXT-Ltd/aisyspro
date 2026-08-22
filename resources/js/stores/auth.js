import { defineStore } from 'pinia';
import api, { csrf } from '../services/api';

export const useAuthStore = defineStore('auth', {
    state: () => ({ user: null, initialized: false }),
    getters: {
        authenticated: (state) => Boolean(state.user),
    },
    actions: {
        async initialize() {
            if (this.initialized) return;
            try {
                const { data } = await api.get('/auth/me');
                this.user = data.user;
            } catch {
                this.user = null;
            } finally {
                this.initialized = true;
            }
        },
        async login(payload) {
            await csrf();
            const { data } = await api.post('/auth/login', payload);
            this.user = data.user;
            this.initialized = true;
        },
        async logout() {
            await api.post('/auth/logout');
            this.user = null;
        },
    },
});

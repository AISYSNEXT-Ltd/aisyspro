import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || '/api/v1',
    timeout: 15000,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true,
    withXSRFToken: true,
});

export default api;

export const csrf = () => axios.get('/sanctum/csrf-cookie', {
    withCredentials: true,
    withXSRFToken: true,
});

export const loginSession = (payload) => axios.post('/connexion-admin/session', payload, {
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true,
    withXSRFToken: true,
});

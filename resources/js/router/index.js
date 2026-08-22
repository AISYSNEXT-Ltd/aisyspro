import { createRouter, createWebHistory } from 'vue-router';
import HomePage from '../pages/HomePage.vue';
import LoginPage from '../pages/LoginPage.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'home',
            component: HomePage,
            meta: { public: true },
        },
        {
            path: '/connexion-admin',
            name: 'login',
            component: LoginPage,
            meta: { public: true },
        },
    ],
    scrollBehavior: () => ({ top: 0 }),
});

export default router;

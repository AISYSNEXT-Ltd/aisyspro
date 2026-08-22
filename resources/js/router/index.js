import { createRouter, createWebHistory } from 'vue-router';
import HomePage from '../pages/HomePage.vue';
import LoginPage from '../pages/LoginPage.vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import DashboardPage from '../pages/admin/DashboardPage.vue';
import EntityPage from '../pages/admin/EntityPage.vue';
import { useAuthStore } from '../stores/auth';

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
            path: '/admin',
            component: AdminLayout,
            meta: { requiresAuth: true },
            children: [
                { path: '', name: 'admin.dashboard', component: DashboardPage },
                ...['clients', 'leads', 'quotes', 'tasks', 'blog-posts', 'solutions'].map((module) => ({
                    path: module,
                    name: `admin.${module}`,
                    component: EntityPage,
                    meta: { module },
                })),
            ],
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

router.beforeEach(async (to) => {
    const auth = useAuthStore();
    await auth.initialize();
    if (to.meta.requiresAuth && !auth.authenticated) return { name: 'login', query: { redirect: to.fullPath } };
    if (to.name === 'login' && auth.authenticated) return { name: 'admin.dashboard' };
});

export default router;

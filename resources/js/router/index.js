import { createRouter, createWebHistory } from 'vue-router';
import HomePage from '../pages/HomePage.vue';
import LoginPage from '../pages/LoginPage.vue';
import SolutionsPage from '../pages/public/SolutionsPage.vue';
import SolutionDetailPage from '../pages/public/SolutionDetailPage.vue';
import PacksPage from '../pages/public/PacksPage.vue';
import BlogPage from '../pages/public/BlogPage.vue';
import BlogDetailPage from '../pages/public/BlogDetailPage.vue';
import AboutPage from '../pages/public/AboutPage.vue';
import FaqPage from '../pages/public/FaqPage.vue';
import ContactPage from '../pages/public/ContactPage.vue';
import QuotePage from '../pages/public/QuotePage.vue';
import LegalPage from '../pages/public/LegalPage.vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import DashboardPage from '../pages/admin/DashboardPage.vue';
import EntityPage from '../pages/admin/EntityPage.vue';
import ProfilePage from '../pages/admin/ProfilePage.vue';
import PageBuilderPage from '../pages/admin/PageBuilderPage.vue';
import MenuBuilderPage from '../pages/admin/MenuBuilderPage.vue';
import MediaLibraryPage from '../pages/admin/MediaLibraryPage.vue';
import SettingsPage from '../pages/admin/SettingsPage.vue';
import LeadPipelinePage from '../pages/admin/LeadPipelinePage.vue';
import ReferencePage from '../pages/admin/ReferencePage.vue';
import NotFoundPage from '../pages/NotFoundPage.vue';
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
        { path: '/solutions', name: 'solutions', component: SolutionsPage, meta: { public: true } },
        { path: '/solutions/:slug', name: 'solution.detail', component: SolutionDetailPage, meta: { public: true } },
        { path: '/packs', name: 'packs', component: PacksPage, meta: { public: true } },
        { path: '/blog', name: 'blog', component: BlogPage, meta: { public: true } },
        { path: '/blog/:slug', name: 'blog.detail', component: BlogDetailPage, meta: { public: true } },
        { path: '/a-propos', name: 'about', component: AboutPage, meta: { public: true } },
        { path: '/faq', name: 'faq', component: FaqPage, meta: { public: true } },
        { path: '/contact', name: 'contact', component: ContactPage, meta: { public: true } },
        { path: '/devis', name: 'quote', component: QuotePage, meta: { public: true } },
        { path: '/mentions-legales', name: 'legal', component: LegalPage, meta: { public: true } },
        { path: '/confidentialite', name: 'privacy', component: LegalPage, meta: { public: true } },
        {
            path: '/admin',
            component: AdminLayout,
            meta: { requiresAuth: true },
            children: [
                { path: '', name: 'admin.dashboard', component: DashboardPage },
                ...['clients', 'quotes', 'tasks', 'inquiries', 'blog-posts', 'solutions', 'packs', 'offer-options', 'faqs', 'pages', 'users'].map((module) => ({
                    path: module,
                    name: `admin.${module}`,
                    component: EntityPage,
                    meta: { module },
                })),
                { path: 'leads', name: 'admin.leads', component: LeadPipelinePage },
                { path: 'leads/liste', name: 'admin.leads.list', component: EntityPage, meta: { module: 'leads' } },
                { path: 'page-builder', name: 'admin.page-builder', component: PageBuilderPage },
                { path: 'menus', name: 'admin.menus', component: MenuBuilderPage },
                { path: 'media', name: 'admin.media', component: MediaLibraryPage },
                { path: 'testimonials', name: 'admin.testimonials', component: EntityPage, meta: { module: 'testimonials' } },
                { path: 'settings', name: 'admin.settings', component: SettingsPage },
                { path: 'referentiels', name: 'admin.references', component: ReferencePage },
                { path: 'profil', name: 'admin.profile', component: ProfilePage },
            ],
        },
        { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFoundPage, meta: { public: true } },
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

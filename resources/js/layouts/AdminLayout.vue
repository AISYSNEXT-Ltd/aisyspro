<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import api from '../services/api';

const auth = useAuthStore();
const router = useRouter();
const open = ref(false);
const profileOpen = ref(false);
const notificationsOpen = ref(false);
const notificationCount = ref(0);

const navigation = [
    { label: 'Pilotage', items: [['Vue d’ensemble', '/admin', '▦', ['administrateur', 'commercial', 'editeur']]] },
    { label: 'CRM & ventes', items: [['Prospects', '/admin/leads', '⌁', ['administrateur', 'commercial']], ['Clients', '/admin/clients', '♙', ['administrateur', 'commercial']], ['Devis', '/admin/quotes', '▤', ['administrateur', 'commercial']], ['Demandes & messages', '/admin/inquiries', '✉', ['administrateur', 'commercial']], ['Tâches & agenda', '/admin/tasks', '◷', ['administrateur', 'commercial']]] },
    { label: 'Contenu', items: [['Page Builder', '/admin/page-builder', '▧', ['administrateur', 'editeur']], ['Blog', '/admin/blog-posts', '≡', ['administrateur', 'editeur']], ['Pages & SEO', '/admin/pages', '▧', ['administrateur', 'editeur']], ['FAQ', '/admin/faqs', '✦', ['administrateur', 'editeur']], ['Témoignages', '/admin/testimonials', '★', ['administrateur', 'editeur']], ['Médias', '/admin/media', '◫', ['administrateur', 'editeur']], ['Menus', '/admin/menus', '☷', ['administrateur', 'editeur']]] },
    { label: 'Offre', items: [['Solutions CMS', '/admin/solutions', '◈', ['administrateur', 'editeur']], ['Packs & tarifs', '/admin/packs', '◫', ['administrateur', 'editeur']], ['Options', '/admin/offer-options', '＋', ['administrateur', 'editeur']]] },
    { label: 'Système', items: [['Utilisateurs & rôles', '/admin/users', '♧', ['administrateur']], ['Référentiels', '/admin/referentiels', '⌘', ['administrateur']], ['Configuration', '/admin/settings', '⚙', ['administrateur']]] },
];

const visibleNavigation = (group) => group.items.filter((item) => item[3].includes(auth.user?.role?.slug));

async function logout() {
    await auth.logout();
    router.push('/connexion-admin');
}

onMounted(async () => {
    try {
        const { data } = await api.get('/dashboard');
        notificationCount.value = data.data.new_inquiries || 0;
    } catch { notificationCount.value = 0; }
});
</script>

<template>
    <div class="admin-shell">
        <aside class="admin-sidebar" :class="{ open }">
            <RouterLink to="/admin" class="brand" @click="open = false">
                <span class="brand-mark">A</span><span>AISYS<strong>PRO</strong></span>
            </RouterLink>
            <nav>
                <section v-for="group in navigation" :key="group.label" v-show="visibleNavigation(group).length" class="nav-group">
                    <small>{{ group.label }}</small>
                    <RouterLink v-for="item in visibleNavigation(group)" :key="item[1]" :to="item[1]" @click="open = false"><span>{{ item[2] }}</span>{{ item[0] }}</RouterLink>
                </section>
            </nav>
        </aside>
        <section class="admin-main">
            <header class="admin-topbar">
                <button class="menu-button" type="button" aria-label="Ouvrir le menu" @click="open = !open">☰</button>
                <div><strong>Espace de gestion</strong><small>Staging AISYSPRO</small></div>
                <div class="topbar-actions">
                    <span class="role-pill">{{ auth.user?.role?.name || 'Utilisateur' }}</span>
                    <div class="topbar-menu"><button class="icon-button" type="button" aria-label="Notifications" @click="notificationsOpen = !notificationsOpen; profileOpen = false">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" /></svg><span v-if="notificationCount" class="notification-badge">{{ notificationCount > 99 ? '99+' : notificationCount }}</span>
                    </button><div v-if="notificationsOpen" class="topbar-dropdown notification-dropdown"><strong>Notifications</strong><RouterLink to="/admin/inquiries" @click="notificationsOpen = false">{{ notificationCount }} nouvelle{{ notificationCount > 1 ? 's' : '' }} demande{{ notificationCount > 1 ? 's' : '' }} à traiter</RouterLink></div></div>
                    <div class="topbar-menu"><button class="profile-button" type="button" @click="profileOpen = !profileOpen; notificationsOpen = false"><span>{{ auth.user?.name?.charAt(0)?.toUpperCase() || 'A' }}</span><div><strong>{{ auth.user?.name }}</strong><small>Mon compte</small></div><b>⌄</b></button>
                        <div v-if="profileOpen" class="topbar-dropdown"><RouterLink to="/admin/profil" @click="profileOpen = false">Mon profil</RouterLink><RouterLink v-if="auth.user?.role?.slug === 'administrateur'" to="/admin/settings" @click="profileOpen = false">Paramètres du compte</RouterLink><button type="button" @click="logout">Se déconnecter</button></div>
                    </div>
                </div>
            </header>
            <main class="admin-content"><RouterView /></main>
        </section>
        <button v-if="open" class="sidebar-backdrop" aria-label="Fermer le menu" @click="open = false" />
    </div>
</template>

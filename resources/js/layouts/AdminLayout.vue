<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const open = ref(false);

const navigation = [
    { label: 'Pilotage', items: [['Vue d’ensemble', '/admin', '▦', ['administrateur', 'commercial', 'editeur']]] },
    { label: 'CRM & ventes', items: [['Prospects', '/admin/leads', '⌁', ['administrateur', 'commercial']], ['Clients', '/admin/clients', '♙', ['administrateur', 'commercial']], ['Devis', '/admin/quotes', '▤', ['administrateur', 'commercial']], ['Demandes & messages', '/admin/inquiries', '✉', ['administrateur', 'commercial']], ['Tâches & agenda', '/admin/tasks', '◷', ['administrateur', 'commercial']]] },
    { label: 'Contenu', items: [['Page Builder', '/admin/page-builder', '▧', ['administrateur', 'editeur']], ['Blog', '/admin/blog-posts', '≡', ['administrateur', 'editeur']], ['Pages & SEO', '/admin/pages', '▧', ['administrateur', 'editeur']], ['FAQ', '/admin/faqs', '✦', ['administrateur', 'editeur']], ['Témoignages', '/admin/testimonials', '★', ['administrateur', 'editeur']], ['Médias', '/admin/media', '◫', ['administrateur', 'editeur']], ['Menus', '/admin/menus', '☷', ['administrateur', 'editeur']]] },
    { label: 'Offre', items: [['Solutions CMS', '/admin/solutions', '◈', ['administrateur', 'editeur']], ['Packs & tarifs', '/admin/packs', '◫', ['administrateur', 'editeur']], ['Options', '/admin/offer-options', '＋', ['administrateur', 'editeur']]] },
    { label: 'Système', items: [['Utilisateurs & rôles', '/admin/users', '♧', ['administrateur']], ['Configuration', '/admin/settings', '⚙', ['administrateur']]] },
];

const visibleNavigation = (group) => group.items.filter((item) => item[3].includes(auth.user?.role?.slug));

async function logout() {
    await auth.logout();
    router.push('/connexion-admin');
}
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
            <div class="sidebar-user">
                <small>Connecté en tant que</small>
                <strong>{{ auth.user?.name }}</strong>
                <RouterLink to="/admin/profil" @click="open = false">Mon profil</RouterLink>
                <button type="button" @click="logout">Se déconnecter</button>
            </div>
        </aside>
        <section class="admin-main">
            <header class="admin-topbar">
                <button class="menu-button" type="button" aria-label="Ouvrir le menu" @click="open = !open">☰</button>
                <div><strong>Espace de gestion</strong><small>Staging AISYSPRO</small></div>
                <span class="role-pill">{{ auth.user?.role?.name || 'Utilisateur' }}</span>
            </header>
            <main class="admin-content"><RouterView /></main>
        </section>
        <button v-if="open" class="sidebar-backdrop" aria-label="Fermer le menu" @click="open = false" />
    </div>
</template>

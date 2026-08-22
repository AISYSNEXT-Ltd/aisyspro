<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const open = ref(false);

const navigation = [
    ['Tableau de bord', '/admin'], ['Prospects', '/admin/leads'], ['Clients', '/admin/clients'],
    ['Devis', '/admin/quotes'], ['Tâches & agenda', '/admin/tasks'], ['Blog', '/admin/blog-posts'],
    ['Solutions', '/admin/solutions'],
];

const visibleNavigation = () => auth.user?.role?.slug === 'administrateur'
    ? [...navigation, ['Utilisateurs & rôles', '/admin/users']]
    : navigation;

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
                <RouterLink v-for="item in visibleNavigation()" :key="item[1]" :to="item[1]" @click="open = false">{{ item[0] }}</RouterLink>
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

<script setup>
import { ref } from 'vue';

const menuOpen = ref(false);
const closeMenu = () => { menuOpen.value = false; };
</script>

<template>
    <div class="public-shell">
        <header class="site-header">
            <RouterLink to="/" class="site-brand" aria-label="AISYSPRO accueil" @click="closeMenu">
                <strong>AISYS</strong><span>PRO</span>
            </RouterLink>
            <button class="mobile-toggle" type="button" :aria-expanded="menuOpen" aria-label="Ouvrir le menu" @click="menuOpen = !menuOpen">☰</button>
            <nav :class="{ open: menuOpen }">
                <RouterLink to="/" @click="closeMenu">Accueil</RouterLink>
                <RouterLink to="/solutions" @click="closeMenu">Solutions</RouterLink>
                <RouterLink to="/packs" @click="closeMenu">Packs</RouterLink>
                <RouterLink to="/blog" @click="closeMenu">Blog</RouterLink>
                <RouterLink to="/a-propos" @click="closeMenu">À propos</RouterLink>
                <RouterLink to="/faq" @click="closeMenu">FAQ</RouterLink>
                <RouterLink to="/contact" @click="closeMenu">Contact</RouterLink>
            </nav>
            <div class="site-header-actions">
                <RouterLink to="/connexion-admin" class="client-link">Espace client</RouterLink>
                <RouterLink to="/devis" class="site-cta">Configurer mon offre ↗</RouterLink>
            </div>
        </header>

        <slot />

        <section class="final-cta">
            <span>Votre prochain projet</span>
            <h2>Une seule équipe.<br><em>Un système qui avance.</em></h2>
            <RouterLink to="/devis" class="site-cta large">Configurer mon offre ↗</RouterLink>
        </section>

        <footer class="site-footer">
            <div class="site-footer-grid">
                <div><RouterLink to="/" class="site-brand"><strong>AISYS</strong><span>PRO</span></RouterLink><p>Applications métier, sites web connectés et hébergement géré pour les professionnels, PME et startups.</p><a href="tel:+21651912668">+216 51 912 668</a><a href="mailto:contact.aisyspro@gmail.com">contact.aisyspro@gmail.com</a></div>
                <div><b>Solutions</b><RouterLink to="/solutions">CRM métier</RouterLink><RouterLink to="/solutions">Site web connecté</RouterLink><RouterLink to="/packs">Hébergement</RouterLink><RouterLink to="/packs">Packs & tarifs</RouterLink></div>
                <div><b>Entreprise</b><RouterLink to="/a-propos">À propos</RouterLink><RouterLink to="/blog">Blog</RouterLink><RouterLink to="/contact">Contact</RouterLink></div>
                <div><b>Informations</b><RouterLink to="/faq">FAQ</RouterLink><RouterLink to="/mentions-legales">Mentions légales</RouterLink><RouterLink to="/confidentialite">Confidentialité</RouterLink></div>
            </div>
            <div class="site-footer-bottom"><span>© {{ new Date().getFullYear() }} AISYSPRO. Tous droits réservés.</span><span>Conçu en Tunisie pour les entreprises qui veulent avancer.</span></div>
        </footer>

        <a class="whatsapp-float" href="https://wa.me/21651912668?text=Bonjour%20AISYSPRO%2C%20je%20souhaite%20obtenir%20plus%20d%E2%80%99informations%20sur%20vos%20solutions." target="_blank" rel="noopener" aria-label="Discuter sur WhatsApp">WA</a>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api';
import DynamicSections from '../components/DynamicSections.vue';
import { useSeo } from '../composables/useSeo';

const menuOpen = ref(false);
const closeMenu = () => { menuOpen.value = false; };
const route = useRoute();
const sections = ref([]);
const headerItems = ref([]);
const footerItems = ref([]);
const replaceSystemContent = computed(() => sections.value.some((section) => section.display_mode === 'replace'));
const pageSlugs = { home: 'accueil', solutions: 'solutions', packs: 'packs', blog: 'blog', about: 'a-propos', faq: 'faq', contact: 'contact' };

async function loadCms() {
    const slug = pageSlugs[route.name];
    sections.value = [];
    const requests = [api.get('/public/menus/header'), api.get('/public/menus/footer')];
    if (slug) requests.push(api.get(`/public/pages/${slug}`));
    const [header, footer, page] = await Promise.allSettled(requests);
    if (header.status === 'fulfilled') headerItems.value = header.value.data.data;
    if (footer.status === 'fulfilled') footerItems.value = footer.value.data.data;
    if (page?.status === 'fulfilled') {
        const record = page.value.data.data;
        sections.value = record.sections || [];
        useSeo({
            title: record.meta_title || record.title,
            description: record.meta_description || record.excerpt,
            canonical: record.canonical_url,
            ogTitle: record.og_title,
            ogDescription: record.og_description,
            image: record.og_image,
            robots: record.robots,
        });
    }
}

watch(() => route.fullPath, loadCms);
onMounted(loadCms);
</script>

<template>
    <div class="public-shell">
        <header class="site-header">
            <RouterLink to="/" class="site-brand" aria-label="AISYSPRO accueil" @click="closeMenu">
                <strong>AISYS</strong><span>PRO</span>
            </RouterLink>
            <button class="mobile-toggle" type="button" :aria-expanded="menuOpen" aria-label="Ouvrir le menu" @click="menuOpen = !menuOpen">☰</button>
            <nav :class="{ open: menuOpen }">
                <template v-if="headerItems.length"><div v-for="item in headerItems" :key="item.id" class="menu-entry">
                    <RouterLink v-if="item.link_type === 'internal'" :to="item.url" @click="closeMenu">{{ item.label }}</RouterLink><a v-else :href="item.url" :target="item.target" rel="noopener">{{ item.label }}</a>
                    <div v-if="item.children?.length" class="submenu"><template v-for="child in item.children" :key="child.id"><RouterLink v-if="child.link_type === 'internal'" :to="child.url" @click="closeMenu">{{ child.label }}</RouterLink><a v-else :href="child.url" :target="child.target" rel="noopener">{{ child.label }}</a></template></div>
                </div></template>
                <template v-else><RouterLink to="/" @click="closeMenu">Accueil</RouterLink><RouterLink to="/solutions" @click="closeMenu">Solutions</RouterLink><RouterLink to="/packs" @click="closeMenu">Packs</RouterLink><RouterLink to="/blog" @click="closeMenu">Blog</RouterLink><RouterLink to="/a-propos" @click="closeMenu">À propos</RouterLink><RouterLink to="/faq" @click="closeMenu">FAQ</RouterLink><RouterLink to="/contact" @click="closeMenu">Contact</RouterLink></template>
            </nav>
            <div class="site-header-actions">
                <RouterLink to="/connexion-admin" class="client-link">Espace client</RouterLink>
                <RouterLink to="/devis" class="site-cta">Configurer mon offre ↗</RouterLink>
            </div>
        </header>

        <slot v-if="!replaceSystemContent" />
        <main v-else class="cms-replacement-intro"><span>Contenu administré</span></main>
        <DynamicSections :sections="sections" />

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
                <div><b>Informations</b><template v-if="footerItems.length"><template v-for="item in footerItems" :key="item.id"><RouterLink v-if="item.link_type === 'internal'" :to="item.url">{{ item.label }}</RouterLink><a v-else :href="item.url" :target="item.target" rel="noopener">{{ item.label }}</a></template></template><template v-else><RouterLink to="/faq">FAQ</RouterLink><RouterLink to="/mentions-legales">Mentions légales</RouterLink><RouterLink to="/confidentialite">Confidentialité</RouterLink></template></div>
            </div>
            <div class="site-footer-bottom"><span>© {{ new Date().getFullYear() }} AISYSPRO. Tous droits réservés.</span><span>Conçu en Tunisie pour les entreprises qui veulent avancer.</span></div>
        </footer>

        <a class="whatsapp-float" href="https://wa.me/21651912668?text=Bonjour%20AISYSPRO%2C%20je%20souhaite%20obtenir%20plus%20d%E2%80%99informations%20sur%20vos%20solutions." target="_blank" rel="noopener" aria-label="Discuter sur WhatsApp">WA</a>
    </div>
</template>

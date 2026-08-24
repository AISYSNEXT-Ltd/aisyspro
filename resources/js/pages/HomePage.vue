<script setup>
import { onMounted, reactive } from 'vue';
import PublicLayout from '../layouts/PublicLayout.vue';
import api from '../services/api';
import { useSeo } from '../composables/useSeo';

const content = reactive({ solutions: [], packs: [], faqs: [], posts: [] });

onMounted(async () => {
    useSeo('AISYSPRO — CRM, site web et hébergement métier', 'Solutions métier, CRM, sites web connectés et hébergement pour les professionnels et PME.');
    const { data } = await api.get('/public/content');
    Object.assign(content, data);
});
</script>

<template>
    <PublicLayout>
        <main>
            <section class="new-hero">
                <div class="hero-mesh"></div>
                <div class="hero-content">
                    <span class="eyebrow-new light">Solutions métier · Site web · Hébergement</span>
                    <h1>Le digital qui comprend<br><em>votre métier.</em></h1>
                    <p>AISYSPRO réunit votre CRM, votre site web et votre infrastructure dans une solution claire, évolutive et conçue autour de vos vrais processus.</p>
                    <div class="new-hero-actions"><RouterLink to="/devis" class="site-cta large">Configurer mon offre ↗</RouterLink><RouterLink to="/solutions" class="outline-cta">Explorer nos solutions →</RouterLink></div>
                    <div class="hero-proof"><span><b>3</b><small>packs transparents</small></span><span><b>50+</b><small>métiers adaptables</small></span><span><b>100%</b><small>paramétrable</small></span></div>
                </div>
                <div class="hero-product" aria-label="Aperçu illustratif du produit AISYSPRO">
                    <div class="product-window">
                        <header><small>workspace.aisyspro.tn</small><i></i><i></i><i></i></header>
                        <div class="product-body">
                            <aside class="product-sidebar"><b>A</b><span>▦</span><span>◎</span><span>⌁</span><span>▤</span><span>⚙</span></aside>
                            <div class="product-main"><div class="product-title"><span><small>Vue d’ensemble</small><b>Bonjour, Ahmed 👋</b></span><button>+ Nouveau devis</button></div><div class="product-kpis"><article><small>Nouveaux leads</small><b>48</b><em>↗ 18,2%</em></article><article><small>Devis ouverts</small><b>17</b><em>↗ 6,4%</em></article><article><small>Conversion</small><b>68%</b><em>↗ 4,1%</em></article></div><div class="product-panels"><article><b>Performance commerciale</b><small>6 derniers mois</small><div class="mini-chart"><i v-for="height in [35, 48, 43, 68, 58, 81, 95]" :key="height" :style="{ height: `${height}%` }"></i></div></article><article><b>Pipeline</b><p>Nouveau <strong>48</strong></p><p>Qualifié <strong>32</strong></p><p>Proposition <strong>26</strong></p><p>Gagné <strong>8</strong></p></article></div></div>
                        </div>
                        <footer class="product-demo"><span>Démonstration · données illustratives</span><div><b>✓</b><small>Nouveau lead<br><strong>Cabinet exemple</strong></small></div><div><b>↗</b><small>Indicateur démo<br><strong>Suivi en temps réel</strong></small></div></footer>
                    </div>
                </div>
                <a href="#ecosysteme" class="hero-scroll">Découvrir ↓</a>
            </section>

            <section class="signal-strip"><strong>Un écosystème complet</strong><span>CRM MÉTIER</span><span>SITE WEB</span><span>CMS</span><span>HÉBERGEMENT</span><span>AUTOMATISATION</span></section>

            <section id="ecosysteme" class="new-section ecosystem"><div class="new-heading"><span class="eyebrow-new">Une vision 360°</span><h2>Trois expertises.<br><em>Un seul partenaire responsable.</em></h2><p>Plus de rupture entre votre vitrine, vos prospects et vos opérations : tout communique, tout se mesure.</p></div><div class="expertise-grid"><article><span>01</span><h3>CRM & application métier</h3><p>Votre vocabulaire, vos règles, vos documents et vos indicateurs dans une plateforme qui ressemble à votre entreprise.</p><RouterLink to="/solutions">Explorer le CRM ↗</RouterLink></article><article><span>02</span><h3>Site web connecté</h3><p>Une présence rapide, crédible et orientée conversion. Chaque formulaire alimente le CRM automatiquement.</p><RouterLink to="/solutions">Découvrir le web ↗</RouterLink></article><article><span>03</span><h3>Hébergement géré</h3><p>Domaine, SSL, emails, sauvegardes et supervision réunis dans une infrastructure suivie par nos experts.</p><RouterLink to="/solutions">Voir l’infrastructure ↗</RouterLink></article></div></section>

            <section class="new-section solutions-preview"><div class="new-heading"><span class="eyebrow-new">Une solution pour chaque métier</span><h2>Votre activité change.<br><em>Le système s’adapte.</em></h2><p>Les cinq solutions prioritaires et leur ordre sont pilotés directement depuis le CMS.</p></div><div class="sector-new-grid"><RouterLink v-for="(solution, index) in content.solutions" :key="solution.id" :to="`/solutions/${solution.slug}`" class="sector-card"><span>{{ String(index + 1).padStart(2, '0') }}</span><small>{{ solution.category }}</small><h3>{{ solution.title }}</h3><p>{{ solution.short_description }}</p><div><em v-for="module in solution.modules?.slice(0, 3)" :key="module">{{ module }}</em></div><b>Découvrir ↗</b></RouterLink><RouterLink to="/solutions#catalogue" class="sector-card discover-card"><span>06</span><small>Catalogue complet</small><h3>47 autres solutions administrables</h3><p>Explorez les solutions adaptées aux secteurs et processus professionnels.</p><b>Explorer les métiers ↗</b></RouterLink></div></section>

            <section class="method-section"><div class="new-heading light"><span class="eyebrow-new light">Méthode AISYSPRO</span><h2>Du besoin au résultat.<br><em>Sans zone grise.</em></h2></div><div class="method-grid"><article v-for="(step, index) in [['Diagnostic','Processus, utilisateurs, données et objectifs mesurables.'],['Prototype','Parcours et écrans validés avant le développement.'],['Sprints','Livraisons progressives et démonstrations régulières.'],['Recette','Tests client, QA et expert métier avec critères précis.'],['Évolution','Formation, support, mesure et améliorations continues.']]" :key="step[0]"><span>0{{ index + 1 }}</span><small>Étape {{ index + 1 }}</small><h3>{{ step[0] }}</h3><p>{{ step[1] }}</p></article></div></section>

            <section class="new-section"><div class="new-heading"><span class="eyebrow-new">Des tarifs lisibles</span><h2>Des packs adaptés.<br><em>Le choix reste simple.</em></h2><p>Commencez avec le bon socle, puis ajoutez uniquement les options utiles.</p></div><div class="pricing-new-grid"><article v-for="pack in content.packs" :key="pack.id" :class="{ featured: pack.featured }"><span v-if="pack.featured" class="popular">Le plus choisi</span><small>{{ pack.description }}</small><h3>{{ pack.name }}</h3><div class="price"><strong>{{ Number(pack.price).toFixed(0) }}</strong><b>DT</b></div><p>{{ pack.billing_period }}</p><ul><li v-for="feature in pack.features" :key="feature">✓ {{ feature }}</li></ul><RouterLink :to="{ path: '/devis', query: { pack: pack.slug } }" class="site-cta">Choisir ce pack ↗</RouterLink></article></div><RouterLink to="/packs" class="text-link">Comparer les packs et toutes les options →</RouterLink></section>

            <section class="comparison-section"><div class="new-heading"><span class="eyebrow-new">Comparer objectivement</span><h2>Une décision lisible.<br><em>Sans dépendance inutile.</em></h2><p>Comparez les approches sur les critères qui ont un impact réel après la mise en ligne.</p></div><div class="comparison-table"><div class="comparison-row comparison-head"><b>Critère</b><b>AISYSPRO</b><b>Approche logicielle standard</b></div><div v-for="row in [['Modèle économique','Périmètre et prix affichés','Abonnement ou licences possibles'],['Personnalisation','Processus et rôles configurables','Cadre souvent standardisé'],['Données','Hébergement client étudiable selon le projet','Dépend du fournisseur choisi'],['Évolution','Modules et intégrations cadrés','Fonctions dictées par la feuille de route éditeur'],['Accompagnement','Service après-vente prévu pendant 1 an','Niveau variable selon le contrat']]" :key="row[0]" class="comparison-row"><b>{{ row[0] }}</b><span>✓ {{ row[1] }}</span><span>{{ row[2] }}</span></div></div><p class="comparison-note">Les conditions exactes, les services tiers et les évolutions sont confirmés dans le devis. La comparaison ne vise aucun concurrent en particulier.</p></section>

            <section class="new-section blog-preview"><div class="new-heading"><span class="eyebrow-new">Conseils & expertise</span><h2>Décider avec<br><em>les bonnes informations.</em></h2><p>Des guides sectoriels concrets pour cadrer vos priorités, vos processus et votre transformation digitale.</p></div><div class="blog-catalog-grid"><article v-for="post in content.posts" :key="post.id" class="blog-card"><div class="blog-cover"><span>{{ post.category?.charAt(0) || 'A' }}</span></div><small>{{ post.category }} · Guide métier</small><h3>{{ post.title }}</h3><p>{{ post.excerpt }}</p><RouterLink :to="`/blog/${post.slug}`">Lire le guide ↗</RouterLink></article></div><RouterLink to="/blog" class="text-link">Voir les 50 guides métier →</RouterLink></section>
        </main>
    </PublicLayout>
</template>

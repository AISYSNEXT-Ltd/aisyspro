<script setup>
import MarkdownContent from './MarkdownContent.vue';

defineProps({ sections: { type: Array, default: () => [] } });

const styleFor = (section) => ({
    '--section-duration': `${section.duration || 0}ms`,
    '--section-delay': `${section.delay || 0}ms`,
    '--section-intensity': `${section.intensity || 0}px`,
});
</script>

<template>
    <section
        v-for="section in sections"
        :key="section.id"
        class="cms-section new-section"
        :class="[`cms-section--${section.type}`, `cms-animation--${section.animation}`]"
        :style="styleFor(section)"
    >
        <div class="cms-section-inner" :class="{ 'cms-section-split': section.type === 'image-text' }">
            <div>
                <span v-if="section.settings?.eyebrow" class="eyebrow-new">{{ section.settings.eyebrow }}</span>
                <h2 v-if="section.title">{{ section.title }}</h2>
                <MarkdownContent v-if="section.content" :content="section.content" />
                <RouterLink v-if="section.settings?.button_label && section.settings?.button_url?.startsWith('/')" :to="section.settings.button_url" class="site-cta">
                    {{ section.settings.button_label }} ↗
                </RouterLink>
                <a v-else-if="section.settings?.button_label && section.settings?.button_url" :href="section.settings.button_url" class="site-cta" target="_blank" rel="noopener">
                    {{ section.settings.button_label }} ↗
                </a>
            </div>
            <img v-if="section.settings?.image" :src="section.settings.image" :alt="section.settings.alt || section.title || ''" loading="lazy" decoding="async">
            <div v-if="Array.isArray(section.settings?.items)" class="cms-card-grid">
                <article v-for="(item, index) in section.settings.items" :key="`${section.id}-${index}`">
                    <span v-if="item.label">{{ item.label }}</span><h3>{{ item.title }}</h3><p>{{ item.text }}</p>
                </article>
            </div>
        </div>
    </section>
</template>

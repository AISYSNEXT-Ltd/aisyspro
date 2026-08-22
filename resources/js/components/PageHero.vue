<script setup>
defineProps({ label: String, title: String, accent: String, description: String });
</script>

<template>
    <section class="page-hero">
        <div class="page-hero-grid"></div>
        <div>
            <span class="eyebrow-new light">{{ label }}</span>
            <h1>{{ title }}<br><em>{{ accent }}</em></h1>
            <p>{{ description }}</p>
            <div v-if="$slots.default" class="page-hero-actions"><slot /></div>
        </div>
        <aside aria-hidden="true"><span>A</span><b>CRM</b><b>WEB</b><b>CLOUD</b></aside>
    </section>
</template>

function meta(selector, attributes) {
    let element = document.head.querySelector(selector);
    if (!element) {
        element = document.createElement('meta');
        document.head.appendChild(element);
    }
    Object.entries(attributes).forEach(([key, value]) => element.setAttribute(key, value || ''));
    return element;
}

export function useSeo(input, description = '') {
    const options = typeof input === 'string' ? { title: input, description } : input;
    const canonical = options.canonical || `${window.location.origin}${window.location.pathname}`;
    const title = options.title || 'AISYSPRO';
    const summary = options.description || '';
    const image = options.image || '';

    document.title = title;
    meta('meta[name="description"]', { name: 'description', content: summary });
    meta('meta[name="robots"]', { name: 'robots', content: options.robots || 'index,follow' });
    meta('meta[property="og:title"]', { property: 'og:title', content: options.ogTitle || title });
    meta('meta[property="og:description"]', { property: 'og:description', content: options.ogDescription || summary });
    meta('meta[property="og:url"]', { property: 'og:url', content: canonical });
    meta('meta[property="og:type"]', { property: 'og:type', content: options.type || 'website' });
    meta('meta[name="twitter:card"]', { name: 'twitter:card', content: image ? 'summary_large_image' : 'summary' });
    if (image) {
        meta('meta[property="og:image"]', { property: 'og:image', content: image });
        meta('meta[name="twitter:image"]', { name: 'twitter:image', content: image });
    }

    let link = document.head.querySelector('link[rel="canonical"]');
    if (!link) { link = document.createElement('link'); link.rel = 'canonical'; document.head.appendChild(link); }
    link.href = canonical;

    document.getElementById('aisyspro-structured-data')?.remove();
    if (options.structuredData) {
        const script = document.createElement('script');
        script.id = 'aisyspro-structured-data';
        script.type = 'application/ld+json';
        script.textContent = JSON.stringify(options.structuredData);
        document.head.appendChild(script);
    }
}

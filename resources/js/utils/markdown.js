const escapeHtml = (value) => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

function inline(value) {
    return escapeHtml(value)
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\[([^\]]+)]\((\/(?:[^)]+)|https?:\/\/[^)]+)\)/g, '<a href="$2">$1</a>');
}

export function renderMarkdown(markdown = '') {
    const lines = String(markdown).replaceAll('\r', '').split('\n');
    const output = [];
    let list = [];
    let paragraph = [];

    const flushList = () => {
        if (list.length) output.push(`<ul>${list.map((item) => `<li>${inline(item)}</li>`).join('')}</ul>`);
        list = [];
    };
    const flushParagraph = () => {
        if (paragraph.length) output.push(`<p>${inline(paragraph.join(' '))}</p>`);
        paragraph = [];
    };

    lines.forEach((line) => {
        const value = line.trim();
        if (!value) { flushParagraph(); flushList(); return; }
        if (value.startsWith('### ')) { flushParagraph(); flushList(); output.push(`<h3>${inline(value.slice(4))}</h3>`); return; }
        if (value.startsWith('## ')) { flushParagraph(); flushList(); output.push(`<h2>${inline(value.slice(3))}</h2>`); return; }
        if (value.startsWith('# ')) { flushParagraph(); flushList(); output.push(`<h1>${inline(value.slice(2))}</h1>`); return; }
        if (value.startsWith('> ')) { flushParagraph(); flushList(); output.push(`<blockquote>${inline(value.slice(2))}</blockquote>`); return; }
        if (/^[-*] /.test(value)) { flushParagraph(); list.push(value.slice(2)); return; }
        flushList(); paragraph.push(value);
    });
    flushParagraph(); flushList();

    return output.join('');
}

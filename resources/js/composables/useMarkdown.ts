import { marked } from 'marked';
import hljs from 'highlight.js';

// Configure marked with syntax highlighting
marked.setOptions({
    highlight: function (code, lang) {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return hljs.highlight(code, { language: lang }).value;
            } catch (err) {
                console.error('Highlight error:', err);
            }
        }
        return hljs.highlightAuto(code).value;
    },
    breaks: true, // Convert \n to <br>
    gfm: true, // GitHub Flavored Markdown
});

/**
 * Render markdown text to HTML
 */
export function renderMarkdown(text: string): string {
    return marked.parse(text) as string;
}

/**
 * Composable for markdown rendering
 */
export function useMarkdown() {
    return {
        render: renderMarkdown,
    };
}

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, nextTick, computed } from 'vue';
import axios from 'axios';
import { renderMarkdown } from '@/composables/useMarkdown';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chat',
        href: '/chat',
    },
];

interface Message {
    role: 'user' | 'assistant';
    content: string;
    created_at?: string;
}

const messages = ref<Message[]>([]);
const newMessage = ref('');
const isLoading = ref(false);
const sessionId = ref('');
const messagesContainer = ref<HTMLElement | null>(null);

// Generate or retrieve session ID
onMounted(() => {
    const storedSessionId = localStorage.getItem('chat_session_id');
    if (storedSessionId) {
        sessionId.value = storedSessionId;
        loadHistory();
    } else {
        sessionId.value = generateSessionId();
        localStorage.setItem('chat_session_id', sessionId.value);
    }
});

function generateSessionId(): string {
    return 'web_' + Date.now() + '_' + Math.random().toString(36).substring(2, 15);
}

async function loadHistory() {
    try {
        const response = await axios.get(`/api/chat/history/${sessionId.value}`);
        if (response.data.success) {
            messages.value = response.data.messages;
            await nextTick();
            scrollToBottom();
        }
    } catch (error) {
        console.error('Failed to load history:', error);
    }
}

async function sendMessage() {
    if (!newMessage.value.trim() || isLoading.value) {
        return;
    }

    const userMessage = newMessage.value.trim();
    newMessage.value = '';

    // Add user message to UI immediately
    messages.value.push({
        role: 'user',
        content: userMessage,
        created_at: new Date().toISOString(),
    });

    await nextTick();
    scrollToBottom();

    isLoading.value = true;

    try {
        const response = await axios.post('/api/chat/send', {
            message: userMessage,
            session_id: sessionId.value,
        });

        if (response.data.success) {
            // Add AI response to UI
            messages.value.push({
                role: 'assistant',
                content: response.data.message,
                created_at: new Date().toISOString(),
            });

            await nextTick();
            scrollToBottom();
        } else {
            // Show error message
            messages.value.push({
                role: 'assistant',
                content: 'Sorry, I encountered an error. Please try again.',
                created_at: new Date().toISOString(),
            });
        }
    } catch (error) {
        console.error('Failed to send message:', error);
        messages.value.push({
            role: 'assistant',
            content: 'Sorry, I encountered an error. Please try again.',
            created_at: new Date().toISOString(),
        });
    } finally {
        isLoading.value = false;
    }
}

function scrollToBottom() {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
}

function handleKeypress(event: KeyboardEvent) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
}

// Render message content (markdown for AI, plain text for user)
function getMessageHtml(message: Message): string {
    if (message.role === 'assistant') {
        return renderMarkdown(message.content);
    }
    // For user messages, escape HTML and preserve line breaks
    return message.content.replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\n/g, '<br>');
}
</script>

<template>
    <Head title="AI Support Chat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col rounded-xl p-4">
            <div
                class="relative flex h-full flex-1 flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-zinc-900"
            >
                <!-- Chat Header -->
                <div
                    class="border-b border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                        AI Support Assistant
                    </h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        Powered by Google Gemini
                    </p>
                </div>

                <!-- Messages Container -->
                <div
                    ref="messagesContainer"
                    class="flex-1 space-y-4 overflow-y-auto p-4"
                >
                    <div
                        v-if="messages.length === 0"
                        class="flex h-full items-center justify-center text-center"
                    >
                        <div>
                            <p class="text-lg font-medium text-zinc-900 dark:text-white">
                                Welcome! How can I help you today?
                            </p>
                            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                                Start typing your question below
                            </p>
                        </div>
                    </div>

                    <div
                        v-for="(message, index) in messages"
                        :key="index"
                        :class="[
                            'flex',
                            message.role === 'user' ? 'justify-end' : 'justify-start',
                        ]"
                    >
                        <div
                            :class="[
                                'max-w-[80%] rounded-lg px-4 py-2',
                                message.role === 'user'
                                    ? 'bg-blue-500 text-white'
                                    : 'bg-zinc-100 text-zinc-900 dark:bg-zinc-800 dark:text-white',
                                message.role === 'assistant' ? 'prose prose-sm dark:prose-invert max-w-none' : '',
                            ]"
                        >
                            <div
                                v-if="message.role === 'assistant'"
                                class="markdown-content"
                                v-html="getMessageHtml(message)"
                            ></div>
                            <p
                                v-else
                                class="whitespace-pre-wrap break-words"
                                v-html="getMessageHtml(message)"
                            ></p>
                        </div>
                    </div>

                    <div
                        v-if="isLoading"
                        class="flex justify-start"
                    >
                        <div
                            class="max-w-[80%] rounded-lg bg-zinc-100 px-4 py-2 text-zinc-900 dark:bg-zinc-800 dark:text-white"
                        >
                            <p class="animate-pulse">Thinking...</p>
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div
                    class="border-t border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <div class="flex gap-2">
                        <textarea
                            v-model="newMessage"
                            @keypress="handleKeypress"
                            :disabled="isLoading"
                            placeholder="Type your message here..."
                            rows="2"
                            class="flex-1 resize-none rounded-lg border border-zinc-300 bg-white px-4 py-2 text-zinc-900 placeholder-zinc-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-400 dark:focus:border-blue-400 dark:focus:ring-blue-400"
                        ></textarea>
                        <button
                            @click="sendMessage"
                            :disabled="!newMessage.trim() || isLoading"
                            class="rounded-lg bg-blue-500 px-6 py-2 font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-zinc-900"
                        >
                            Send
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                        Press Enter to send, Shift+Enter for new line
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

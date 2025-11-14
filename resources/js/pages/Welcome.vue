<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const telegramBotUsername = (import.meta.env.VITE_TELEGRAM_BOT_USERNAME || 'YOUR_BOT_USERNAME').replace('@', '');
const telegramBotLink = computed(() => `https://t.me/${telegramBotUsername}`);
</script>

<template>
    <Head title="AI Support Bot">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-800"
    >
        <!-- Header -->
        <header class="w-full border-b border-zinc-200 bg-white/80 backdrop-blur-sm dark:border-zinc-800 dark:bg-zinc-900/80">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-2">
                    <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <h1 class="text-xl font-bold text-zinc-900 dark:text-white">
                        AI Support Bot
                    </h1>
                </div>
                <nav class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="mx-auto w-full max-w-7xl flex-1 px-6 py-16">
            <div class="grid gap-12 lg:grid-cols-2 lg:gap-8">
                <!-- Left Column: Description -->
                <div class="flex flex-col justify-center">
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-blue-100 px-4 py-1.5 text-sm font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        Powered by Google Gemini AI
                    </div>

                    <h2 class="mb-4 text-4xl font-bold tracking-tight text-zinc-900 dark:text-white lg:text-5xl">
                        Get Instant Support,<br />
                        <span class="text-blue-600 dark:text-blue-400">Anytime, Anywhere</span>
                    </h2>

                    <p class="mb-8 text-lg text-zinc-600 dark:text-zinc-400">
                        Our AI-powered support bot is available 24/7 to answer your questions
                        and help you solve problems. Chat directly from your browser or connect
                        via Telegram for support on the go.
                    </p>

                    <!-- Features -->
                    <div class="mb-8 space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 rounded-full bg-green-100 p-1 dark:bg-green-900/30">
                                <svg class="h-4 w-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 dark:text-white">
                                    Instant AI Responses
                                </h3>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    Get immediate answers powered by advanced AI technology
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="mt-1 rounded-full bg-blue-100 p-1 dark:bg-blue-900/30">
                                <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 dark:text-white">
                                    Multi-Channel Support
                                </h3>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    Chat via web browser or Telegram - your choice
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="mt-1 rounded-full bg-purple-100 p-1 dark:bg-purple-900/30">
                                <svg class="h-4 w-4 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 dark:text-white">
                                    Conversation History
                                </h3>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    Your chat history is saved for seamless conversations
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="mt-1 rounded-full bg-orange-100 p-1 dark:bg-orange-900/30">
                                <svg class="h-4 w-4 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-zinc-900 dark:text-white">
                                    24/7 Availability
                                </h3>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                    Get help anytime, day or night
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="flex flex-wrap gap-4">
                        <Link
                            href="/chat"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white shadow-lg transition hover:bg-blue-700 hover:shadow-xl"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                            Start Web Chat
                        </Link>

                        <a
                            :href="telegramBotLink"
                            target="_blank"
                            class="inline-flex items-center gap-2 rounded-lg border-2 border-zinc-300 bg-white px-6 py-3 font-semibold text-zinc-700 transition hover:border-zinc-400 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-zinc-600 dark:hover:bg-zinc-700"
                        >
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.18 1.897-.962 6.502-1.359 8.627-.168.9-.5 1.201-.82 1.23-.697.064-1.226-.461-1.901-.903-1.056-.692-1.653-1.123-2.678-1.799-1.185-.781-.417-1.21.258-1.911.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.248-.024c-.106.024-1.793 1.139-5.062 3.345-.479.329-.913.489-1.302.481-.428-.009-1.252-.242-1.865-.442-.752-.244-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.831-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635.099-.002.321.023.465.141.121.099.154.232.17.326.016.094.036.308.02.475z"/>
                            </svg>
                            Chat on Telegram
                        </a>
                    </div>
                </div>

                <!-- Right Column: Preview/Image -->
                <div class="flex items-center justify-center">
                    <div class="relative w-full max-w-lg">
                        <!-- Decorative elements -->
                        <div class="absolute -left-4 -top-4 h-72 w-72 rounded-full bg-purple-300 opacity-20 blur-3xl dark:bg-purple-600"></div>
                        <div class="absolute -bottom-8 -right-4 h-72 w-72 rounded-full bg-blue-300 opacity-20 blur-3xl dark:bg-blue-600"></div>

                        <!-- Chat Preview -->
                        <div class="relative z-10 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
                            <!-- Chat Header -->
                            <div class="border-b border-zinc-200 bg-gradient-to-r from-blue-600 to-purple-600 p-4 dark:border-zinc-800">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-white">AI Support Assistant</h3>
                                        <p class="text-xs text-blue-100">Online</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Chat Messages -->
                            <div class="space-y-4 bg-zinc-50 p-4 dark:bg-zinc-950">
                                <div class="flex justify-start">
                                    <div class="max-w-[80%] rounded-lg bg-white p-3 shadow dark:bg-zinc-800">
                                        <p class="text-sm text-zinc-900 dark:text-white">
                                            Hello! How can I help you today?
                                        </p>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <div class="max-w-[80%] rounded-lg bg-blue-600 p-3 shadow">
                                        <p class="text-sm text-white">
                                            I need help with my account
                                        </p>
                                    </div>
                                </div>

                                <div class="flex justify-start">
                                    <div class="max-w-[80%] rounded-lg bg-white p-3 shadow dark:bg-zinc-800">
                                        <p class="text-sm text-zinc-900 dark:text-white">
                                            I'd be happy to help you with your account. What specific issue are you experiencing?
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Chat Input Preview -->
                            <div class="border-t border-zinc-200 p-3 dark:border-zinc-800">
                                <div class="flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800">
                                    <input
                                        type="text"
                                        placeholder="Type your message..."
                                        disabled
                                        class="flex-1 border-0 bg-transparent text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none dark:text-white dark:placeholder-zinc-500"
                                    />
                                    <button disabled class="text-blue-600 dark:text-blue-400">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="mt-20 grid gap-8 rounded-2xl border border-zinc-200 bg-white p-8 shadow-lg dark:border-zinc-800 dark:bg-zinc-900 md:grid-cols-3">
                <div class="text-center">
                    <div class="mb-2 text-4xl font-bold text-blue-600 dark:text-blue-400">24/7</div>
                    <div class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Available Support</div>
                </div>
                <div class="text-center">
                    <div class="mb-2 text-4xl font-bold text-purple-600 dark:text-purple-400">2</div>
                    <div class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Communication Channels</div>
                </div>
                <div class="text-center">
                    <div class="mb-2 text-4xl font-bold text-green-600 dark:text-green-400">AI</div>
                    <div class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Powered by Gemini</div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-zinc-200 bg-white py-8 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mx-auto max-w-7xl px-6 text-center">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Built with Laravel, Vue.js, Inertia.js, and Google Gemini AI
                </p>
            </div>
        </footer>
    </div>
</template>

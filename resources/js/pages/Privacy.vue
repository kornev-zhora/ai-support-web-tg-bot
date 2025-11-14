<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Privacy Policy',
        href: '/privacy',
    },
];
</script>

<template>
    <Head title="Privacy Policy" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-4xl p-6">
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-8 dark:border-sidebar-border dark:bg-zinc-900">
                <h1 class="mb-8 text-3xl font-bold text-zinc-900 dark:text-white">
                    Privacy Policy
                </h1>

                <div class="prose prose-zinc max-w-none dark:prose-invert">
                    <p class="text-lg text-zinc-600 dark:text-zinc-400">
                        Last updated: {{ new Date().toLocaleDateString() }}
                    </p>

                    <h2>Data Processing and Storage</h2>
                    <p>
                        Our AI Support Bot processes and stores your chat messages to provide intelligent responses. 
                        Messages are temporarily stored in Redis with a 24-hour Time To Live (TTL) for performance optimization. 
                        After 24 hours, messages are automatically deleted. Conversation metadata and statistics are 
                        permanently stored in our PostgreSQL database.
                    </p>

                    <h2>Your Rights Under GDPR</h2>
                    <p>
                        As a data subject under the General Data Protection Regulation (GDPR), you have specific rights 
                        regarding your personal data:
                    </p>

                    <h3>Article 15 - Right of Access</h3>
                    <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                        <p class="font-semibold text-blue-900 dark:text-blue-100">
                            You have the right to obtain confirmation as to whether or not personal data concerning you are being processed.
                        </p>
                        <p class="mt-2 text-blue-800 dark:text-blue-200">
                            This includes the right to access your personal data and receive information about:
                        </p>
                        <ul class="mt-2 text-blue-800 dark:text-blue-200">
                            <li>The purposes of the processing</li>
                            <li>The categories of personal data concerned</li>
                            <li>The recipients or categories of recipients to whom data has been disclosed</li>
                            <li>The envisaged period for which data will be stored</li>
                            <li>Your rights to rectification, erasure, or restriction of processing</li>
                        </ul>
                    </div>

                    <h3>Article 17 - Right to Erasure ("Right to be Forgotten")</h3>
                    <div class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                        <p class="font-semibold text-red-900 dark:text-red-100">
                            You have the right to obtain the erasure of personal data concerning you without undue delay.
                        </p>
                        <p class="mt-2 text-red-800 dark:text-red-200">
                            This right applies when one of the following grounds applies:
                        </p>
                        <ul class="mt-2 text-red-800 dark:text-red-200">
                            <li>The personal data are no longer necessary for the purposes for which they were collected</li>
                            <li>You withdraw consent and there is no other legal ground for processing</li>
                            <li>You object to processing and there are no overriding legitimate grounds</li>
                            <li>The personal data have been unlawfully processed</li>
                            <li>Erasure is required for compliance with a legal obligation</li>
                        </ul>
                    </div>

                    <h2>Data Retention</h2>
                    <p>
                        <strong>Chat Messages:</strong> Stored in Redis for 24 hours, then automatically deleted.<br>
                        <strong>Conversation Metadata:</strong> Stored permanently in PostgreSQL for service improvement and analytics.
                    </p>

                    <h2>Exercising Your Rights</h2>
                    <p>
                        To exercise your rights under Articles 15 and 17, or for any privacy-related inquiries, 
                        please contact us at:
                    </p>
                    <div class="rounded-lg bg-zinc-100 p-4 dark:bg-zinc-800">
                        <p class="font-mono text-sm">
                            Email: privacy@example.com<br>
                            Subject: GDPR Request - [Your Request Type]
                        </p>
                    </div>

                    <h2>Data Controller</h2>
                    <p>
                        The data controller responsible for your personal data is the operator of this AI Support Bot service.
                    </p>

                    <h2>Changes to This Policy</h2>
                    <p>
                        We may update this privacy policy from time to time. Any changes will be posted on this page 
                        with an updated revision date.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
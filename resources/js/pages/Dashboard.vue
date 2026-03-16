<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import GroupController from '@/actions/App/Http/Controllers/GroupController';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type Props = {
    canCreateHomeGroup: boolean;
    homeGroup: {
        id: number;
        name: string;
    } | null;
    status?: string;
    homeGroupName?: string;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

const page = usePage();
const user = computed(() => page.props.auth.user);
const hasHomeGroup = computed(() => props.homeGroup !== null);

const nextSteps = computed(() => [
    {
        title: 'Admin account ready',
        description: 'Your HomeDuty account is configured as the household admin account.',
        value: user.value.is_group_admin ? 'Ready' : 'Pending',
    },
    {
        title: 'Primary contact',
        description: 'This phone number will be used for group coordination and reminders.',
        value: user.value.phone_number ?? 'Add in profile settings',
    },
    {
        title: 'Home Group setup',
        description: hasHomeGroup.value
            ? 'Your household is ready for member invitations and shared duty planning.'
            : 'Create your first shared group to anchor member invitations and duty scheduling.',
        value: hasHomeGroup.value ? props.homeGroup?.name : props.canCreateHomeGroup ? 'Ready to create' : 'Restricted',
    },
]);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-3xl p-4 md:p-6">
            <section class="rounded-3xl border border-border/70 bg-linear-to-br from-white via-stone-50 to-slate-100 p-6 shadow-sm dark:from-sidebar dark:via-sidebar dark:to-sidebar-accent/40">
                <div class="max-w-2xl space-y-3">
                    <p class="text-sm font-medium uppercase tracking-[0.24em] text-muted-foreground">
                        HomeDuty admin workspace
                    </p>
                    <Heading
                        title="Your admin account is ready"
                        description="Feature 1 is active: your account can sign in, manage its profile, and is marked as the initial Home Group admin."
                    />
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-3">
                <article
                    v-for="step in nextSteps"
                    :key="step.title"
                    class="rounded-2xl border border-border/70 bg-background/90 p-5 shadow-sm transition-transform duration-200 ease-out hover:-translate-y-0.5"
                >
                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-muted-foreground">
                        {{ step.title }}
                    </p>
                    <p class="mt-3 text-2xl font-semibold text-foreground">
                        {{ step.value }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-muted-foreground">
                        {{ step.description }}
                    </p>
                </article>
            </section>

            <section class="rounded-3xl border border-dashed border-border/80 bg-muted/30 p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">
                            {{ hasHomeGroup ? 'Home Group created' : 'Create your first Home Group' }}
                        </h2>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground">
                            {{ hasHomeGroup
                                ? 'Your admin account now owns the household workspace. The next feature will add invitations and member management.'
                                : 'This creates the shared household workspace, sets you as the owner, and adds you as the first admin member.' }}
                        </p>
                        <p
                            v-if="status === 'home-group-created' && homeGroupName"
                            class="mt-3 text-sm font-medium text-foreground"
                        >
                            {{ homeGroupName }} is ready.
                        </p>
                    </div>

                    <Button v-if="canCreateHomeGroup" as-child>
                        <Link :href="GroupController.create()">Create Home Group</Link>
                    </Button>
                </div>

                <p class="mt-4 text-sm font-medium text-foreground">
                    Signed in as {{ user.name }}
                </p>
                <p class="text-sm text-muted-foreground">
                    {{ user.email }}
                </p>
                <p class="text-sm text-muted-foreground">
                    {{ user.phone_number ?? 'Add a phone number in profile settings' }}
                </p>
                <p class="mt-2 text-sm text-muted-foreground">
                    {{ hasHomeGroup ? `Current group: ${props.homeGroup?.name}` : 'No home group created yet.' }}
                </p>
            </section>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import GroupController from '@/actions/App/Http/Controllers/GroupController';
import GroupDutyController from '@/actions/App/Http/Controllers/GroupDutyController';
import GroupInvitationController from '@/actions/App/Http/Controllers/GroupInvitationController';
import GroupMemberController from '@/actions/App/Http/Controllers/GroupMemberController';
import { Badge } from '@/components/ui/badge';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type UpcomingDuty = {
    date: string;
    type: string;
    icon: string;
};

type Props = {
    canCreateHomeGroup: boolean;
    canManageHomeGroupDuties: boolean;
    canManageHomeGroupMembers: boolean;
    canViewHomeGroupDuties: boolean;
    canViewHomeGroupMembers: boolean;
    homeGroup: {
        dutiesCount: number;
        id: number;
        name: string;
        memberCount: number;
        pendingInvitationsCount: number;
        isOwner: boolean;
    } | null;
    pendingInvitation: {
        token: string;
        groupName: string;
        email: string;
        roleLabel: string;
        expiresAt: string | null;
    } | null;
    upcomingDuties: UpcomingDuty[];
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
const homeGroupId = computed(() => props.homeGroup?.id ?? 0);

function formatDate(dateStr: string): string {
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
    });
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Hero welcome -->
            <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-600 via-orange-500 to-amber-500 p-8 text-white shadow-lg dark:from-amber-800 dark:via-orange-700 dark:to-amber-700">
                <div class="absolute -top-8 -right-8 h-40 w-40 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-6 -left-6 h-28 w-28 rounded-full bg-white/5"></div>
                <div class="relative">
                    <p class="text-sm font-medium tracking-wide text-amber-100">
                        Welcome back
                    </p>
                    <h1 class="mt-1 text-2xl font-bold md:text-3xl">
                        {{ user.name }}
                    </h1>
                    <p class="mt-2 max-w-lg text-sm leading-relaxed text-amber-100">
                        {{ hasHomeGroup
                            ? `Managing ${props.homeGroup?.name} with ${props.homeGroup?.memberCount} members`
                            : 'Get started by creating your Home Group' }}
                    </p>
                </div>
            </section>

            <!-- Stat cards -->
            <section v-if="hasHomeGroup" class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-border/60 bg-card p-5 shadow-sm transition-shadow hover:shadow-md">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-lg dark:bg-amber-900/40">
                            👥
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-foreground">{{ props.homeGroup?.memberCount }}</p>
                            <p class="text-xs text-muted-foreground">Active Members</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-border/60 bg-card p-5 shadow-sm transition-shadow hover:shadow-md">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100 text-lg dark:bg-orange-900/40">
                            📋
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-foreground">{{ props.homeGroup?.dutiesCount }}</p>
                            <p class="text-xs text-muted-foreground">Planned Duties</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-border/60 bg-card p-5 shadow-sm transition-shadow hover:shadow-md">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-yellow-100 text-lg dark:bg-yellow-900/40">
                            ✉️
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-foreground">{{ props.homeGroup?.pendingInvitationsCount }}</p>
                            <p class="text-xs text-muted-foreground">Pending Invitations</p>
                        </div>
                    </div>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Upcoming duties -->
                <section class="rounded-2xl border border-border/60 bg-card shadow-sm">
                    <div class="border-b border-border/40 px-6 py-4">
                        <h2 class="font-semibold text-foreground">Your Upcoming Duties</h2>
                    </div>
                    <div class="p-6">
                        <div v-if="upcomingDuties.length > 0" class="space-y-3">
                            <div
                                v-for="(duty, i) in upcomingDuties"
                                :key="i"
                                class="flex items-center gap-4 rounded-xl border border-border/40 bg-background/60 px-4 py-3 transition-colors hover:bg-accent/30"
                            >
                                <span class="text-xl">{{ duty.icon }}</span>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-foreground">{{ duty.type }}</p>
                                    <p class="text-xs text-muted-foreground">{{ formatDate(duty.date) }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="py-8 text-center">
                            <p class="text-3xl">🎉</p>
                            <p class="mt-2 text-sm text-muted-foreground">No upcoming duties</p>
                        </div>
                    </div>
                </section>

                <!-- Quick actions -->
                <section class="rounded-2xl border border-border/60 bg-card shadow-sm">
                    <div class="border-b border-border/40 px-6 py-4">
                        <h2 class="font-semibold text-foreground">Quick Actions</h2>
                    </div>
                    <div class="space-y-3 p-6">
                        <Button v-if="canCreateHomeGroup" as-child class="w-full justify-start gap-3">
                            <Link :href="GroupController.create()">
                                <span class="text-lg">🏠</span>
                                Create Home Group
                            </Link>
                        </Button>

                        <Button v-if="hasHomeGroup && canViewHomeGroupMembers" variant="outline" as-child class="w-full justify-start gap-3">
                            <Link :href="GroupMemberController.index(homeGroupId)">
                                <span class="text-lg">👥</span>
                                {{ canManageHomeGroupMembers ? 'Manage Members' : 'View Members' }}
                            </Link>
                        </Button>

                        <Button v-if="hasHomeGroup && canViewHomeGroupDuties" variant="outline" as-child class="w-full justify-start gap-3">
                            <Link :href="GroupDutyController.index(homeGroupId)">
                                <span class="text-lg">📋</span>
                                {{ canManageHomeGroupDuties ? 'Plan Duties' : 'View Duties' }}
                            </Link>
                        </Button>

                        <p
                            v-if="status === 'home-group-created' && homeGroupName"
                            class="rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400"
                        >
                            ✓ {{ homeGroupName }} is ready.
                        </p>
                    </div>
                </section>
            </div>

            <!-- Pending invitation -->
            <section v-if="pendingInvitation" class="rounded-2xl border-2 border-amber-300 bg-amber-50/50 p-6 shadow-sm dark:border-amber-700 dark:bg-amber-950/20">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">📨</span>
                            <div>
                                <h2 class="font-semibold text-foreground">Pending Invitation</h2>
                                <Badge variant="outline" class="mt-1">{{ pendingInvitation.roleLabel }}</Badge>
                            </div>
                        </div>
                        <div class="mt-3 space-y-1 text-sm text-muted-foreground">
                            <p>Group: <span class="font-medium text-foreground">{{ pendingInvitation.groupName }}</span></p>
                            <p>Email: <span class="font-medium text-foreground">{{ pendingInvitation.email }}</span></p>
                            <p>Expires {{ pendingInvitation.expiresAt ? new Date(pendingInvitation.expiresAt).toLocaleDateString() : 'soon' }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <Form v-bind="GroupInvitationController.accept.form(pendingInvitation.token)" v-slot="{ processing }">
                            <Button type="submit" :disabled="processing">
                                <Spinner v-if="processing" />
                                Accept
                            </Button>
                        </Form>
                        <Button variant="outline" as-child>
                            <Link :href="GroupInvitationController.show(pendingInvitation.token)">Review</Link>
                        </Button>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

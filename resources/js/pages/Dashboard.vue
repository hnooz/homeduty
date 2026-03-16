<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import GroupController from '@/actions/App/Http/Controllers/GroupController';
import GroupDutyController from '@/actions/App/Http/Controllers/GroupDutyController';
import GroupInvitationController from '@/actions/App/Http/Controllers/GroupInvitationController';
import GroupMemberController from '@/actions/App/Http/Controllers/GroupMemberController';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

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
    {
        title: 'Member roster',
        description: hasHomeGroup.value
            ? 'Track active members and pending invitations from one place.'
            : 'Create your Home Group before inviting or managing members.',
        value: hasHomeGroup.value ? `${props.homeGroup?.memberCount} active` : 'Unavailable',
    },
    {
        title: 'Duty plan',
        description: hasHomeGroup.value
            ? 'Build the recurring household duties list and assign each item to the right person.'
            : 'A Home Group is required before you can plan shared duties.',
        value: hasHomeGroup.value ? `${props.homeGroup?.dutiesCount} planned` : 'Unavailable',
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

            <section class="grid gap-4 md:grid-cols-4">
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

            <Card v-if="pendingInvitation" class="rounded-3xl border-border/70 shadow-sm">
                <CardHeader>
                    <div class="flex flex-wrap items-center gap-3">
                        <CardTitle>Pending invitation</CardTitle>
                        <Badge variant="outline">{{ pendingInvitation.roleLabel }}</Badge>
                    </div>
                    <CardDescription>
                        You have a Home Group invitation waiting and can accept it directly from the dashboard.
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="space-y-2 text-sm text-muted-foreground">
                        <p>
                            Group: <span class="font-medium text-foreground">{{ pendingInvitation.groupName }}</span>
                        </p>
                        <p>
                            Invited email: <span class="font-medium text-foreground">{{ pendingInvitation.email }}</span>
                        </p>
                        <p>
                            Expires {{ pendingInvitation.expiresAt ? new Date(pendingInvitation.expiresAt).toLocaleDateString() : 'soon' }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <Form v-bind="GroupInvitationController.accept.form(pendingInvitation.token)" v-slot="{ processing }">
                            <Button type="submit" :disabled="processing">
                                <Spinner v-if="processing" />
                                Accept invitation
                            </Button>
                        </Form>

                        <Button variant="outline" as-child>
                            <Link :href="GroupInvitationController.show(pendingInvitation.token)">Review invitation</Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <section class="rounded-3xl border border-dashed border-border/80 bg-muted/30 p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">
                            {{ hasHomeGroup ? 'Home Group ready' : 'Create your first Home Group' }}
                        </h2>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground">
                            {{ hasHomeGroup
                                ? 'Your household workspace is active. Feature 4 starts the shared duty planner with recurring assignments for the group.'
                                : 'This creates the shared household workspace, sets you as the owner, and adds you as the first admin member.' }}
                        </p>
                        <p
                            v-if="status === 'home-group-created' && homeGroupName"
                            class="mt-3 text-sm font-medium text-foreground"
                        >
                            {{ homeGroupName }} is ready.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <Button v-if="canCreateHomeGroup" as-child>
                            <Link :href="GroupController.create()">Create Home Group</Link>
                        </Button>

                        <Button v-if="hasHomeGroup && canViewHomeGroupMembers" variant="outline" as-child>
                            <Link :href="GroupMemberController.index(homeGroupId)">
                                {{ canManageHomeGroupMembers ? 'Manage members' : 'View members' }}
                            </Link>
                        </Button>

                        <Button v-if="hasHomeGroup && canViewHomeGroupDuties" variant="outline" as-child>
                            <Link :href="GroupDutyController.index(homeGroupId)">
                                {{ canManageHomeGroupDuties ? 'Plan duties' : 'View duties' }}
                            </Link>
                        </Button>
                    </div>
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
                <p v-if="hasHomeGroup" class="text-sm text-muted-foreground">
                    {{ props.homeGroup?.memberCount }} active members, {{ props.homeGroup?.pendingInvitationsCount }} pending invitations.
                </p>
                <p v-if="hasHomeGroup" class="text-sm text-muted-foreground">
                    {{ props.homeGroup?.dutiesCount }} duties currently planned.
                </p>
                <p v-if="hasHomeGroup && props.homeGroup?.isOwner" class="text-sm text-muted-foreground">
                    You are the owner of this Home Group.
                </p>
            </section>
        </div>
    </AppLayout>
</template>

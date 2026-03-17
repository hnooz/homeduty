<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import GroupInvitationController from '@/actions/App/Http/Controllers/GroupInvitationController';
import GroupMemberController from '@/actions/App/Http/Controllers/GroupMemberController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type RoleOption = {
    value: string;
    label: string;
};

type GroupMember = {
    id: number;
    userId: number;
    name: string;
    email: string;
    phoneNumber: string | null;
    role: string;
    roleLabel: string;
    isOwner: boolean;
};

type GroupInvitation = {
    token: string;
    name: string;
    email: string;
    phoneNumber: string | null;
    role: string;
    roleLabel: string;
    expiresAt: string | null;
    hasRegisteredUser: boolean;
    registeredUserName: string | null;
};

type Props = {
    group: {
        id: number;
        name: string;
        ownerId: number;
    };
    members: GroupMember[];
    pendingInvitations: GroupInvitation[];
    roleOptions: RoleOption[];
    canManageMembers: boolean;
    status?: string;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
    {
        title: 'Members',
        href: GroupMemberController.index(props.group.id),
    },
];

const page = usePage();
const currentUser = computed(() => page.props.auth.user);
</script>

<template>
    <Head :title="`${group.name} members`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-3xl p-4 md:p-6">
            <section class="rounded-3xl border border-border/70 bg-linear-to-br from-stone-50 via-white to-slate-100 p-6 shadow-sm dark:from-sidebar dark:via-sidebar dark:to-sidebar-accent/40">
                <Heading
                    :title="`${group.name} members`"
                    :description="canManageMembers
                        ? 'Invite new members, adjust admin access, and keep the household roster current.'
                        : 'View the active roster and the pending invitations for your Home Group.'"
                />
                <p
                    v-if="status"
                    class="mt-4 text-sm font-medium text-foreground"
                >
                    {{ status.replaceAll('-', ' ') }}
                </p>
            </section>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(22rem,1fr)]">
                <div class="space-y-6">
                    <Card class="rounded-3xl border-border/70 shadow-sm">
                        <CardHeader>
                            <CardTitle>Active members</CardTitle>
                            <CardDescription>
                                {{ members.length }} people currently belong to this Home Group.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <article
                                v-for="member in members"
                                :key="member.id"
                                class="rounded-2xl border border-border/70 bg-background/90 p-4"
                            >
                                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-base font-semibold text-foreground">
                                                {{ member.name }}
                                            </p>
                                            <Badge variant="outline">
                                                {{ member.roleLabel }}
                                            </Badge>
                                            <Badge v-if="member.isOwner" variant="secondary">
                                                Owner
                                            </Badge>
                                            <Badge v-if="member.userId === currentUser.id" variant="secondary">
                                                You
                                            </Badge>
                                        </div>
                                        <p class="text-sm text-muted-foreground">
                                            {{ member.email }}
                                        </p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ member.phoneNumber ?? 'No phone number on file' }}
                                        </p>
                                    </div>

                                    <div
                                        v-if="canManageMembers && !member.isOwner"
                                        class="flex flex-col gap-3 md:min-w-72"
                                    >
                                        <Form
                                            v-bind="GroupMemberController.update.form({ group: group.id, groupMember: member.id })"
                                            class="flex flex-col gap-3 sm:flex-row"
                                            v-slot="{ errors, processing }"
                                        >
                                            <div class="grid flex-1 gap-2">
                                                <Label :for="`role-${member.id}`">Role</Label>
                                                <select
                                                    :id="`role-${member.id}`"
                                                    name="role"
                                                    :default-value="member.role"
                                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground"
                                                >
                                                    <option
                                                        v-for="option in roleOptions"
                                                        :key="option.value"
                                                        :value="option.value"
                                                    >
                                                        {{ option.label }}
                                                    </option>
                                                </select>
                                                <InputError :message="errors.role" />
                                            </div>

                                            <Button type="submit" :disabled="processing" class="self-end">
                                                <Spinner v-if="processing" />
                                                Save role
                                            </Button>
                                        </Form>

                                        <Form
                                            v-bind="GroupMemberController.destroy.form({ group: group.id, groupMember: member.id })"
                                            v-slot="{ processing }"
                                        >
                                            <Button type="submit" variant="destructive" :disabled="processing">
                                                <Spinner v-if="processing" />
                                                Remove member
                                            </Button>
                                        </Form>
                                    </div>
                                </div>
                            </article>
                        </CardContent>
                    </Card>

                    <Card class="rounded-3xl border-border/70 shadow-sm">
                        <CardHeader>
                            <CardTitle>Pending invitations</CardTitle>
                            <CardDescription>
                                Invitations stay active for seven days or until they are accepted.
                            </CardDescription>
                        </CardHeader>
                        <CardContent v-if="pendingInvitations.length" class="space-y-4">
                            <article
                                v-for="invitation in pendingInvitations"
                                :key="invitation.token"
                                class="rounded-2xl border border-dashed border-border/80 bg-muted/20 p-4"
                            >
                                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-base font-semibold text-foreground">
                                                {{ invitation.name }}
                                            </p>
                                            <Badge variant="outline">
                                                {{ invitation.roleLabel }}
                                            </Badge>
                                        </div>
                                        <p class="text-sm text-muted-foreground">
                                            {{ invitation.email }}
                                        </p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ invitation.phoneNumber ?? 'No phone number supplied' }}
                                        </p>
                                        <p class="text-sm text-muted-foreground">
                                            Expires {{ invitation.expiresAt ? new Date(invitation.expiresAt).toLocaleDateString() : 'soon' }}
                                        </p>
                                    </div>

                                    <div v-if="canManageMembers" class="flex flex-col gap-3 md:items-end">
                                        <Form
                                            v-bind="GroupInvitationController.acceptDirect.form({ group: group.id, groupInvitation: invitation.token })"
                                            v-slot="{ processing: accepting }"
                                        >
                                            <Button type="submit" :disabled="accepting">
                                                <Spinner v-if="accepting" />
                                                Accept directly
                                            </Button>
                                        </Form>

                                        <p v-if="invitation.hasRegisteredUser" class="text-xs text-muted-foreground md:text-right">
                                            {{ invitation.registeredUserName }} already has an account and can be added immediately.
                                        </p>
                                        <p v-else class="text-xs text-muted-foreground md:text-right">
                                            An account will be created and a password reset link will be sent to {{ invitation.email }}.
                                        </p>

                                        <Form
                                            v-bind="GroupInvitationController.destroy.form({ group: group.id, groupInvitation: invitation.token })"
                                            v-slot="{ processing }"
                                        >
                                            <Button type="submit" variant="outline" :disabled="processing">
                                                <Spinner v-if="processing" />
                                                Cancel invitation
                                            </Button>
                                        </Form>
                                    </div>
                                </div>
                            </article>
                        </CardContent>
                        <CardContent v-else class="text-sm text-muted-foreground">
                            No invitations are pending right now.
                        </CardContent>
                    </Card>
                </div>

                <Card v-if="canManageMembers" class="rounded-3xl border-border/70 shadow-sm">
                    <CardHeader>
                        <CardTitle>Invite a member</CardTitle>
                        <CardDescription>
                            Send a seven-day invitation to a roommate, partner, or family member.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            v-bind="GroupInvitationController.store.form(group.id)"
                            class="space-y-6"
                            :reset-on-success="['name', 'email', 'phone_number']"
                            v-slot="{ errors, processing }"
                        >
                            <div class="grid gap-2">
                                <Label for="name">Name</Label>
                                <Input id="name" name="name" required placeholder="Alex Rivera" />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email">Email address</Label>
                                <Input id="email" name="email" type="email" required placeholder="alex@example.com" />
                                <InputError :message="errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="phone_number">Phone number</Label>
                                <Input id="phone_number" name="phone_number" type="tel" placeholder="+1 555 123 4567" />
                                <InputError :message="errors.phone_number" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="role">Role</Label>
                                <select
                                    id="role"
                                    name="role"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground"
                                >
                                    <option v-for="option in roleOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <InputError :message="errors.role" />
                            </div>

                            <Button type="submit" class="w-full" :disabled="processing">
                                <Spinner v-if="processing" />
                                Send invitation
                            </Button>
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
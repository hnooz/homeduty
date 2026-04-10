<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import GroupInvitationController from '@/actions/App/Http/Controllers/GroupInvitationController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type Props = {
    invitation: {
        groupName: string;
        name: string;
        email: string;
        role: string;
        roleLabel: string;
        token: string;
        expiresAt: string | null;
    };
};

const props = defineProps<Props>();
const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
    {
        title: 'Accept invitation',
        href: GroupInvitationController.show(props.invitation.token),
    },
];
</script>

<template>
    <Head title="Accept invitation" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 items-start justify-center p-4 md:p-6">
            <Card
                class="w-full max-w-2xl rounded-3xl border-border/70 shadow-sm"
            >
                <CardHeader>
                    <Heading
                        title="Accept your Home Group invitation"
                        :description="`Join ${invitation.groupName} with the account currently signed in.`"
                    />
                </CardHeader>
                <CardContent class="space-y-6">
                    <div
                        class="rounded-2xl border border-border/70 bg-muted/30 p-5"
                    >
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-base font-semibold text-foreground">
                                {{ invitation.groupName }}
                            </p>
                            <Badge variant="outline">{{
                                invitation.roleLabel
                            }}</Badge>
                        </div>
                        <p class="mt-3 text-sm text-muted-foreground">
                            Invited for {{ invitation.email }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            Signed in as {{ page.props.auth.user.email }}
                        </p>
                        <p class="mt-3 text-sm text-muted-foreground">
                            Expires
                            {{
                                invitation.expiresAt
                                    ? new Date(
                                          invitation.expiresAt,
                                      ).toLocaleDateString()
                                    : 'soon'
                            }}
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-dashed border-border/80 bg-background/80 p-5 text-sm leading-6 text-muted-foreground"
                    >
                        Accepting this invitation will attach your account to
                        the Home Group and apply the invited role immediately.
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <Form
                            v-bind="
                                GroupInvitationController.accept.form(
                                    invitation.token,
                                )
                            "
                            v-slot="{ processing }"
                        >
                            <Button type="submit" :disabled="processing">
                                <Spinner v-if="processing" />
                                Accept invitation
                            </Button>
                        </Form>

                        <Button variant="outline" as-child>
                            <Link :href="dashboard()">Back to dashboard</Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

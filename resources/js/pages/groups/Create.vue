<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import GroupController from '@/actions/App/Http/Controllers/GroupController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
    {
        title: 'Create Home Group',
        href: GroupController.create(),
    },
];
</script>

<template>
    <Head title="Create Home Group" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-3xl p-4 md:p-6">
            <section class="rounded-3xl border border-border/70 bg-linear-to-br from-stone-50 via-white to-slate-100 p-6 shadow-sm dark:from-sidebar dark:via-sidebar dark:to-sidebar-accent/40">
                <Heading
                    title="Create your Home Group"
                    description="Set up the shared household workspace that will own members, schedules, and upcoming duties."
                />
            </section>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(22rem,1fr)]">
                <Card class="rounded-3xl border-border/70 shadow-sm">
                    <CardHeader>
                        <CardTitle>Group details</CardTitle>
                        <CardDescription>
                            Choose a clear name for the home you and your members live in.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            v-bind="GroupController.store.form()"
                            class="space-y-6"
                            :reset-on-success="['name']"
                            v-slot="{ errors, processing }"
                        >
                            <div class="grid gap-2">
                                <Label for="name">Home Group name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    required
                                    autofocus
                                    autocomplete="organization"
                                    placeholder="Maple Street Apartment"
                                />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row">
                                <Button type="submit" :disabled="processing">
                                    <Spinner v-if="processing" />
                                    Create Home Group
                                </Button>

                                <Button variant="outline" as-child>
                                    <Link :href="dashboard()">Cancel</Link>
                                </Button>
                            </div>
                        </Form>
                    </CardContent>
                </Card>

                <Card class="rounded-3xl border-border/70 bg-muted/30 shadow-sm">
                    <CardHeader>
                        <CardTitle>What happens next</CardTitle>
                        <CardDescription>
                            Feature 2 creates the base household structure for the rest of HomeDuty.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm leading-6 text-muted-foreground">
                        <div>
                            <p class="font-medium text-foreground">You become the owner</p>
                            <p>The group is linked to your admin account as the initial owner.</p>
                        </div>
                        <div>
                            <p class="font-medium text-foreground">You are added as the first member</p>
                            <p>Your membership is created immediately with the admin role.</p>
                        </div>
                        <div>
                            <p class="font-medium text-foreground">Member invitations come next</p>
                            <p>Feature 3 will use this group to invite roommates and assign roles.</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
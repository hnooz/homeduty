<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import GroupDutyController from '@/actions/App/Http/Controllers/GroupDutyController';
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

type Duty = {
    id: number;
    name: string;
    description: string | null;
    frequency: string;
    frequencyLabel: string;
    startsOn: string | null;
    assignedUser: {
        id: number;
        name: string;
        email: string;
    } | null;
};

type Option = {
    value: number | string;
    label: string;
    description?: string;
};

type Props = {
    group: {
        id: number;
        name: string;
    };
    duties: Duty[];
    assigneeOptions: Option[];
    frequencyOptions: Option[];
    canManageDuties: boolean;
    status?: string;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
    {
        title: 'Duties',
        href: GroupDutyController.index(props.group.id),
    },
];
</script>

<template>
    <Head :title="`${group.name} duties`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-3xl p-4 md:p-6">
            <section class="rounded-3xl border border-border/70 bg-linear-to-br from-amber-50 via-white to-lime-50 p-6 shadow-sm dark:from-sidebar dark:via-sidebar dark:to-sidebar-accent/40">
                <Heading
                    :title="`${group.name} duties`"
                    :description="canManageDuties
                        ? 'Plan recurring household work, assign each duty, and keep the schedule visible to everyone in the group.'
                        : 'View the current household duty plan and see who is assigned to each recurring responsibility.'"
                />
                <p v-if="status" class="mt-4 text-sm font-medium text-foreground">
                    {{ status.replaceAll('-', ' ') }}
                </p>
            </section>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(22rem,1fr)]">
                <Card class="rounded-3xl border-border/70 shadow-sm">
                    <CardHeader>
                        <CardTitle>Planned duties</CardTitle>
                        <CardDescription>
                            {{ duties.length }} recurring duties are currently planned for this Home Group.
                        </CardDescription>
                    </CardHeader>
                    <CardContent v-if="duties.length" class="space-y-4">
                        <article
                            v-for="duty in duties"
                            :key="duty.id"
                            class="rounded-2xl border border-border/70 bg-background/90 p-4"
                        >
                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-base font-semibold text-foreground">
                                            {{ duty.name }}
                                        </p>
                                        <Badge variant="outline">
                                            {{ duty.frequencyLabel }}
                                        </Badge>
                                    </div>
                                    <p v-if="duty.description" class="text-sm leading-6 text-muted-foreground">
                                        {{ duty.description }}
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        Starts {{ duty.startsOn ? new Date(duty.startsOn).toLocaleDateString() : 'soon' }}
                                    </p>
                                </div>

                                <div v-if="canManageDuties" class="w-full rounded-2xl border border-border/70 bg-muted/20 p-4 md:max-w-md">
                                    <Form
                                        v-bind="GroupDutyController.update.form({ group: group.id, duty: duty.id })"
                                        class="space-y-4"
                                        v-slot="{ errors, processing }"
                                    >
                                        <div class="grid gap-2">
                                            <Label :for="`duty-name-${duty.id}`">Duty name</Label>
                                            <Input :id="`duty-name-${duty.id}`" name="name" :value="duty.name" required />
                                            <InputError :message="errors.name" />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label :for="`duty-description-${duty.id}`">Description</Label>
                                            <textarea
                                                :id="`duty-description-${duty.id}`"
                                                name="description"
                                                rows="3"
                                                :value="duty.description ?? ''"
                                                class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground"
                                            />
                                            <InputError :message="errors.description" />
                                        </div>

                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div class="grid gap-2">
                                                <Label :for="`duty-frequency-${duty.id}`">Frequency</Label>
                                                <select
                                                    :id="`duty-frequency-${duty.id}`"
                                                    name="frequency"
                                                    :default-value="duty.frequency"
                                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground"
                                                >
                                                    <option
                                                        v-for="option in frequencyOptions"
                                                        :key="String(option.value)"
                                                        :value="option.value"
                                                    >
                                                        {{ option.label }}
                                                    </option>
                                                </select>
                                                <InputError :message="errors.frequency" />
                                            </div>

                                            <div class="grid gap-2">
                                                <Label :for="`duty-starts-on-${duty.id}`">Starts on</Label>
                                                <Input :id="`duty-starts-on-${duty.id}`" name="starts_on" type="date" :value="duty.startsOn ?? ''" required />
                                                <InputError :message="errors.starts_on" />
                                            </div>
                                        </div>

                                        <div class="grid gap-2">
                                            <Label :for="`duty-assigned-user-${duty.id}`">Assign to</Label>
                                            <select
                                                :id="`duty-assigned-user-${duty.id}`"
                                                name="assigned_user_id"
                                                :default-value="duty.assignedUser?.id ?? ''"
                                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground"
                                            >
                                                <option value="">Leave unassigned</option>
                                                <option
                                                    v-for="option in assigneeOptions"
                                                    :key="String(option.value)"
                                                    :value="option.value"
                                                >
                                                    {{ option.label }}
                                                </option>
                                            </select>
                                            <InputError :message="errors.assigned_user_id" />
                                        </div>

                                        <div class="flex flex-col gap-3 sm:flex-row">
                                            <Button type="submit" :disabled="processing">
                                                <Spinner v-if="processing" />
                                                Save duty
                                            </Button>
                                        </div>
                                    </Form>

                                    <Form
                                        v-bind="GroupDutyController.destroy.form({ group: group.id, duty: duty.id })"
                                        class="mt-3"
                                        v-slot="{ processing }"
                                    >
                                        <Button type="submit" variant="destructive" :disabled="processing">
                                            <Spinner v-if="processing" />
                                            Remove duty
                                        </Button>
                                    </Form>
                                </div>

                                <div v-else class="rounded-2xl border border-dashed border-border/80 bg-muted/20 px-4 py-3 text-sm">
                                    <p class="font-medium text-foreground">
                                        {{ duty.assignedUser ? duty.assignedUser.name : 'Unassigned' }}
                                    </p>
                                    <p class="text-muted-foreground">
                                        {{ duty.assignedUser ? duty.assignedUser.email : 'Choose an assignee to make ownership clear.' }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    </CardContent>
                    <CardContent v-else class="text-sm leading-6 text-muted-foreground">
                        No duties have been planned yet. Once the first recurring task is added, the full group will be able to see it here.
                    </CardContent>
                </Card>

                <Card v-if="canManageDuties" class="rounded-3xl border-border/70 shadow-sm">
                    <CardHeader>
                        <CardTitle>Add a duty</CardTitle>
                        <CardDescription>
                            Start with the recurring work that keeps the household running, then assign each item to a member.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            v-bind="GroupDutyController.store.form(group.id)"
                            class="space-y-6"
                            :reset-on-success="['name', 'description', 'assigned_user_id']"
                            v-slot="{ errors, processing }"
                        >
                            <div class="grid gap-2">
                                <Label for="name">Duty name</Label>
                                <Input id="name" name="name" required placeholder="Trash collection" />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="description">Description</Label>
                                <textarea
                                    id="description"
                                    name="description"
                                    rows="4"
                                    placeholder="Set out the bins, replace the liners, and bring the bins back in."
                                    class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground"
                                />
                                <InputError :message="errors.description" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="frequency">Frequency</Label>
                                <select
                                    id="frequency"
                                    name="frequency"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground"
                                >
                                    <option
                                        v-for="option in frequencyOptions"
                                        :key="String(option.value)"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <InputError :message="errors.frequency" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="starts_on">Starts on</Label>
                                <Input id="starts_on" name="starts_on" type="date" required />
                                <InputError :message="errors.starts_on" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="assigned_user_id">Assign to</Label>
                                <select
                                    id="assigned_user_id"
                                    name="assigned_user_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground"
                                >
                                    <option value="">Leave unassigned</option>
                                    <option
                                        v-for="option in assigneeOptions"
                                        :key="String(option.value)"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <p class="text-xs text-muted-foreground">
                                    {{ assigneeOptions.length ? assigneeOptions[0].description : 'Add members to the group to assign duties.' }}
                                </p>
                                <InputError :message="errors.assigned_user_id" />
                            </div>

                            <Button type="submit" class="w-full" :disabled="processing">
                                <Spinner v-if="processing" />
                                Add duty
                            </Button>
                        </Form>
                    </CardContent>
                </Card>

                <Card v-else class="rounded-3xl border-border/70 shadow-sm">
                    <CardHeader>
                        <CardTitle>Planning access</CardTitle>
                        <CardDescription>
                            Owners and accepted admins can add new duties and assign them across the household.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Button variant="outline" as-child>
                            <Link :href="dashboard()">Back to dashboard</Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
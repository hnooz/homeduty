<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import GroupDutyController from '@/actions/App/Http/Controllers/GroupDutyController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type DutyMember = {
    id: number;
    name: string;
};

type DutySlot = {
    date: string;
    userName: string;
    userId: number;
};

type Duty = {
    id: number;
    type: string;
    typeLabel: string;
    typeIcon: string;
    startsOn: string | null;
    members: DutyMember[];
    upcomingSlots: DutySlot[];
};

type MemberOption = {
    value: number;
    label: string;
};

type TypeOption = {
    value: string;
    label: string;
    icon: string;
};

type Props = {
    group: {
        id: number;
        name: string;
    };
    duties: Duty[];
    memberOptions: MemberOption[];
    typeOptions: TypeOption[];
    canManageDuties: boolean;
    status?: string;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Duties', href: GroupDutyController.index(props.group.id) },
];

// Tag-style multi-select for create form
const selectedMemberIds = ref<number[]>([]);

function toggleMember(id: number): void {
    const index = selectedMemberIds.value.indexOf(id);

    if (index === -1) {
        selectedMemberIds.value.push(id);
    } else {
        selectedMemberIds.value.splice(index, 1);
    }
}

function isMemberSelected(id: number): boolean {
    return selectedMemberIds.value.includes(id);
}

// Tag-style multi-select for edit form
const editingDutyId = ref<number | null>(null);
const editSelectedMemberIds = ref<number[]>([]);

function startEditing(duty: Duty): void {
    editingDutyId.value = duty.id;
    editSelectedMemberIds.value = duty.members.map((m) => m.id);
}

function cancelEditing(): void {
    editingDutyId.value = null;
    editSelectedMemberIds.value = [];
}

function toggleEditMember(id: number): void {
    const index = editSelectedMemberIds.value.indexOf(id);

    if (index === -1) {
        editSelectedMemberIds.value.push(id);
    } else {
        editSelectedMemberIds.value.splice(index, 1);
    }
}

function isEditMemberSelected(id: number): boolean {
    return editSelectedMemberIds.value.includes(id);
}

const availableMembers = computed(() =>
    props.memberOptions.filter((m) => !isMemberSelected(m.value)),
);

const editAvailableMembers = computed(() =>
    props.memberOptions.filter((m) => !isEditMemberSelected(m.value)),
);

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString(undefined, {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
    });
}
</script>

<template>
    <Head :title="`${group.name} duties`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-3xl p-4 md:p-6"
        >
            <!-- Header -->
            <section
                class="rounded-3xl border border-indigo-200/70 bg-gradient-to-br from-indigo-50 via-purple-50 to-violet-50 p-6 shadow-sm"
            >
                <Heading
                    :title="`${group.name} duties`"
                    :description="
                        canManageDuties
                            ? 'Choose a duty type, pick a start date, and select members to begin the rotation.'
                            : 'View the current duty rotation schedule for your household.'
                    "
                />
                <p
                    v-if="status"
                    class="mt-4 text-sm font-medium text-indigo-800"
                >
                    {{ status.replaceAll('-', ' ') }}
                </p>
            </section>

            <div
                class="grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(22rem,1fr)]"
            >
                <!-- Duty list -->
                <div class="space-y-4">
                    <template v-if="duties.length">
                        <Card
                            v-for="duty in duties"
                            :key="duty.id"
                            class="rounded-3xl border-indigo-200/50 shadow-sm"
                        >
                            <CardHeader class="pb-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl">{{
                                            duty.typeIcon
                                        }}</span>
                                        <CardTitle class="text-lg">{{
                                            duty.typeLabel
                                        }}</CardTitle>
                                    </div>
                                    <Badge
                                        variant="outline"
                                        class="border-indigo-300 text-indigo-700"
                                    >
                                        Starts
                                        {{
                                            duty.startsOn
                                                ? formatDate(duty.startsOn)
                                                : 'soon'
                                        }}
                                    </Badge>
                                </div>
                                <CardDescription>
                                    {{ duty.members.length }} member{{
                                        duty.members.length !== 1 ? 's' : ''
                                    }}
                                    in rotation
                                </CardDescription>
                            </CardHeader>

                            <CardContent class="space-y-4">
                                <!-- Members tags -->
                                <div class="flex flex-wrap gap-2">
                                    <Badge
                                        v-for="member in duty.members"
                                        :key="member.id"
                                        class="bg-indigo-100 text-indigo-800"
                                    >
                                        {{ member.name }}
                                    </Badge>
                                </div>

                                <!-- Upcoming rotation schedule -->
                                <div
                                    v-if="duty.upcomingSlots.length"
                                    class="rounded-2xl border border-indigo-100 bg-indigo-50/50 p-3"
                                >
                                    <p
                                        class="mb-2 text-xs font-medium tracking-wider text-indigo-600 uppercase"
                                    >
                                        Upcoming schedule
                                    </p>
                                    <div class="grid gap-1">
                                        <div
                                            v-for="(
                                                slot, i
                                            ) in duty.upcomingSlots"
                                            :key="i"
                                            class="flex items-center justify-between rounded-lg px-2 py-1.5 text-sm"
                                            :class="
                                                i === 0
                                                    ? 'bg-indigo-200/50 font-medium'
                                                    : ''
                                            "
                                        >
                                            <span class="text-indigo-900">{{
                                                formatDate(slot.date)
                                            }}</span>
                                            <span class="text-indigo-700">{{
                                                slot.userName
                                            }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Admin edit/delete -->
                                <div
                                    v-if="canManageDuties"
                                    class="flex gap-2 pt-2"
                                >
                                    <Button
                                        v-if="editingDutyId !== duty.id"
                                        variant="outline"
                                        size="sm"
                                        class="border-indigo-300 text-indigo-700 hover:bg-indigo-50"
                                        @click="startEditing(duty)"
                                    >
                                        Edit
                                    </Button>

                                    <Form
                                        v-bind="
                                            GroupDutyController.destroy.form({
                                                group: group.id,
                                                duty: duty.id,
                                            })
                                        "
                                        v-slot="{ processing }"
                                    >
                                        <Button
                                            type="submit"
                                            variant="destructive"
                                            size="sm"
                                            :disabled="processing"
                                        >
                                            <Spinner v-if="processing" />
                                            Remove
                                        </Button>
                                    </Form>
                                </div>

                                <!-- Edit form (inline) -->
                                <div
                                    v-if="
                                        canManageDuties &&
                                        editingDutyId === duty.id
                                    "
                                    class="rounded-2xl border border-indigo-200 bg-white p-4"
                                >
                                    <Form
                                        v-bind="
                                            GroupDutyController.update.form({
                                                group: group.id,
                                                duty: duty.id,
                                            })
                                        "
                                        class="space-y-4"
                                        v-slot="{ errors, processing }"
                                        @success="cancelEditing()"
                                    >
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div class="grid gap-2">
                                                <Label
                                                    :for="`edit-type-${duty.id}`"
                                                    >Type</Label
                                                >
                                                <select
                                                    :id="`edit-type-${duty.id}`"
                                                    name="type"
                                                    :value="duty.type"
                                                    class="flex h-10 w-full rounded-lg border border-indigo-200 bg-white px-3 py-2 text-sm"
                                                >
                                                    <option
                                                        v-for="opt in typeOptions"
                                                        :key="opt.value"
                                                        :value="opt.value"
                                                    >
                                                        {{ opt.icon }}
                                                        {{ opt.label }}
                                                    </option>
                                                </select>
                                                <InputError
                                                    :message="errors.type"
                                                />
                                            </div>

                                            <div class="grid gap-2">
                                                <Label
                                                    :for="`edit-starts-on-${duty.id}`"
                                                    >Start date</Label
                                                >
                                                <Input
                                                    :id="`edit-starts-on-${duty.id}`"
                                                    name="starts_on"
                                                    type="date"
                                                    :value="duty.startsOn ?? ''"
                                                    required
                                                />
                                                <InputError
                                                    :message="errors.starts_on"
                                                />
                                            </div>
                                        </div>

                                        <!-- Member multi-select (edit) -->
                                        <div class="grid gap-2">
                                            <Label>Members</Label>
                                            <div class="flex flex-wrap gap-1.5">
                                                <button
                                                    v-for="id in editSelectedMemberIds"
                                                    :key="id"
                                                    type="button"
                                                    class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-800 transition-colors hover:bg-indigo-200"
                                                    @click="
                                                        toggleEditMember(id)
                                                    "
                                                >
                                                    {{
                                                        memberOptions.find(
                                                            (m) =>
                                                                m.value === id,
                                                        )?.label
                                                    }}
                                                    <span
                                                        class="text-indigo-500"
                                                        >&times;</span
                                                    >
                                                </button>
                                            </div>
                                            <div
                                                v-if="
                                                    editAvailableMembers.length
                                                "
                                                class="flex flex-wrap gap-1.5"
                                            >
                                                <button
                                                    v-for="member in editAvailableMembers"
                                                    :key="member.value"
                                                    type="button"
                                                    class="inline-flex items-center rounded-full border border-dashed border-indigo-300 px-3 py-1 text-sm text-indigo-600 transition-colors hover:bg-indigo-50"
                                                    @click="
                                                        toggleEditMember(
                                                            member.value,
                                                        )
                                                    "
                                                >
                                                    + {{ member.label }}
                                                </button>
                                            </div>
                                            <input
                                                v-for="id in editSelectedMemberIds"
                                                :key="`edit-hidden-${id}`"
                                                type="hidden"
                                                name="member_ids[]"
                                                :value="id"
                                            />
                                            <InputError
                                                :message="errors['member_ids']"
                                            />
                                        </div>

                                        <div class="flex gap-2">
                                            <Button
                                                type="submit"
                                                size="sm"
                                                :disabled="processing"
                                                class="bg-indigo-600 hover:bg-indigo-700"
                                            >
                                                <Spinner v-if="processing" />
                                                Save changes
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                @click="cancelEditing()"
                                            >
                                                Cancel
                                            </Button>
                                        </div>
                                    </Form>
                                </div>
                            </CardContent>
                        </Card>
                    </template>

                    <Card
                        v-else
                        class="rounded-3xl border-indigo-200/50 shadow-sm"
                    >
                        <CardContent
                            class="py-8 text-center text-sm text-indigo-700"
                        >
                            No duties planned yet. Add a duty to start the
                            rotation schedule.
                        </CardContent>
                    </Card>
                </div>

                <!-- Create form (right side) -->
                <Card
                    v-if="canManageDuties"
                    class="h-fit rounded-3xl border-indigo-200/50 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle>Add a duty</CardTitle>
                        <CardDescription>
                            Pick a type, set a start date, and select members
                            for the rotation.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            v-bind="GroupDutyController.store.form(group.id)"
                            class="space-y-5"
                            v-slot="{ errors, processing }"
                            @success="selectedMemberIds = []"
                        >
                            <div class="grid gap-2">
                                <Label for="type">Duty type</Label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label
                                        v-for="opt in typeOptions"
                                        :key="opt.value"
                                        class="flex cursor-pointer items-center gap-2 rounded-xl border-2 px-4 py-3 transition-colors has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50"
                                        :class="'border-indigo-200 hover:border-indigo-300'"
                                    >
                                        <input
                                            type="radio"
                                            name="type"
                                            :value="opt.value"
                                            class="sr-only"
                                        />
                                        <span class="text-xl">{{
                                            opt.icon
                                        }}</span>
                                        <span class="text-sm font-medium">{{
                                            opt.label
                                        }}</span>
                                    </label>
                                </div>
                                <InputError :message="errors.type" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="starts_on">Start date</Label>
                                <Input
                                    id="starts_on"
                                    name="starts_on"
                                    type="date"
                                    required
                                />
                                <InputError :message="errors.starts_on" />
                            </div>

                            <div class="grid gap-2">
                                <Label>Select members</Label>
                                <!-- Selected members as tags -->
                                <div
                                    v-if="selectedMemberIds.length"
                                    class="flex flex-wrap gap-1.5"
                                >
                                    <button
                                        v-for="id in selectedMemberIds"
                                        :key="id"
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-800 transition-colors hover:bg-indigo-200"
                                        @click="toggleMember(id)"
                                    >
                                        {{
                                            memberOptions.find(
                                                (m) => m.value === id,
                                            )?.label
                                        }}
                                        <span class="text-indigo-500"
                                            >&times;</span
                                        >
                                    </button>
                                </div>
                                <!-- Available members to add -->
                                <div
                                    v-if="availableMembers.length"
                                    class="flex flex-wrap gap-1.5"
                                >
                                    <button
                                        v-for="member in availableMembers"
                                        :key="member.value"
                                        type="button"
                                        class="inline-flex items-center rounded-full border border-dashed border-indigo-300 px-3 py-1 text-sm text-indigo-600 transition-colors hover:bg-indigo-50"
                                        @click="toggleMember(member.value)"
                                    >
                                        + {{ member.label }}
                                    </button>
                                </div>
                                <p
                                    v-if="!memberOptions.length"
                                    class="text-xs text-indigo-600"
                                >
                                    Accept members into the group first to
                                    assign duties.
                                </p>
                                <!-- Hidden inputs for form submission -->
                                <input
                                    v-for="id in selectedMemberIds"
                                    :key="`hidden-${id}`"
                                    type="hidden"
                                    name="member_ids[]"
                                    :value="id"
                                />
                                <InputError :message="errors['member_ids']" />
                            </div>

                            <Button
                                type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700"
                                :disabled="
                                    processing || !selectedMemberIds.length
                                "
                            >
                                <Spinner v-if="processing" />
                                Create duty
                            </Button>
                        </Form>
                    </CardContent>
                </Card>

                <Card
                    v-else
                    class="h-fit rounded-3xl border-indigo-200/50 shadow-sm"
                >
                    <CardHeader>
                        <CardTitle>Duty schedule</CardTitle>
                        <CardDescription>
                            Only group admins can create and manage duty
                            rotations.
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

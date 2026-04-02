<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit, update } from '@/routes/email-settings';
import type { BreadcrumbItem } from '@/types';

type Props = {
    emailSettings: Record<string, string | null>;
};

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Email settings',
        href: edit(),
    },
];

const selectedMailer = ref(props.emailSettings.mail_mailer ?? 'log');
const isSmtp = computed(() => selectedMailer.value === 'smtp');
const isResend = computed(() => selectedMailer.value === 'resend');
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Email settings" />

        <h1 class="sr-only">Email settings</h1>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Email configuration"
                    description="Configure outgoing email settings for the application"
                />

                <Form
                    method="patch"
                    :action="update().url"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="mail_mailer">Mailer</Label>
                        <Select
                            name="mail_mailer"
                            :default-value="selectedMailer"
                            @update:model-value="(val) => (selectedMailer = val as string)"
                        >
                            <SelectTrigger id="mail_mailer">
                                <SelectValue placeholder="Select mailer" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="resend">Resend</SelectItem>
                                <SelectItem value="smtp">SMTP</SelectItem>
                                <SelectItem value="sendmail">Sendmail</SelectItem>
                                <SelectItem value="log">Log (development)</SelectItem>
                                <SelectItem value="array">Array (testing)</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError class="mt-2" :message="errors.mail_mailer" />
                    </div>

                    <template v-if="isResend">
                        <div class="grid gap-2">
                            <Label for="resend_api_key">Resend API Key</Label>
                            <Input
                                id="resend_api_key"
                                type="password"
                                class="mt-1 block w-full"
                                name="resend_api_key"
                                :default-value="emailSettings.resend_api_key ?? ''"
                                required
                                placeholder="re_••••••••••••••••"
                                autocomplete="off"
                            />
                            <InputError class="mt-2" :message="errors.resend_api_key" />
                        </div>
                    </template>

                    <template v-if="isSmtp">
                        <div class="grid gap-2">
                            <Label for="mail_host">SMTP Host</Label>
                            <Input
                                id="mail_host"
                                class="mt-1 block w-full"
                                name="mail_host"
                                :default-value="emailSettings.mail_host ?? ''"
                                placeholder="smtp.example.com"
                            />
                            <InputError class="mt-2" :message="errors.mail_host" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="mail_port">SMTP Port</Label>
                            <Input
                                id="mail_port"
                                type="number"
                                class="mt-1 block w-full"
                                name="mail_port"
                                :default-value="emailSettings.mail_port ?? '587'"
                                placeholder="587"
                            />
                            <InputError class="mt-2" :message="errors.mail_port" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="mail_encryption">Encryption</Label>
                            <Select name="mail_encryption" :default-value="emailSettings.mail_encryption ?? 'tls'">
                                <SelectTrigger id="mail_encryption">
                                    <SelectValue placeholder="Select encryption" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="tls">TLS</SelectItem>
                                    <SelectItem value="ssl">SSL</SelectItem>
                                    <SelectItem value="">None</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-2" :message="errors.mail_encryption" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="mail_username">SMTP Username</Label>
                            <Input
                                id="mail_username"
                                class="mt-1 block w-full"
                                name="mail_username"
                                :default-value="emailSettings.mail_username ?? ''"
                                placeholder="username@example.com"
                                autocomplete="username"
                            />
                            <InputError class="mt-2" :message="errors.mail_username" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="mail_password">SMTP Password</Label>
                            <Input
                                id="mail_password"
                                type="password"
                                class="mt-1 block w-full"
                                name="mail_password"
                                :default-value="emailSettings.mail_password ?? ''"
                                placeholder="••••••••"
                                autocomplete="current-password"
                            />
                            <InputError class="mt-2" :message="errors.mail_password" />
                        </div>
                    </template>

                    <div class="grid gap-2">
                        <Label for="mail_from_address">From Address</Label>
                        <Input
                            id="mail_from_address"
                            type="email"
                            class="mt-1 block w-full"
                            name="mail_from_address"
                            :default-value="emailSettings.mail_from_address ?? ''"
                            required
                            placeholder="noreply@example.com"
                        />
                        <InputError class="mt-2" :message="errors.mail_from_address" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="mail_from_name">From Name</Label>
                        <Input
                            id="mail_from_name"
                            class="mt-1 block w-full"
                            name="mail_from_name"
                            :default-value="emailSettings.mail_from_name ?? ''"
                            required
                            placeholder="My App"
                        />
                        <InputError class="mt-2" :message="errors.mail_from_name" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing">Save</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

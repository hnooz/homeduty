<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { dashboard, home, login, register } from '@/routes';

const steps = [
    {
        n: '01',
        title: 'Create your group',
        desc: 'Sign up and start a private household — just give it a name. No setup wizard, no team plans.',
    },
    {
        n: '02',
        title: 'Invite the people you live with',
        desc: 'Send an invite link to housemates or family. They join with one click — no account hoops.',
    },
    {
        n: '03',
        title: 'Add the duties',
        desc: 'List the chores that actually matter: dishes, cooking, trash, laundry. Set how often each repeats.',
    },
    {
        n: '04',
        title: 'Let rotation handle the rest',
        desc: 'HomeDuty assigns duties fairly across the group and rotates them automatically each cycle.',
    },
    {
        n: '05',
        title: 'Mark done, stay in sync',
        desc: 'Tap done when a duty is complete. Everyone in the house sees it instantly — no nagging needed.',
    },
];
</script>

<template>
    <Head title="How HomeDuty works" />

    <div class="hd-surface min-h-screen">
        <header
            class="sticky top-0 z-10 backdrop-blur"
            style="
                border-bottom: 1px solid var(--color-rule);
                background: rgba(255, 255, 255, 0.86);
            "
        >
            <div
                class="mx-auto flex max-w-[1000px] flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6 sm:py-4 md:gap-6 md:px-8"
            >
                <Link
                    :href="home()"
                    class="flex items-center gap-2.5 text-heading no-underline"
                >
                    <span
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-brand"
                        style="
                            box-shadow: rgba(50, 50, 93, 0.25) 0 6px 12px -6px;
                        "
                    >
                        <AppLogoIcon class="h-[18px] w-[18px] text-white" />
                    </span>
                    <span
                        class="text-base font-normal"
                        style="letter-spacing: -0.16px"
                    >
                        HomeDuty
                    </span>
                </Link>

                <div class="flex items-center gap-1.5 sm:gap-2.5">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="hd-btn hd-btn-primary"
                    >
                        Open dashboard
                    </Link>
                    <template v-else>
                        <Link :href="login()" class="hd-btn hd-btn-ghost">
                            Sign in
                        </Link>
                        <Link :href="register()" class="hd-btn hd-btn-primary">
                            Start now
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-[760px] px-4 pt-12 pb-16 sm:px-6 sm:pt-[72px] sm:pb-24 md:px-8">
            <section class="mb-10 sm:mb-14">
                <div class="hd-eyebrow mb-[18px]">How it works</div>
                <h1 class="hd-display mb-4">
                    Five steps to a fairer household.
                </h1>
                <p class="hd-lede max-w-[560px]">
                    HomeDuty replaces the group-chat back-and-forth with a
                    quiet, private rotation everyone can trust.
                </p>
            </section>

            <ol class="m-0 mb-16 flex list-none flex-col gap-2 p-0">
                <li
                    v-for="step in steps"
                    :key="step.n"
                    class="hd-card grid grid-cols-[48px_1fr] gap-4 sm:grid-cols-[64px_1fr] sm:gap-6"
                >
                    <div
                        class="pt-1 text-[28px] leading-none font-light text-brand"
                        style="
                            letter-spacing: -0.8px;
                            font-feature-settings: 'tnum';
                        "
                    >
                        {{ step.n }}
                    </div>
                    <div>
                        <h2
                            class="m-0 mb-1.5 text-lg font-normal text-heading"
                            style="letter-spacing: -0.36px"
                        >
                            {{ step.title }}
                        </h2>
                        <p
                            class="m-0 text-[15px] font-light text-body"
                            style="line-height: 1.55"
                        >
                            {{ step.desc }}
                        </p>
                    </div>
                </li>
            </ol>

            <section
                class="px-2 py-10 text-center sm:px-6 sm:py-12"
                style="border-top: 1px solid var(--color-rule)"
            >
                <h3
                    class="m-0 mb-5 text-2xl font-light"
                    style="letter-spacing: -0.6px"
                >
                    Ready to try it?
                </h3>
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="register()"
                        class="hd-btn hd-btn-lg hd-btn-primary"
                    >
                        Start now
                    </Link>
                    <Link
                        v-else
                        :href="dashboard()"
                        class="hd-btn hd-btn-lg hd-btn-primary"
                    >
                        Open dashboard
                    </Link>
                    <Link
                        :href="home()"
                        class="hd-btn hd-btn-lg hd-btn-outlined"
                    >
                        Back to home
                    </Link>
                </div>
            </section>
        </main>
    </div>
</template>

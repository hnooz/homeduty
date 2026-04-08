<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, howItWorks, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);

const rotation = [
    {
        initial: 'A',
        name: 'Ahmed',
        avatarBg: '#533afd',
        duty: 'Cleaning',
        dutyClass: 'bg-[#eef0ff] text-[#2e2b8c] border-[#d6d9fc]',
        day: 'Mon',
        status: 'Done',
        done: true,
    },
    {
        initial: 'S',
        name: 'Sara',
        avatarBg: '#ea2261',
        duty: 'Cooking',
        dutyClass: 'bg-[#ffe9f6] text-[#b41a72] border-[#ffd7ef]',
        day: 'Tue',
        status: 'Done',
        done: true,
    },
    {
        initial: 'O',
        name: 'Omar',
        avatarBg: '#2874ad',
        duty: 'Cleaning',
        dutyClass: 'bg-[#eef0ff] text-[#2e2b8c] border-[#d6d9fc]',
        day: 'Thu',
        status: 'Pending',
        done: false,
    },
    {
        initial: 'L',
        name: 'Lina',
        avatarBg: '#1c1e54',
        duty: 'Cooking',
        dutyClass: 'bg-[#ffe9f6] text-[#b41a72] border-[#ffd7ef]',
        day: 'Fri',
        status: 'Pending',
        done: false,
    },
];

const features = [
    {
        title: 'Fair rotation',
        desc: 'Duties rotate automatically — nobody stays on dishes forever.',
    },
    {
        title: 'Private by default',
        desc: 'Built for housemates and families, not enterprise teams.',
    },
    {
        title: 'Track & confirm',
        desc: 'Mark a duty done; everyone in the house sees it instantly.',
    },
];
</script>

<template>
    <Head title="HomeDuty — Shared chores, fairly handled">
        <meta
            name="description"
            content="HomeDuty is a quiet, fair chore scheduler for housemates and families. Create a private home group, share cleaning and cooking duties, and let automatic rotations handle the rest."
        />
        <meta
            property="og:title"
            content="HomeDuty — Shared chores, fairly handled"
        />
        <meta
            property="og:description"
            content="A quiet, fair chore scheduler for the people you actually live with."
        />
    </Head>

    <div class="hd-surface flex min-h-screen w-full flex-col">
        <!-- NAV -->
        <header
            class="flex-none border-b backdrop-blur"
            style="
                border-color: var(--color-rule);
                background: rgba(255, 255, 255, 0.86);
            "
        >
            <div
                class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6 sm:py-4 md:gap-6 md:px-8"
            >
                <img
                    src="/logo.png"
                    alt="HomeDuty"
                    class="h-9 w-auto sm:h-12"
                />

                <nav
                    class="hidden items-center gap-7 md:flex"
                    aria-label="Primary"
                >
                    <Link
                        :href="howItWorks()"
                        class="text-sm font-normal text-heading no-underline transition-colors hover:text-brand"
                    >
                        How it works
                    </Link>
                    <a
                        href="#features"
                        class="text-sm font-normal text-heading no-underline transition-colors hover:text-brand"
                    >
                        Features
                    </a>
                </nav>

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
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="hd-btn hd-btn-primary"
                        >
                            Start now
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- MAIN -->
        <main
            class="mx-auto grid w-full max-w-[1200px] flex-1 items-center gap-10 px-4 py-10 sm:px-6 sm:py-12 md:px-8 lg:grid-cols-[1.05fr_0.95fr] lg:gap-14 lg:py-0"
        >
            <section class="flex w-full max-w-[560px] flex-col gap-5 sm:gap-[22px]">
                <div class="hd-eyebrow self-start">
                    Household coordination, redesigned
                </div>

                <h1
                    class="m-0 font-light text-heading"
                    style="
                        font-size: clamp(32px, 6vw, 56px);
                        line-height: 1.05;
                        letter-spacing: -1.4px;
                    "
                >
                    Everyone in the house<br />
                    knows their duty.
                </h1>

                <p class="hd-lede max-w-[520px]">
                    HomeDuty is a quiet, fair scheduler for the people you
                    actually live with. Create a private group, share the
                    chores, and let rotations handle the rest — no spreadsheet,
                    no group-chat arguments, no nagging.
                </p>

                <div class="mt-1 flex flex-wrap items-center gap-3">
                    <Link
                        v-if="canRegister && !$page.props.auth.user"
                        :href="register()"
                        class="hd-btn hd-btn-primary hd-btn-lg"
                    >
                        Start now
                    </Link>
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="hd-btn hd-btn-primary hd-btn-lg"
                    >
                        Open dashboard
                    </Link>
                    <Link
                        :href="howItWorks()"
                        class="hd-btn hd-btn-outlined hd-btn-lg"
                    >
                        See how it works
                    </Link>
                </div>

                <ul
                    class="m-0 mt-1.5 flex list-none items-center gap-2.5 p-0 text-[13px] font-light text-body"
                >
                    <li>Private groups</li>
                    <li aria-hidden="true" class="text-brand-border">·</li>
                    <li>Fair rotation</li>
                    <li aria-hidden="true" class="text-brand-border">·</li>
                    <li>No app to install</li>
                </ul>
            </section>

            <aside
                class="relative hidden items-center justify-center p-2 lg:flex"
                aria-hidden="true"
            >
                <div
                    class="w-full max-w-[460px] overflow-hidden rounded-lg bg-white"
                    style="
                        border: 1px solid var(--color-rule);
                        box-shadow:
                            rgba(50, 50, 93, 0.25) 0 30px 45px -30px,
                            rgba(0, 0, 0, 0.1) 0 18px 36px -18px;
                    "
                >
                    <div
                        class="flex items-center gap-3 border-b px-4 py-3"
                        style="
                            border-color: var(--color-rule);
                            background: #fbfcfe;
                        "
                    >
                        <div class="inline-flex gap-[5px]">
                            <span
                                class="h-[9px] w-[9px] rounded-full"
                                style="background: #e5edf5"
                            />
                            <span
                                class="h-[9px] w-[9px] rounded-full"
                                style="background: #e5edf5"
                            />
                            <span
                                class="h-[9px] w-[9px] rounded-full"
                                style="background: #e5edf5"
                            />
                        </div>
                        <div
                            class="text-xs font-normal text-label"
                            style="letter-spacing: -0.12px"
                        >
                            This week · Maple House
                        </div>
                    </div>

                    <div class="px-1 py-1.5">
                        <div
                            v-for="(row, i) in rotation"
                            :key="row.name"
                            class="grid items-center px-3.5 py-3"
                            :class="i < rotation.length - 1 ? 'border-b' : ''"
                            style="
                                grid-template-columns: 1.4fr 1fr 0.6fr 0.8fr;
                                border-color: #f1f4f9;
                            "
                        >
                            <div
                                class="flex items-center gap-2.5 text-[13px] text-heading"
                            >
                                <span
                                    class="inline-flex h-[26px] w-[26px] items-center justify-center rounded-md text-[11px] font-normal text-white"
                                    :style="{ background: row.avatarBg }"
                                >
                                    {{ row.initial }}
                                </span>
                                <span>{{ row.name }}</span>
                            </div>
                            <div>
                                <span
                                    class="inline-flex items-center rounded-[4px] border px-2 py-px text-[11px]"
                                    :class="row.dutyClass"
                                >
                                    {{ row.duty }}
                                </span>
                            </div>
                            <div
                                class="text-[13px] text-body"
                                style="font-feature-settings: 'tnum'"
                            >
                                {{ row.day }}
                            </div>
                            <div
                                class="justify-self-end rounded-[4px] border px-2 py-px text-[11px]"
                                :class="
                                    row.done
                                        ? 'border-[rgba(21,190,83,0.4)] bg-[rgba(21,190,83,0.2)] text-[#108c3d]'
                                        : 'border-rule bg-white text-body'
                                "
                            >
                                {{ row.status }}
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-between border-t px-4 py-2.5 text-[11px] text-body"
                        style="
                            border-color: var(--color-rule);
                            background: #fbfcfe;
                        "
                    >
                        <span>Rotation · Week 14</span>
                        <span class="text-brand">Next swap → Mon</span>
                    </div>
                </div>

                <div
                    class="absolute top-1 right-0 inline-flex items-center gap-2 rounded-[4px] border bg-white px-2.5 py-1.5 text-xs font-normal text-heading"
                    style="
                        border-color: var(--color-rule);
                        box-shadow: rgba(23, 23, 23, 0.06) 0 3px 6px;
                    "
                >
                    <span
                        class="inline-block h-[7px] w-[7px] rounded-full"
                        style="
                            background: #15be53;
                            box-shadow: 0 0 0 3px rgba(21, 190, 83, 0.18);
                        "
                    />
                    Sara marked Cooking · Done
                </div>
            </aside>
        </main>

        <!-- FEATURE STRIP -->
        <footer
            id="features"
            class="mx-auto grid w-full max-w-[1200px] flex-none gap-6 border-t px-4 py-6 sm:grid-cols-2 sm:gap-8 sm:px-6 sm:py-[22px] md:px-8 lg:grid-cols-3"
            style="border-color: var(--color-rule); background: #fbfcfe"
        >
            <div v-for="f in features" :key="f.title">
                <div
                    class="mb-1 text-sm font-normal text-heading"
                    style="letter-spacing: -0.14px"
                >
                    {{ f.title }}
                </div>
                <div
                    class="text-[13px] font-light text-body"
                    style="line-height: 1.4"
                >
                    {{ f.desc }}
                </div>
            </div>
        </footer>
    </div>
</template>

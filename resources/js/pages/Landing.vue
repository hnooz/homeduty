<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="HomeDuty — Shared chores, fairly handled" />

    <div class="page">
        <!-- NAV -->
        <header class="nav">
            <div class="nav-inner">
                <div class="brand">
                    <span class="brand-mark">
                        <AppLogoIcon class="brand-icon" />
                    </span>
                    <span class="brand-name">HomeDuty</span>
                </div>

                <nav class="nav-links" aria-label="Primary">
                    <a href="#how">How it works</a>
                    <a href="#features">Features</a>
                </nav>

                <div class="nav-cta">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="btn btn-primary"
                    >
                        Open dashboard
                    </Link>
                    <template v-else>
                        <Link :href="login()" class="btn btn-ghost">
                            Sign in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="btn btn-primary"
                        >
                            Start now
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- MAIN -->
        <main class="main">
            <section class="hero">
                <div class="eyebrow">
                    <span class="eyebrow-dot" /> Household coordination,
                    redesigned
                </div>

                <h1 class="display">
                    Everyone in the house<br />
                    knows their duty.
                </h1>

                <p class="lede">
                    HomeDuty is a quiet, fair scheduler for the people you
                    actually live with. Create a private group, share the
                    chores, and let rotations handle the rest — no spreadsheet,
                    no group-chat arguments, no nagging.
                </p>

                <div class="cta-row">
                    <Link
                        v-if="canRegister && !$page.props.auth.user"
                        :href="register()"
                        class="btn btn-primary btn-lg"
                    >
                        Start now
                    </Link>
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="btn btn-primary btn-lg"
                    >
                        Open dashboard
                    </Link>
                    <a href="#how" class="btn btn-outlined btn-lg">
                        See how it works
                    </a>
                </div>

                <ul class="proof">
                    <li>Private groups</li>
                    <li class="dot" aria-hidden="true">·</li>
                    <li>Fair rotation</li>
                    <li class="dot" aria-hidden="true">·</li>
                    <li>No app to install</li>
                </ul>
            </section>

            <aside class="mockup-wrap" aria-hidden="true">
                <div class="mockup">
                    <div class="mock-head">
                        <div class="mock-dots"><span /><span /><span /></div>
                        <div class="mock-title">This week · Maple House</div>
                    </div>

                    <div class="mock-body">
                        <div class="mock-row">
                            <div class="mock-cell person">
                                <span class="avatar a1">A</span>
                                <span>Ahmed</span>
                            </div>
                            <div class="mock-cell">
                                <span class="badge badge-clean">Cleaning</span>
                            </div>
                            <div class="mock-cell day">Mon</div>
                            <div class="mock-cell status done">Done</div>
                        </div>

                        <div class="mock-row">
                            <div class="mock-cell person">
                                <span class="avatar a2">S</span>
                                <span>Sara</span>
                            </div>
                            <div class="mock-cell">
                                <span class="badge badge-cook">Cooking</span>
                            </div>
                            <div class="mock-cell day">Tue</div>
                            <div class="mock-cell status done">Done</div>
                        </div>

                        <div class="mock-row">
                            <div class="mock-cell person">
                                <span class="avatar a3">O</span>
                                <span>Omar</span>
                            </div>
                            <div class="mock-cell">
                                <span class="badge badge-clean">Cleaning</span>
                            </div>
                            <div class="mock-cell day">Thu</div>
                            <div class="mock-cell status pending">Pending</div>
                        </div>

                        <div class="mock-row">
                            <div class="mock-cell person">
                                <span class="avatar a4">L</span>
                                <span>Lina</span>
                            </div>
                            <div class="mock-cell">
                                <span class="badge badge-cook">Cooking</span>
                            </div>
                            <div class="mock-cell day">Fri</div>
                            <div class="mock-cell status pending">Pending</div>
                        </div>
                    </div>

                    <div class="mock-foot">
                        <span class="rotation">Rotation · Week 14</span>
                        <span class="next">Next swap → Mon</span>
                    </div>
                </div>

                <div class="chip">
                    <span class="chip-dot" /> Sara marked Cooking · Done
                </div>
            </aside>
        </main>

        <!-- FEATURE STRIP -->
        <footer class="strip" id="features">
            <div class="feat">
                <div class="feat-title">Fair rotation</div>
                <div class="feat-desc">
                    Duties rotate automatically — nobody stays on dishes
                    forever.
                </div>
            </div>
            <div class="feat">
                <div class="feat-title">Private by default</div>
                <div class="feat-desc">
                    Built for housemates and families, not enterprise teams.
                </div>
            </div>
            <div class="feat">
                <div class="feat-title">Track &amp; confirm</div>
                <div class="feat-desc">
                    Mark a duty done; everyone in the house sees it instantly.
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* ---------- Stripe-inspired tokens (DESIGN.md) ---------- */
.page {
    --c-bg: #ffffff;
    --c-heading: #061b31;
    --c-label: #273951;
    --c-body: #64748d;
    --c-border: #e5edf5;
    --c-border-purple: #b9b9f9;
    --c-purple: #533afd;
    --c-purple-hover: #4434d4;
    --c-purple-soft: rgba(83, 58, 253, 0.05);
    --c-success-bg: rgba(21, 190, 83, 0.2);
    --c-success-border: rgba(21, 190, 83, 0.4);
    --c-success-text: #108c3d;
    --c-clean-bg: #eef0ff;
    --c-clean-text: #2e2b8c;
    --c-cook-bg: #ffe9f6;
    --c-cook-text: #b41a72;
    --shadow-card:
        rgba(50, 50, 93, 0.25) 0px 30px 45px -30px,
        rgba(0, 0, 0, 0.1) 0px 18px 36px -18px;
    --shadow-soft: rgba(23, 23, 23, 0.06) 0px 3px 6px;

    height: 100vh;
    width: 100vw;
    background: var(--c-bg);
    color: var(--c-heading);
    font-family:
        'sohne-var',
        'Inter',
        'SF Pro Display',
        system-ui,
        -apple-system,
        sans-serif;
    font-feature-settings: 'ss01';
    font-weight: 300;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* ---------- NAV ---------- */
.nav {
    flex: 0 0 auto;
    border-bottom: 1px solid var(--c-border);
    background: rgba(255, 255, 255, 0.86);
    backdrop-filter: blur(12px);
}

.nav-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 16px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
}

.brand {
    display: flex;
    align-items: center;
    gap: 10px;
}

.brand-mark {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background: var(--c-purple);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: rgba(50, 50, 93, 0.25) 0px 6px 12px -6px;
}

.brand-icon {
    width: 18px;
    height: 18px;
    color: #fff;
}

.brand-name {
    font-size: 16px;
    font-weight: 400;
    color: var(--c-heading);
    letter-spacing: -0.16px;
}

.nav-links {
    display: flex;
    gap: 28px;
}

.nav-links a {
    font-size: 14px;
    font-weight: 400;
    color: var(--c-heading);
    text-decoration: none;
    font-feature-settings: 'ss01';
    transition: color 0.15s ease;
}
.nav-links a:hover {
    color: var(--c-purple);
}

.nav-cta {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* ---------- BUTTONS ---------- */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 400;
    line-height: 1;
    text-decoration: none;
    border: 1px solid transparent;
    cursor: pointer;
    font-feature-settings: 'ss01';
    transition:
        background-color 0.15s ease,
        color 0.15s ease,
        border-color 0.15s ease,
        box-shadow 0.15s ease;
}

.btn-primary {
    background: var(--c-purple);
    color: #fff;
    box-shadow: rgba(50, 50, 93, 0.25) 0px 6px 14px -6px;
}
.btn-primary:hover {
    background: var(--c-purple-hover);
}

.btn-outlined {
    background: transparent;
    color: var(--c-purple);
    border-color: var(--c-border-purple);
}
.btn-outlined:hover {
    background: var(--c-purple-soft);
}

.btn-ghost {
    background: transparent;
    color: var(--c-heading);
}
.btn-ghost:hover {
    color: var(--c-purple);
}

.btn-lg {
    padding: 12px 22px;
    font-size: 15px;
}

/* ---------- MAIN ---------- */
.main {
    flex: 1 1 auto;
    min-height: 0;
    max-width: 1200px;
    width: 100%;
    margin: 0 auto;
    padding: 0 32px;
    display: grid;
    grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
    align-items: center;
    gap: 56px;
}

/* ---------- HERO ---------- */
.hero {
    display: flex;
    flex-direction: column;
    gap: 22px;
    max-width: 560px;
}

.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    align-self: flex-start;
    padding: 4px 10px;
    border-radius: 4px;
    background: #eef0ff;
    color: var(--c-purple);
    border: 1px solid #d6d9fc;
    font-size: 12px;
    font-weight: 400;
    font-feature-settings: 'ss01';
}
.eyebrow-dot {
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: var(--c-purple);
}

.display {
    font-size: clamp(40px, 4.4vw, 56px);
    font-weight: 300;
    line-height: 1.03;
    letter-spacing: -1.4px;
    color: var(--c-heading);
    margin: 0;
    font-feature-settings: 'ss01';
}

.lede {
    font-size: 18px;
    font-weight: 300;
    line-height: 1.4;
    color: var(--c-body);
    margin: 0;
    max-width: 520px;
    font-feature-settings: 'ss01';
}

.cta-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 4px;
}

.proof {
    display: flex;
    align-items: center;
    gap: 10px;
    list-style: none;
    padding: 0;
    margin: 6px 0 0;
    color: var(--c-body);
    font-size: 13px;
    font-weight: 300;
    font-feature-settings: 'ss01';
}
.proof .dot {
    color: var(--c-border-purple);
}

/* ---------- MOCKUP ---------- */
.mockup-wrap {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 8px;
}

.mockup {
    width: 100%;
    max-width: 460px;
    background: #ffffff;
    border: 1px solid var(--c-border);
    border-radius: 8px;
    box-shadow: var(--shadow-card);
    overflow: hidden;
}

.mock-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--c-border);
    background: #fbfcfe;
}
.mock-dots {
    display: inline-flex;
    gap: 5px;
}
.mock-dots span {
    width: 9px;
    height: 9px;
    border-radius: 999px;
    background: #e5edf5;
}
.mock-title {
    font-size: 12px;
    color: var(--c-label);
    font-weight: 400;
    font-feature-settings: 'ss01';
    letter-spacing: -0.12px;
}

.mock-body {
    padding: 6px 4px;
}

.mock-row {
    display: grid;
    grid-template-columns: 1.4fr 1fr 0.6fr 0.8fr;
    align-items: center;
    padding: 12px 14px;
    border-bottom: 1px solid #f1f4f9;
}
.mock-row:last-child {
    border-bottom: none;
}

.mock-cell {
    font-size: 13px;
    color: var(--c-label);
    font-weight: 400;
    font-feature-settings: 'ss01';
}

.person {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--c-heading);
}

.avatar {
    width: 26px;
    height: 26px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 11px;
    font-weight: 400;
}
.a1 {
    background: #533afd;
}
.a2 {
    background: #ea2261;
}
.a3 {
    background: #2874ad;
}
.a4 {
    background: #1c1e54;
}

.badge {
    display: inline-flex;
    align-items: center;
    padding: 1px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 400;
    font-feature-settings: 'ss01';
}
.badge-clean {
    background: var(--c-clean-bg);
    color: var(--c-clean-text);
    border: 1px solid #d6d9fc;
}
.badge-cook {
    background: var(--c-cook-bg);
    color: var(--c-cook-text);
    border: 1px solid #ffd7ef;
}

.day {
    color: var(--c-body);
    font-feature-settings: 'tnum';
}

.status {
    font-size: 11px;
    font-feature-settings: 'ss01';
    justify-self: end;
    padding: 1px 8px;
    border-radius: 4px;
}
.status.done {
    background: var(--c-success-bg);
    color: var(--c-success-text);
    border: 1px solid var(--c-success-border);
}
.status.pending {
    background: #ffffff;
    color: var(--c-body);
    border: 1px solid var(--c-border);
}

.mock-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    border-top: 1px solid var(--c-border);
    background: #fbfcfe;
    font-size: 11px;
    color: var(--c-body);
    font-feature-settings: 'ss01';
}
.next {
    color: var(--c-purple);
}

.chip {
    position: absolute;
    top: 4px;
    right: 0;
    background: #ffffff;
    border: 1px solid var(--c-border);
    border-radius: 4px;
    padding: 6px 10px;
    font-size: 12px;
    color: var(--c-heading);
    font-weight: 400;
    font-feature-settings: 'ss01';
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: var(--shadow-soft);
}
.chip-dot {
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: #15be53;
    box-shadow: 0 0 0 3px rgba(21, 190, 83, 0.18);
}

/* ---------- STRIP ---------- */
.strip {
    flex: 0 0 auto;
    border-top: 1px solid var(--c-border);
    background: #fbfcfe;
}

.strip {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 32px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 22px 32px;
}

.feat-title {
    font-size: 14px;
    font-weight: 400;
    color: var(--c-heading);
    margin-bottom: 4px;
    font-feature-settings: 'ss01';
    letter-spacing: -0.14px;
}
.feat-desc {
    font-size: 13px;
    color: var(--c-body);
    line-height: 1.4;
    font-weight: 300;
    font-feature-settings: 'ss01';
}

/* ---------- TABLET ---------- */
@media (max-width: 1024px) {
    .main {
        grid-template-columns: 1fr;
        gap: 28px;
        padding: 24px 24px 0;
        align-content: center;
    }
    .mockup-wrap {
        display: none;
    }
    .display {
        font-size: 40px;
        letter-spacing: -0.96px;
        line-height: 1.08;
    }
    .lede {
        font-size: 16px;
    }
    .strip {
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        padding: 18px 24px;
    }
    .feat:nth-child(3) {
        display: none;
    }
}

@media (max-width: 640px) {
    .nav-links {
        display: none;
    }
    .nav-inner {
        padding: 14px 20px;
    }
    .main {
        padding: 16px 20px 0;
    }
    .display {
        font-size: 32px;
        letter-spacing: -0.64px;
    }
    .cta-row {
        flex-direction: column;
        align-items: stretch;
    }
    .btn-lg {
        width: 100%;
    }
    .strip {
        grid-template-columns: 1fr;
        padding: 14px 20px;
    }
    .feat:nth-child(2),
    .feat:nth-child(3) {
        display: none;
    }
}
</style>

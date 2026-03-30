<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { CalendarDays, LayoutGrid, Settings, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import GroupDutyController from '@/actions/App/Http/Controllers/GroupDutyController';
import GroupMemberController from '@/actions/App/Http/Controllers/GroupMemberController';
import { dashboard } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import type { NavItem } from '@/types';

const page = usePage();
const homeGroup = computed(() => page.props.homeGroup as { id: number } | null);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    if (homeGroup.value) {
        items.push(
            {
                title: 'Members',
                href: GroupMemberController.index(homeGroup.value.id),
                icon: Users,
            },
            {
                title: 'Duties',
                href: GroupDutyController.index(homeGroup.value.id),
                icon: CalendarDays,
            },
        );
    }

    items.push({
        title: 'Settings',
        href: editProfile(),
        icon: Settings,
    });

    return items;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

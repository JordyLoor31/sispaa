<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarMenuSub, SidebarMenuSubButton, SidebarMenuSubItem } from '@/components/ui/sidebar';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        items: NavItem[];
        label?: string;
        hideLabel?: boolean;
    }>(),
    {
        label: 'Menú',
        hideLabel: false,
    },
);

const page = usePage<SharedData & Record<string, unknown>>();

const resolvePath = (href?: string) => {
    if (!href) {
        return '#';
    }

    const ziggy = page.props.ziggy;

    if (ziggy?.url) {
        return new URL(href, ziggy.url).pathname;
    }

    if (typeof window === 'undefined') {
        return href;
    }

    return new URL(href, window.location.origin).pathname;
};

const isItemOpen = (item: NavItem) => {
    if (!item.items?.length) {
        return false;
    }

    const currentPath = page.url;
    const parentPath = resolvePath(item.href);
    const hasActiveChild = item.items.some((subItem) => resolvePath(subItem.href) === currentPath);

    return hasActiveChild || parentPath === currentPath;
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel v-if="!hideLabel">{{ label }}</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <Collapsible v-if="item.items && item.items.length > 0" :default-open="isItemOpen(item)" class="group/collapsible">
                    <SidebarMenuItem>
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton>
                                <component :is="item.icon" v-if="item.icon" class="!size-[25px] shrink-0 group-data-[collapsible=icon]:!size-5" />
                                <span class="truncate group-data-[collapsible=icon]:hidden">{{ item.title }}</span>
                                <ChevronRight class="ml-auto shrink-0 transition-transform group-data-[state=open]/collapsible:rotate-90 group-data-[collapsible=icon]:hidden" />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <SidebarMenuSub>
                                <SidebarMenuSubItem v-for="subItem in item.items" :key="subItem.title">
                                    <SidebarMenuSubButton as-child :is-active="resolvePath(subItem.href) === page.url">
                                        <Link :href="subItem.href || '#'">
                                            <span class="truncate">{{ subItem.title }}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>
                <SidebarMenuItem v-else>
                    <SidebarMenuButton as-child :is-active="resolvePath(item.href) === page.url">
                        <Link :href="item.href || '#'" class="flex w-full items-center justify-between group-data-[collapsible=icon]:!justify-center">
                            <div class="flex min-w-0 items-center gap-2 group-data-[collapsible=icon]:justify-center">
                                <component :is="item.icon" v-if="item.icon" class="!size-[25px] shrink-0 group-data-[collapsible=icon]:!size-5" />
                                <span class="truncate group-data-[collapsible=icon]:hidden">{{ item.title }}</span>
                            </div>
                            <span v-if="item.title === 'Notificaciones' && (page.props.auth?.user as any)?.unread_notifications > 0" class="flex h-5 min-w-[20px] shrink-0 px-1 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white leading-none group-data-[collapsible=icon]:hidden">
                                {{ (page.props.auth.user as any).unread_notifications }}
                            </span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>

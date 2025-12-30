import {
    LayoutDashboard,
    Package,
    ShoppingBag,
    Warehouse,
    Users,
    Settings,
} from 'lucide-vue-next';
import type { Component } from 'vue';

export interface NavItem {
    title: string;
    href: string;
    icon: Component;
    badge?: number;
}

export function useAdminNavigation() {
    const navItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/admin/dashboard',
            icon: LayoutDashboard,
        },
        {
            title: 'Products',
            href: '/admin/products',
            icon: Package,
        },
        {
            title: 'Orders',
            href: '/admin/orders',
            icon: ShoppingBag,
        },
        {
            title: 'Stock Management',
            href: '/admin/stock',
            icon: Warehouse,
        },
        {
            title: 'Admin Users',
            href: '/admin/admins',
            icon: Users,
        },
        {
            title: 'Settings',
            href: '/admin/settings',
            icon: Settings,
        },
    ];

    return {
        navItems,
    };
}

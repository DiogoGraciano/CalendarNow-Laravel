import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as calendarsIndex } from '@/routes/calendars';
import { index as scheduleIndex } from '@/routes/schedule';
import { index as employeesIndex } from '@/routes/employees';
import { index as employeeDaysOffIndex } from '@/routes/employee-days-off';
import { index as holidaysIndex } from '@/routes/holidays';
import { index as servicesIndex } from '@/routes/services';
import { index as accountsIndex } from '@/routes/accounts';
import { index as dresIndex } from '@/routes/dres/index';
import { index as reportsIndex } from '@/routes/reports';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { Calendar, LayoutGrid, CalendarDays, Users, Wrench, DollarSign, FileText, BarChart3, CalendarOff, PartyPopper } from 'lucide-react';
import { useTranslation } from '@/hooks/use-translation';
import AppLogo from './app-logo';

export function AppSidebar() {
    const { t } = useTranslation();
    
    const mainNavItems: NavItem[] = [
        {
            title: t('common.dashboard'),
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: t('calendar.title'),
            href: calendarsIndex(),
            icon: Calendar,
        },
        {
            title: t('schedule.title'),
            href: scheduleIndex(),
            icon: CalendarDays,
        },
        {
            title: t('employee.title'),
            href: employeesIndex(),
            icon: Users,
        },
        {
            title: t('dayOff.title'),
            href: employeeDaysOffIndex(),
            icon: CalendarOff,
        },
        {
            title: t('holiday.title'),
            href: holidaysIndex(),
            icon: PartyPopper,
        },
        {
            title: t('service.title'),
            href: servicesIndex(),
            icon: Wrench,
        },
        {
            title: t('account.title'),
            href: accountsIndex(),
            icon: DollarSign,
        },
        {
            title: t('dre.title'),
            href: dresIndex(),
            icon: FileText,
        },
        {
            title: t('report.title'),
            href: reportsIndex(),
            icon: BarChart3,
        },
    ];

    const footerNavItems: NavItem[] = [];
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}

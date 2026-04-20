import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import multiMonthPlugin from '@fullcalendar/multimonth';
import allLocales from '@fullcalendar/core/locales-all';
import { useRef, useEffect } from 'react';
import type { EventInput, DateSelectArg, EventClickArg } from '@fullcalendar/core';
import { useTranslation } from '@/hooks/use-translation';

/** Mapeia o idioma do i18n para o locale do FullCalendar (pt -> pt-br). */
function fullCalendarLocale(i18nLang: string): string {
    if (i18nLang === 'pt') return 'pt-br';
    if (i18nLang === 'es') return 'es';
    if (i18nLang === 'en') return 'en';
    return 'pt-br';
}

interface FullCalendarComponentProps {
    events: EventInput[];
    initialDate?: Date | string;
    slotMinTime?: string;
    slotMaxTime?: string;
    hiddenDays?: number[];
    onDateClick?: (arg: DateSelectArg) => void;
    onEventClick?: (arg: EventClickArg) => void;
    eventsUrl?: string;
    /** Incrementar para forçar refetch dos eventos (ex.: após criar/editar agendamento) */
    eventsRefreshTrigger?: number;
}

export function FullCalendarComponent({
    events,
    initialDate,
    slotMinTime = '08:00:00',
    slotMaxTime = '18:00:00',
    hiddenDays = [],
    onDateClick,
    onEventClick,
    eventsUrl,
    eventsRefreshTrigger,
}: FullCalendarComponentProps) {
    const calendarRef = useRef<FullCalendar>(null);
    const { currentLanguage } = useTranslation();
    const locale = fullCalendarLocale(currentLanguage);

    useEffect(() => {
        // Recarregar eventos quando a URL mudar
        if (calendarRef.current && eventsUrl) {
            const calendarApi = calendarRef.current.getApi();
            if (calendarApi && typeof calendarApi.refetchEvents === 'function') {
                calendarApi.refetchEvents();
            }
        }
    }, [eventsUrl]);

    useEffect(() => {
        // Refetch quando o trigger mudar (ex.: após salvar agendamento)
        if (calendarRef.current && eventsUrl && eventsRefreshTrigger != null && eventsRefreshTrigger > 0) {
            const calendarApi = calendarRef.current.getApi();
            if (calendarApi && typeof calendarApi.refetchEvents === 'function') {
                calendarApi.refetchEvents();
            }
        }
    }, [eventsUrl, eventsRefreshTrigger]);

    // Atualizar locale do calendário quando o idioma da aplicação mudar
    useEffect(() => {
        if (calendarRef.current) {
            const calendarApi = calendarRef.current.getApi();
            if (calendarApi && typeof calendarApi.setOption === 'function') {
                calendarApi.setOption('locale', locale);
            }
        }
    }, [locale]);

    // Cleanup ao desmontar o componente
    useEffect(() => {
        return () => {
            if (calendarRef.current) {
                const calendarApi = calendarRef.current.getApi();
                if (calendarApi && typeof calendarApi.destroy === 'function') {
                    try {
                        calendarApi.destroy();
                    } catch (error) {
                        // Ignorar erros durante o cleanup
                    }
                }
            }
        };
    }, []);

    return (
        <div className="w-full">
            <FullCalendar
                ref={calendarRef}
                plugins={[
                    dayGridPlugin,
                    timeGridPlugin,
                    interactionPlugin,
                    multiMonthPlugin,
                ]}
                locales={allLocales}
                locale={locale}
                height="auto"
                expandRows={true}
                timeZone="local"
                initialDate={initialDate}
                slotMinTime={slotMinTime}
                slotMaxTime={slotMaxTime}
                headerToolbar={{
                    left: 'prevYear,prev,next,nextYear today',
                    center: 'multiMonthYear,dayGridMonth,timeGridWeek,timeGridDay',
                    right: 'title',
                }}
                initialView="timeGridDay"
                longPressDelay={500}
                eventLongPressDelay={500}
                selectLongPressDelay={500}
                hiddenDays={Array.isArray(hiddenDays) && hiddenDays.length > 0 ? hiddenDays : []}
                selectable={true}
                allDaySlot={true}
                dayMaxEvents={true}
                selectOverlap={false}
                eventOverlap={false}
                events={eventsUrl || events}
                select={onDateClick}
                eventClick={onEventClick}
            />
        </div>
    );
}











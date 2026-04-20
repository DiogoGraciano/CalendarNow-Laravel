import Alpine from 'alpinejs';
import 'htmx.org';

interface EmployeeData {
    id: number;
    name: string;
    photo_url: string | null;
    service_ids: number[];
    public_calendar_id: number | null;
}

interface ServiceData {
    id: number;
    name: string;
    price: number;
    duration_minutes: number;
    image_url: string | null;
}

Alpine.data('bookingWizard', (employees: EmployeeData[], services: ServiceData[]) => ({
    currentStep: 1,
    totalSteps: 5,
    selectedEmployeeId: null as number | null,
    selectedServiceIds: [] as number[],
    selectedSlotStart: '',
    selectedSlotEnd: '',
    selectedSlotEmployeeId: null as number | null,
    customerName: '',
    customerEmail: '',
    customerPhone: '',
    employees: employees || [],
    services: services || [],
    slotsLoaded: false,

    get filteredServices(): ServiceData[] {
        if (!this.selectedEmployeeId) return [];
        const emp = this.employees.find((e: EmployeeData) => e.id === this.selectedEmployeeId);
        if (!emp || !emp.service_ids) return [];
        return this.services.filter((s: ServiceData) => emp.service_ids.includes(s.id));
    },

    get totalPrice(): number {
        return this.services
            .filter((s: ServiceData) => this.selectedServiceIds.includes(s.id))
            .reduce((sum: number, s: ServiceData) => sum + Number(s.price), 0);
    },

    get totalDuration(): number {
        return this.services
            .filter((s: ServiceData) => this.selectedServiceIds.includes(s.id))
            .reduce((sum: number, s: ServiceData) => sum + Number(s.duration_minutes), 0);
    },

    get selectedEmployeeName(): string {
        const emp = this.employees.find((e: EmployeeData) => e.id === this.selectedEmployeeId);
        return emp ? emp.name : '';
    },

    get selectedEmployeePhoto(): string | null {
        const emp = this.employees.find((e: EmployeeData) => e.id === this.selectedEmployeeId);
        return emp ? emp.photo_url : null;
    },

    get selectedServiceNames(): string[] {
        return this.services
            .filter((s: ServiceData) => this.selectedServiceIds.includes(s.id))
            .map((s: ServiceData) => s.name);
    },

    get calendarId(): number | null {
        const emp = this.employees.find((e: EmployeeData) => e.id === this.selectedEmployeeId);
        return emp ? emp.public_calendar_id : null;
    },

    get formattedSlotDate(): string {
        if (!this.selectedSlotStart) return '';
        const d = new Date(this.selectedSlotStart);
        return d.toLocaleDateString('pt-BR');
    },

    get formattedSlotTime(): string {
        if (!this.selectedSlotStart || !this.selectedSlotEnd) return '';
        const start = new Date(this.selectedSlotStart);
        const end = new Date(this.selectedSlotEnd);
        const pad = (n: number) => String(n).padStart(2, '0');
        return `${pad(start.getHours())}:${pad(start.getMinutes())} - ${pad(end.getHours())}:${pad(end.getMinutes())}`;
    },

    selectEmployee(id: number): void {
        this.selectedEmployeeId = id;
        const emp = this.employees.find((e: EmployeeData) => e.id === id);
        if (emp) {
            this.selectedServiceIds = this.selectedServiceIds.filter(
                (sid: number) => emp.service_ids.includes(sid)
            );
        }
        this.selectedSlotStart = '';
        this.selectedSlotEnd = '';
        this.selectedSlotEmployeeId = null;
        this.slotsLoaded = false;
    },

    toggleService(id: number): void {
        const idx = this.selectedServiceIds.indexOf(id);
        if (idx === -1) {
            this.selectedServiceIds.push(id);
        } else {
            this.selectedServiceIds.splice(idx, 1);
        }
        this.selectedSlotStart = '';
        this.selectedSlotEnd = '';
        this.slotsLoaded = false;
    },

    isServiceSelected(id: number): boolean {
        return this.selectedServiceIds.includes(id);
    },

    selectSlot(start: string, end: string, employeeId: number): void {
        this.selectedSlotStart = start;
        this.selectedSlotEnd = end;
        this.selectedSlotEmployeeId = employeeId;
    },

    canProceed(): boolean {
        switch (this.currentStep) {
            case 1:
                return this.selectedEmployeeId !== null;
            case 2:
                return this.selectedServiceIds.length > 0;
            case 3:
                return this.selectedSlotStart !== '' && this.selectedSlotEnd !== '';
            case 4:
                return this.customerName.trim() !== '';
            case 5:
                return true;
            default:
                return false;
        }
    },

    nextStep(): void {
        if (!this.canProceed()) return;
        if (this.currentStep < this.totalSteps) {
            this.currentStep++;
            if (this.currentStep === 3) {
                this.triggerSlotLoad();
            }
        }
    },

    prevStep(): void {
        if (this.currentStep > 1) {
            this.currentStep--;
        }
    },

    triggerSlotLoad(): void {
        this.$nextTick(() => {
            const trigger = document.getElementById('slots-trigger') as HTMLElement | null;
            if (trigger) {
                trigger.dispatchEvent(new CustomEvent('loadSlots'));
            }
        });
    },

    handleSlotClick(event: Event): void {
        const btn = (event.target as HTMLElement).closest('.slot-btn') as HTMLElement | null;
        if (!btn) return;
        event.preventDefault();
        const start = btn.getAttribute('data-start') || '';
        const end = btn.getAttribute('data-end') || '';
        const empId = parseInt(btn.getAttribute('data-employee-id') || '0', 10);
        this.selectSlot(start, end, empId);

        document.querySelectorAll('.slot-btn').forEach((b) => {
            b.classList.remove('slot-selected');
            b.setAttribute('aria-pressed', 'false');
        });
        btn.classList.add('slot-selected');
        btn.setAttribute('aria-pressed', 'true');
    },

    formatPrice(price: number): string {
        return Number(price).toFixed(2).replace('.', ',');
    },
}));

window.Alpine = Alpine;
Alpine.start();

import Alpine from 'alpinejs';
import 'htmx.org';

Alpine.data('marketplaceSearch', () => ({
    search: '',
    segmentId: '' as string,
    country: '',
    city: '',

    selectSegment(id: string): void {
        this.segmentId = this.segmentId === id ? '' : id;
        this.triggerSearch();
    },

    selectCountry(value: string): void {
        this.country = value;
        this.city = '';
        this.reloadCities();
        this.triggerSearch();
    },

    selectCity(value: string): void {
        this.city = value;
        this.triggerSearch();
    },

    reloadCities(): void {
        this.$nextTick(() => {
            const trigger = document.getElementById('cities-trigger') as HTMLElement | null;
            if (trigger) {
                trigger.dispatchEvent(new CustomEvent('countryChanged'));
            }
        });
    },

    triggerSearch(): void {
        this.$nextTick(() => {
            const trigger = document.getElementById('search-trigger') as HTMLElement | null;
            if (trigger) {
                trigger.dispatchEvent(new CustomEvent('filterChanged'));
            }
        });
    },
}));

window.Alpine = Alpine;
Alpine.start();

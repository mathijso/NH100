// De beoordeling per dag gebeurt in PHP (RoutePlannerService); dit bestand toont alleen.

const MONTHS = ['januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december'];
const MONTHS_SHORT = ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'];
const WEEKDAYS = ['zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag'];
const WEEKDAYS_SHORT = ['zo', 'ma', 'di', 'wo', 'do', 'vr', 'za'];

const STATUS_LABEL = {
    green: 'Berijdbaar',
    amber: 'Misschien berijdbaar',
    red: 'Niet geschikt',
};

// '2026-10-24' als lokale datum, zonder tijdzone-verschuiving
export function parseDate(iso) {
    const [y, m, d] = iso.split('-').map(Number);
    return new Date(y, m - 1, d);
}

export function ucfirst(text) {
    return text ? text.charAt(0).toUpperCase() + text.slice(1) : '';
}

export function formatLong(iso) {
    const d = parseDate(iso);
    return ucfirst(`${WEEKDAYS[d.getDay()]} ${d.getDate()} ${MONTHS[d.getMonth()]}`);
}

export function formatShort(iso) {
    const d = parseDate(iso);
    return `${WEEKDAYS_SHORT[d.getDay()]} ${d.getDate()} ${MONTHS_SHORT[d.getMonth()]}`;
}

export function planner({ days, today, weather }) {
    const byDate = Object.fromEntries(days.map(day => [day.date, day]));
    const todayDate = parseDate(today);

    return {
        days,
        today,
        weather,
        byDate,
        month: { year: todayDate.getFullYear(), month: todayDate.getMonth() },
        weekendOnly: readSetting('weekendOnly') === '1',
        listSize: 14,
        selected: null,
        shareLabel: 'Deel planner',

        init() {
            const day = this.todayDay;
            if (day) {
                document.title = `NH100 Route Planner - ${{
                    green: 'Route berijdbaar vandaag',
                    amber: 'Route mogelijk berijdbaar',
                    red: 'Route niet geschikt vandaag',
                }[day.status]}`;
            }
        },

        get todayDay() {
            return byDate[today] ?? null;
        },

        get upcoming() {
            return days.filter(day => day.date >= today);
        },

        // De eerstvolgende zaterdag en zondag (vandaag meegerekend)
        get weekend() {
            return this.upcoming.filter(day => day.weekend).slice(0, 2);
        },

        // Eerste groene weekenddag ná de twee getoonde
        get nextGreenWeekendDay() {
            const shown = this.weekend.map(day => day.date);
            return this.upcoming.find(day => day.weekend && day.status === 'green' && !shown.includes(day.date)) ?? null;
        },

        get nextGreenDay() {
            return this.upcoming.find(day => day.status === 'green') ?? null;
        },

        get monthLabel() {
            return `${MONTHS[this.month.month]} ${this.month.year}`;
        },

        // Lege vakjes vóór de 1e, maandag = 0
        get monthOffset() {
            const first = new Date(this.month.year, this.month.month, 1).getDay();
            return first === 0 ? 6 : first - 1;
        },

        get monthDays() {
            const count = new Date(this.month.year, this.month.month + 1, 0).getDate();
            const prefix = `${this.month.year}-${String(this.month.month + 1).padStart(2, '0')}`;

            return Array.from({ length: count }, (_, i) => {
                const date = `${prefix}-${String(i + 1).padStart(2, '0')}`;
                return byDate[date] ?? { date, missing: true, weekend: [0, 6].includes(parseDate(date).getDay()) };
            });
        },

        get canGoBack() {
            return days.length > 0 && this.monthKey(-1) >= days[0].date.slice(0, 7);
        },

        get canGoForward() {
            return days.length > 0 && this.monthKey(1) <= days[days.length - 1].date.slice(0, 7);
        },

        get listDays() {
            const list = this.weekendOnly ? this.upcoming.filter(day => day.weekend) : this.upcoming;
            return list.slice(0, this.listSize);
        },

        get hasMore() {
            const list = this.weekendOnly ? this.upcoming.filter(day => day.weekend) : this.upcoming;
            return list.length > this.listSize;
        },

        monthKey(delta) {
            const d = new Date(this.month.year, this.month.month + delta, 1);
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
        },

        moveMonth(delta) {
            if ((delta < 0 && !this.canGoBack) || (delta > 0 && !this.canGoForward)) return;
            const d = new Date(this.month.year, this.month.month + delta, 1);
            this.month = { year: d.getFullYear(), month: d.getMonth() };
        },

        toggleWeekendOnly() {
            this.weekendOnly = !this.weekendOnly;
            this.listSize = 14;
            writeSetting('weekendOnly', this.weekendOnly ? '1' : '0');
        },

        open(day) {
            if (!day || day.missing) return;
            this.selected = day;
            this.$nextTick(() => this.$refs.closeDetail?.focus());
        },

        close() {
            this.selected = null;
        },

        async share() {
            const data = {
                title: 'NH100 Route Planner',
                text: 'Wanneer is de NH100 berijdbaar? Getij, seizoen en wind per dag.',
                url: window.location.href.split('#')[0],
            };

            try {
                if (navigator.share) {
                    await navigator.share(data);
                    return;
                }
                await navigator.clipboard.writeText(data.url);
                this.shareLabel = 'Link gekopieerd';
            } catch (e) {
                if (e?.name === 'AbortError') return;
                this.shareLabel = 'Kopiëren lukte niet';
            }
            setTimeout(() => (this.shareLabel = 'Deel planner'), 2500);
        },

        isToday(day) {
            return day.date === today;
        },

        isPast(day) {
            return day.date < today;
        },

        statusLabel(day) {
            return STATUS_LABEL[day?.status] ?? '';
        },

        lowTides(day) {
            return day.tides.filter(tide => tide.type === 'Eb');
        },

        windText(wind) {
            if (!wind) return '';
            return `${wind.direction} ${wind.beaufort} Bft${wind.effect ? `, ${wind.effect}` : ''}`;
        },

        formatLong,
        formatShort,
        ucfirst,
        dayNumber: iso => parseDate(iso).getDate(),
    };
}

function readSetting(key) {
    try {
        return localStorage.getItem(`nh100.${key}`);
    } catch {
        return null;
    }
}

function writeSetting(key, value) {
    try {
        localStorage.setItem(`nh100.${key}`, value);
    } catch {
        // privévenster of geblokkeerde opslag: dan onthouden we het niet
    }
}

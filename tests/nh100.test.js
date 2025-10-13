// Simple Node test harness for nh100.js logic
import { isRouteRideable } from '../resources/js/nh100.js';

function parse(timeStr) {
    // timeStr like '2025-10-16 08:24'
    const [datePart, timePart] = timeStr.split(' ');
    const [y, m, d] = datePart.split('-').map(Number);
    const [hh, mm] = timePart.split(':').map(Number);
    const dt = new Date(y, m - 1, d, hh, mm, 0, 0);
    return dt.toISOString();
}

function tide(timeIso, type) {
    return { time: timeIso, type, height: 0 };
}

function assert(condition, message) {
    if (!condition) {
        throw new Error('Assertion failed: ' + message);
    }
}

// Build tides list for a specific day
function buildDayTides(dateStr, pairs) {
    // pairs: [ ['Eb','08:24'], ['Vloed','12:50'], ... ]
    return pairs.map(([nlType, hhmm]) => {
        const iso = parse(`${dateStr} ${hhmm}`);
        const type = nlType === 'Eb' ? 'Low' : 'High';
        return tide(iso, type);
    });
}

// Merge with neighboring days (empty here, we only test given day)
function buildTidesForTest(list) {
    return list;
}

function run() {
    // Test thresholds
    // 1) Green - full 120 min overlap
    {
        const date = new Date(2025, 9, 13); // 13 Oct 2025
        const tides = buildTidesForTest(
            buildDayTides('2025-10-13', [['Eb', '10:30']])
        );
        const res = isRouteRideable(date, tides);
        assert(res.status === 'green', 'Expected green for full overlap');
    }

    // 2) Amber - partial overlap (~>20 and <120)
    // Example from user: 2025-10-16 tides
    // Vloed: 00:17, Eb: 08:24, Vloed: 12:50, Eb: 20:16
    // 10:00-12:00 intersects Eb window (06:24-10:24) by ~24 min => amber
    {
        const date = new Date(2025, 9, 16); // 16 Oct 2025
        const tides = buildTidesForTest(
            buildDayTides('2025-10-16', [
                ['Vloed', '00:17'],
                ['Eb', '08:24'],
                ['Vloed', '12:50'],
                ['Eb', '20:16'],
            ])
        );
        const res = isRouteRideable(date, tides);
        assert(res.status === 'amber', 'Expected amber for partial overlap on 2025-10-16');
    }

    // 3) Red - negligible overlap (<20 min)
    {
        const date = new Date(2025, 9, 14); // 14 Oct 2025
        const tides = buildTidesForTest(
            buildDayTides('2025-10-14', [['Eb', '07:35']]) // eb 07:35 → window 10-12: 2h window around eb is 05:35-09:35, no overlap
        );
        const res = isRouteRideable(date, tides);
        assert(res.status === 'red', 'Expected red for no/low overlap');
    }

    console.log('✅ nh100 logic tests passed');
}

run();



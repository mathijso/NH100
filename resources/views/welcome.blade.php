<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NH100 Route Planner</title>
    <meta name="description" content="Wanneer is de NH100 offroad route door het Noordhollands Duinreservaat berijdbaar? Getij, seizoen en wind per dag, met de weekenden voorop.">
    <meta name="theme-color" content="#d97706">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="canonical" href="{{ url('/') }}/">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="nl_NL">
    <meta property="og:title" content="NH100 Route Planner">
    <meta property="og:description" content="Wanneer is de NH100 berijdbaar? Getij, seizoen en wind per dag, met de weekenden voorop.">
    <meta property="og:url" content="{{ url('/') }}/">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">

    <script>
        window.NH100_DATA = @json(['days' => $days, 'today' => $today, 'weather' => $currentWeather]);
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.bezoek')
</head>

<body class="bg-neutral-50 min-h-screen" x-data="planner(window.NH100_DATA)" @keydown.escape.window="close()">
    <div class="container mx-auto px-4 py-6 md:py-10 max-w-7xl">
        <!-- Header -->
        <div class="bg-white border border-stone-200 rounded-xl shadow-sm p-5 md:p-8 mb-6 animate-fadeIn">
            <div class="border-l-4 border-amber-600 pl-4 mb-5">
                <h1 class="text-3xl md:text-5xl font-bold text-slate-900 mb-2 tracking-tight">NH100 Route Planner</h1>
                <p class="text-slate-600 text-sm md:text-base">Noordhollands Duinreservaat</p>
            </div>
            <p class="text-slate-700 leading-relaxed text-base">
                De NH100 is het favoriete offroad trainingsrondje door het Noordhollands Duinreservaat,
                over het strand en door de bossen van Schoorl en Bergen. Deze planner helpt je bepalen
                wanneer de route berijdbaar is op basis van getij, seizoen en wind. De route is bedacht door <a
                    href="http://nh100.nl/" target="_blank" rel="noopener" class="text-amber-700 hover:text-amber-800 font-medium underline decoration-2 underline-offset-2">Laurens ten Dam en Niki Terpstra</a>.
            </p>

            <!-- Vandaag -->
            <template x-if="todayDay">
                <button type="button" @click="open(todayDay)"
                    class="mt-6 w-full text-left border-l-4 rounded-lg p-4 md:p-5 hover:shadow-md transition-shadow"
                    :class="'day-' + todayDay.status">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div class="flex items-start gap-4">
                            @include('partials.status-icon', ['status' => 'todayDay.status'])
                            <div>
                                <h2 class="text-lg md:text-xl font-bold"
                                    x-text="todayDay.status === 'green' ? 'Route berijdbaar vandaag' : (todayDay.status === 'amber' ? 'Route mogelijk berijdbaar vandaag' : 'Route niet geschikt vandaag')"></h2>
                                <p class="text-sm md:text-base mt-1" :class="'text-status-' + todayDay.status" x-text="todayDay.reason"></p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs md:text-sm font-medium">
                            <template x-for="tide in todayDay.tides" :key="tide.time">
                                <span class="px-3 py-1.5 rounded-full" :class="'chip-' + todayDay.status" x-text="`${tide.type}: ${tide.time}`"></span>
                            </template>
                        </div>
                    </div>
                </button>
            </template>
            <template x-if="!todayDay">
                <div class="mt-6 bg-stone-100 border-l-4 border-stone-400 rounded-lg p-4 text-stone-700">
                    Geen getijdendata beschikbaar voor vandaag.
                </div>
            </template>

            <!-- Komend weekend -->
            <section class="mt-6" aria-labelledby="weekend-title">
                <h2 id="weekend-title" class="text-xl md:text-2xl font-bold text-slate-900 mb-3" x-text="weekendTitle">Komend weekend</h2>
                <div class="grid sm:grid-cols-2 gap-3">
                    <template x-for="day in weekend" :key="day.date">
                        <button type="button" @click="open(day)"
                            class="text-left border-2 rounded-xl p-4 hover:shadow-md transition-shadow"
                            :class="'day-' + day.status">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-bold text-lg" x-text="formatLong(day.date)"></div>
                                    <div class="text-sm font-semibold" :class="'text-status-' + day.status" x-text="statusLabel(day)"></div>
                                </div>
                                @include('partials.status-icon', ['status' => 'day.status'])
                            </div>
                            <p class="text-sm mt-2" :class="'text-status-' + day.status" x-text="day.reason"></p>
                            <div class="mt-3 flex flex-wrap gap-2 text-xs font-medium">
                                <template x-for="tide in lowTides(day)" :key="tide.time">
                                    <span class="px-2.5 py-1 rounded-full" :class="'chip-' + day.status" x-text="`Eb ${tide.time} (${tide.cm} cm)`"></span>
                                </template>
                                <span x-show="day.extra_low" class="px-2.5 py-1 rounded-full bg-sky-100 text-sky-800">Extra laag water</span>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600">
                                <span x-text="`Zon op ${day.sunrise}`"></span>
                                <span x-show="day.wind" x-text="'Wind 11:00: ' + windText(day.wind)"></span>
                                <span x-show="!day.wind" class="text-slate-400">Windverwachting volgt later</span>
                            </div>
                        </button>
                    </template>
                </div>

                <div class="mt-3 flex flex-col gap-1 text-sm text-slate-700">
                    <template x-if="nextGreenWeekendDay">
                        <p>
                            Volgende groene weekenddag:
                            <button type="button" class="font-semibold text-emerald-800 underline decoration-2 underline-offset-2"
                                @click="open(nextGreenWeekendDay)" x-text="formatLong(nextGreenWeekendDay.date)"></button>
                            <span class="text-slate-500" x-text="'· ' + nextGreenWeekendDay.reason"></span>
                        </p>
                    </template>
                    <template x-if="!nextGreenWeekendDay && weekend.length">
                        <p class="text-slate-500">Geen groene weekenddag meer na dit weekend binnen de beschikbare getijdendata.</p>
                    </template>
                    <template x-if="nextGreenDay && !weekend.some(day => day.date === nextGreenDay.date)">
                        <p>
                            Eerstvolgende groene dag:
                            <button type="button" class="font-semibold text-emerald-800 underline decoration-2 underline-offset-2"
                                @click="open(nextGreenDay)" x-text="formatLong(nextGreenDay.date)"></button>
                        </p>
                    </template>
                </div>
            </section>

            <!-- Route, weer en links -->
            <div class="mt-8 grid md:grid-cols-3 gap-6">
                <div class="bg-stone-50 border border-stone-200 rounded-xl p-5 flex flex-col gap-5 md:col-span-2">
                    <a href="https://www.komoot.com/nl-nl/tour/296286553" target="_blank" rel="noopener" class="flex-1 flex items-center justify-center group">
                        <img src="{{ asset('images/nh100.jpg') }}" alt="Kaart van de NH100 route" width="560" height="927" loading="lazy"
                            class="rounded-lg shadow-md max-h-80 w-auto object-contain group-hover:shadow-xl transition-shadow duration-300" />
                    </a>

                    <!-- Weer nu -->
                    <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl p-5 shadow-sm border border-slate-200">
                        <template x-if="!weather">
                            <div class="text-center text-slate-500 text-sm py-8">Weergegevens niet beschikbaar</div>
                        </template>
                        <template x-if="weather">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-800">Weer nu</h3>
                                        <p class="text-sm text-slate-600 mt-0.5">Egmond aan Zee</p>
                                    </div>
                                    <div class="text-3xl font-bold text-slate-900" x-show="weather.temperature !== null" x-text="`${weather.temperature}°C`"></div>
                                </div>

                                <div class="flex flex-col md:flex-row items-center gap-4">
                                    <div class="relative flex-shrink-0">
                                        <svg width="120" height="120" viewBox="0 0 120 120" role="img" :aria-label="`Wind uit ${weather.direction}`">
                                            <defs>
                                                <radialGradient id="bgGradient" cx="50%" cy="50%" r="50%">
                                                    <stop offset="0%" style="stop-color:#f8fafc;stop-opacity:1" />
                                                    <stop offset="100%" style="stop-color:#e2e8f0;stop-opacity:1" />
                                                </radialGradient>
                                            </defs>
                                            <circle cx="60" cy="60" r="52" fill="url(#bgGradient)" stroke="#475569" stroke-width="2" />
                                            <g>
                                                <text x="60" y="18" text-anchor="middle" font-size="12" font-weight="bold" fill="#1e293b">N</text>
                                                <text x="105" y="63" text-anchor="middle" font-size="11" fill="#475569">O</text>
                                                <text x="60" y="107" text-anchor="middle" font-size="11" fill="#475569">Z</text>
                                                <text x="15" y="63" text-anchor="middle" font-size="11" fill="#475569">W</text>
                                            </g>
                                            <line x1="60" y1="20" x2="60" y2="100" stroke="#94a3b8" stroke-width="1" opacity="0.4" />
                                            <line x1="20" y1="60" x2="100" y2="60" stroke="#94a3b8" stroke-width="1" opacity="0.4" />
                                            <!-- Pijl wijst naar waar de wind vandaan komt -->
                                            <g :style="`transform: rotate(${weather.degrees}deg); transform-origin: 60px 60px;`" class="transition-transform duration-1000">
                                                <line x1="60" y1="60" x2="60" y2="25" stroke="#ea580c" stroke-width="3.5" stroke-linecap="round" />
                                                <polygon points="60,19 54,30 66,30" fill="#ea580c" />
                                                <line x1="54" y1="86" x2="60" y2="75" stroke="#ea580c" stroke-width="2.5" />
                                                <line x1="66" y1="86" x2="60" y2="75" stroke="#ea580c" stroke-width="2.5" />
                                            </g>
                                            <circle cx="60" cy="60" r="5" fill="#334155" stroke="white" stroke-width="2" />
                                        </svg>
                                    </div>

                                    <div class="flex-1 w-full grid grid-cols-2 lg:grid-cols-3 gap-2.5">
                                        <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm">
                                            <div class="text-xs text-slate-600 mb-1.5 font-medium">Richting</div>
                                            <div class="text-sm font-bold text-slate-900" x-text="weather.direction"></div>
                                        </div>
                                        <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm">
                                            <div class="text-xs text-slate-600 mb-1.5 font-medium">Kracht</div>
                                            <div class="text-sm font-bold text-slate-900" x-text="`${weather.beaufort} Bft`"></div>
                                        </div>
                                        <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm">
                                            <div class="text-xs text-slate-600 mb-1.5 font-medium">Snelheid</div>
                                            <div class="text-sm font-bold text-slate-900" x-text="`${weather.kmh} km/h`"></div>
                                        </div>
                                        <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm" x-show="weather.gust_kmh">
                                            <div class="text-xs text-slate-600 mb-1.5 font-medium">Windstoten</div>
                                            <div class="text-sm font-bold text-amber-700" x-text="`${weather.gust_kmh} km/h`"></div>
                                        </div>
                                        <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm" x-show="weather.effect">
                                            <div class="text-xs text-slate-600 mb-1.5 font-medium">Op het strand</div>
                                            <div class="text-sm font-bold text-slate-900" x-text="ucfirst(weather.effect)"></div>
                                        </div>
                                        <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-lg p-3 shadow-sm" x-show="todayDay">
                                            <div class="text-xs text-amber-800 font-semibold mb-1.5">Zonsopgang</div>
                                            <div class="text-sm font-bold text-amber-900" x-text="todayDay?.sunrise"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex flex-col gap-3">
                        <a href="https://www.komoot.com/nl-nl/tour/296286553" target="_blank" rel="noopener"
                            class="block bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl p-4 shadow-sm transition-all hover:shadow-md border border-emerald-700">
                            <div class="flex items-center gap-3">
                                <div class="bg-white rounded-lg p-2.5 flex-shrink-0">
                                    <img src="{{ asset('images/komoot.jpg') }}" alt="" class="w-8 h-8">
                                </div>
                                <div>
                                    <h3 class="font-bold text-base">Bekijk officiële route</h3>
                                    <p class="text-xs text-emerald-100 mt-0.5">Op Komoot</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ asset('gpx/2025-09-15_296286553_NH100-2026(1509).gpx') }}"
                            download="NH100.gpx"
                            class="flex items-center gap-3 px-4 py-3 bg-slate-700 hover:bg-slate-800 text-white rounded-xl shadow-sm transition-all hover:shadow-md border border-slate-800">
                            <div class="bg-white rounded-lg p-2.5 flex-shrink-0">
                                <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2" stroke-linecap="round" stroke-linejoin="round" />
                                    <polyline points="7 11 12 16 17 11" stroke-linecap="round" stroke-linejoin="round" />
                                    <line x1="12" y1="4" x2="12" y2="16" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div>
                                <span class="font-bold text-base">Download GPX</span>
                                <p class="text-xs text-slate-300 mt-0.5">Voor je GPS-apparaat</p>
                            </div>
                        </a>

                        <a href="https://www.pwn.nl/duinkaartkopen#" target="_blank" rel="noopener"
                            class="block bg-sky-600 hover:bg-sky-700 text-white rounded-xl p-4 shadow-sm transition-all hover:shadow-md border border-sky-700">
                            <div class="flex items-center gap-3">
                                <div class="bg-white rounded-lg p-2.5 flex-shrink-0">
                                    <img src="{{ asset('images/pwn.svg') }}" alt="" class="w-8 h-8">
                                </div>
                                <div>
                                    <h3 class="font-bold text-base">Koop Duinkaart</h3>
                                    <p class="text-xs text-sky-100 mt-0.5">Verplicht - €2,00 per dag</p>
                                </div>
                            </div>
                        </a>

                        <!-- Agenda-abonnement -->
                        <div class="bg-white border border-stone-300 rounded-xl p-4 shadow-sm">
                            <a href="{{ preg_replace('#^https?://#', 'webcal://', url('agenda.ics')) }}"
                                class="flex items-center gap-3 group">
                                <div class="bg-amber-100 rounded-lg p-2.5 flex-shrink-0 text-amber-800">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="3" y="5" width="18" height="16" rx="2" />
                                        <path d="M3 10h18M8 3v4M16 3v4" stroke-linecap="round" />
                                    </svg>
                                </div>
                                <div>
                                    <span class="font-bold text-base text-slate-900 group-hover:underline">Groene weekenden in je agenda</span>
                                    <p class="text-xs text-slate-600 mt-0.5">Abonnement, ververst vanzelf</p>
                                </div>
                            </a>
                            <p class="text-xs text-slate-500 mt-2">
                                Ook doordeweeks rijden? <a class="underline" href="{{ preg_replace('#^https?://#', 'webcal://', url('agenda.ics')) }}?dagen=alle">Alle groene dagen</a>
                                of <a class="underline" href="{{ url('agenda.ics') }}">download het bestand</a>.
                            </p>
                        </div>

                        <button type="button" @click="share()"
                            class="flex items-center justify-center gap-2 px-4 py-3 bg-white hover:bg-stone-100 border border-stone-300 rounded-xl text-slate-800 font-semibold shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 12v7a2 2 0 002 2h12a2 2 0 002-2v-7M16 6l-4-4-4 4M12 2v14" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span x-text="shareLabel">Deel planner</span>
                        </button>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                        <h3 class="font-bold text-amber-900 mb-3">Belangrijke restricties</h3>
                        <ul class="text-sm text-amber-900 space-y-2 list-disc pl-5 marker:text-amber-600">
                            <li>Je moet voor <strong>10:30</strong> uit het duinreservaat zijn</li>
                            <li>1 okt - 1 mei: strand hele dag toegankelijk</li>
                            <li>Strandgedeelte alleen bij laagwater goed te fietsen</li>
                            <li>Duinkaart verplicht (<a href="https://www.pwn.nl/duinkaartkopen#" target="_blank" rel="noopener" class="text-amber-800 underline font-medium decoration-2">€2,00 per dag</a>)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kalender -->
        <div class="bg-white border border-stone-200 rounded-xl shadow-sm p-5 md:p-8 animate-fadeIn">
            <!-- Mobiel: doorlopende lijst -->
            <div class="md:hidden">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="text-2xl font-bold text-slate-900">Komende dagen</h2>
                    <button type="button" @click="toggleWeekendOnly()" role="switch" :aria-checked="weekendOnly.toString()"
                        class="group flex items-center gap-2 text-sm font-medium text-slate-700 whitespace-nowrap">
                        <span class="relative inline-flex h-6 w-11 shrink-0 rounded-full bg-slate-300 group-aria-checked:bg-amber-600">
                            <span class="absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform group-aria-checked:translate-x-5"></span>
                        </span>
                        Alleen weekend
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="day in listDays" :key="day.date">
                        <button type="button" @click="open(day)"
                            class="w-full text-left border-l-4 rounded-lg p-4 shadow-sm"
                            :class="['day-' + day.status, isToday(day) ? 'ring-2 ring-slate-400' : '', day.weekend ? 'border-y border-r' : '']">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    @include('partials.status-icon', ['status' => 'day.status'])
                                    <div>
                                        <div class="font-bold text-base" x-text="formatLong(day.date)"></div>
                                        <div class="text-sm" :class="'text-status-' + day.status" x-text="day.reason"></div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <span x-show="isToday(day)" class="bg-slate-800 text-white px-2 py-0.5 rounded-md text-xs font-bold">VANDAAG</span>
                                    <span x-show="day.weekend && !isToday(day)" class="bg-amber-600 text-white px-2 py-0.5 rounded-md text-xs font-bold">WEEKEND</span>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2 text-xs font-medium">
                                <template x-for="tide in day.tides" :key="tide.time">
                                    <span class="px-2.5 py-1 rounded-full" :class="['chip-' + day.status, tide.type === 'Eb' ? 'font-bold' : 'opacity-75']" x-text="`${tide.type}: ${tide.time}`"></span>
                                </template>
                                <span x-show="day.extra_low" class="px-2.5 py-1 rounded-full bg-sky-100 text-sky-800">Extra laag</span>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-x-4 text-xs text-slate-600">
                                <span x-text="`Zon op ${day.sunrise}`"></span>
                                <span x-show="day.wind" x-text="windText(day.wind)"></span>
                            </div>
                        </button>
                    </template>
                </div>
                <button type="button" x-show="hasMore" @click="listSize += 14"
                    class="mt-4 w-full py-3 rounded-lg border border-stone-300 bg-stone-50 hover:bg-stone-100 font-semibold text-slate-700">
                    Meer dagen tonen
                </button>
            </div>

            <!-- Desktop: maandkalender -->
            <div class="hidden md:block">
                <div class="flex items-center justify-between gap-4 mb-4 pb-4 border-b border-stone-200">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="moveMonth(-1)" :disabled="!canGoBack" aria-label="Vorige maand"
                            class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-300 disabled:opacity-40 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <h2 class="text-3xl font-bold text-slate-900 min-w-56" x-text="monthLabel"></h2>
                        <button type="button" @click="moveMonth(1)" :disabled="!canGoForward" aria-label="Volgende maand"
                            class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-300 disabled:opacity-40 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="text-sm text-slate-500">Klik op een dag voor details</p>
                </div>

                <div class="grid grid-cols-7 gap-2 mb-2">
                    @foreach (['Ma', 'Di', 'Wo', 'Do', 'Vr'] as $weekday)
                        <div class="font-bold text-center p-2 bg-slate-100 border border-slate-200 text-slate-700 rounded-lg text-sm">{{ $weekday }}</div>
                    @endforeach
                    @foreach (['Za', 'Zo'] as $weekday)
                        <div class="font-bold text-center p-2 bg-amber-100 border border-amber-300 text-amber-900 rounded-lg text-sm">{{ $weekday }}</div>
                    @endforeach
                </div>

                <div class="grid grid-cols-7 gap-2">
                    <template x-for="i in monthOffset" :key="'empty-' + i">
                        <div class="rounded-lg min-h-36"></div>
                    </template>

                    <template x-for="day in monthDays" :key="day.date">
                        <button type="button" @click="open(day)" :disabled="day.missing"
                            class="text-left border-2 rounded-lg p-2 min-h-36 flex flex-col hover:shadow-md transition-shadow disabled:cursor-default disabled:hover:shadow-none"
                            :class="[day.missing ? 'day-missing' : 'day-' + day.status, isToday(day) ? 'ring-2 ring-slate-600 ring-offset-1' : '', day.weekend ? 'border-4' : '', isPast(day) ? 'opacity-60' : '']">
                            <div class="flex justify-between items-start mb-1.5 w-full">
                                <span class="font-bold text-base" x-text="dayNumber(day.date)"></span>
                                <template x-if="!day.missing">
                                    @include('partials.status-icon', ['status' => 'day.status', 'size' => 'w-6 h-6', 'iconSize' => 'w-3.5 h-3.5'])
                                </template>
                            </div>
                            <template x-if="day.missing">
                                <span class="text-[11px]">Geen data</span>
                            </template>
                            <template x-if="!day.missing">
                                <div class="flex flex-col gap-1 text-[11px] leading-tight w-full">
                                    <span x-show="isToday(day)" class="bg-slate-800 text-white px-1.5 py-0.5 rounded text-center font-bold text-[10px]">VANDAAG</span>
                                    <span x-show="day.status !== 'green'" class="px-1.5 py-0.5 rounded text-center font-medium truncate" :class="'chip-' + day.status" x-text="day.reason"></span>
                                    <template x-for="tide in day.tides" :key="tide.time">
                                        <span :class="tide.type === 'Eb' ? 'font-bold' : 'opacity-70'" x-text="`${tide.type}: ${tide.time}`"></span>
                                    </template>
                                    <span x-show="day.extra_low" class="mt-0.5 px-1.5 py-0.5 rounded bg-sky-100 text-sky-800 font-medium text-center">Extra laag</span>
                                    <span x-show="day.wind" class="text-slate-600" x-text="windText(day.wind)"></span>
                                </div>
                            </template>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Legenda en regels -->
            <div class="mt-6 pt-4 border-t border-stone-200 flex flex-wrap gap-x-5 gap-y-2 text-sm text-slate-700">
                <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full dot-green"></span>Berijdbaar</span>
                <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full dot-amber"></span>Misschien</span>
                <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full dot-red"></span>Niet geschikt</span>
                <span class="flex items-center gap-2"><span class="px-1.5 rounded bg-sky-100 text-sky-800 text-xs font-medium">Extra laag</span>laagwater op {{ \App\Services\RoutePlannerService::EXTRA_LOW_CM }} cm NAP of lager: breed, hard strand</span>
                <span class="hidden md:flex items-center gap-2"><span class="w-4 h-4 rounded border-4 border-slate-400"></span>Weekend</span>
            </div>

            <details class="mt-4 group">
                <summary class="cursor-pointer font-semibold text-slate-800 hover:text-amber-800">Hoe beoordeelt de planner een dag?</summary>
                <div class="mt-3 text-sm text-slate-700 space-y-2 max-w-3xl">
                    <p>Je start rond 07:00, verlaat het duinreservaat voor 10:30 en rijdt daarna het strand van Wijk aan Zee naar Bergen aan Zee, tot ongeveer 12:00.</p>
                    <ol class="list-decimal pl-5 space-y-1">
                        <li>Van 1 mei tot 1 oktober mag het strand alleen voor 10:00 op. Zomerdagen zijn daarom altijd rood.</li>
                        <li><strong>Groen</strong> als het laagwater tussen 08:00 en 15:00 valt.</li>
                        <li><strong>Rood</strong> als het laatste hoogwater minder dan 2 uur voor 10:30 was.</li>
                        <li>Anders telt de overlap van 10:30-12:00 met de periode van 4 uur voor tot 2 uur na laagwater: 90 minuten is <strong>groen</strong>, 30 tot 90 minuten <strong>oranje</strong>, minder is <strong>rood</strong>.</li>
                    </ol>
                    <p>Getijden zijn de astronomische voorspelling van Rijkswaterstaat voor IJmuiden buitenhaven. Harde wind of storm kan het water hoger opstuwen dan voorspeld. De wind is de verwachting van Open-Meteo voor 11:00; wind uit het zuiden is rugwind op het strand, uit het noorden tegenwind.</p>
                </div>
            </details>
        </div>

        <footer class="mt-12 pt-8 border-t border-stone-200">
            <div class="text-center text-slate-600 text-sm">
                <p>© {{ now()->year }} NH100 Route Planner</p>
                <p class="mt-1">Ontwikkeld door <a href="https://oggel-codelabs.nl" target="_blank" rel="noopener"
                        class="text-amber-700 hover:text-amber-800 font-medium underline decoration-2">Oggel Codelabs</a></p>
            </div>
        </footer>
    </div>

    <!-- Dagdetail -->
    <div x-show="selected" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-end md:items-center justify-center bg-slate-900/50 p-0 md:p-6"
        @click.self="close()">
        <template x-if="selected">
            <div role="dialog" aria-modal="true" aria-labelledby="detail-title"
                class="bg-white w-full md:max-w-lg rounded-t-2xl md:rounded-2xl shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="p-5 border-l-8 rounded-t-2xl" :class="'day-' + selected.status">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            @include('partials.status-icon', ['status' => 'selected.status'])
                            <div>
                                <h2 id="detail-title" class="text-xl font-bold" x-text="formatLong(selected.date)"></h2>
                                <p class="font-semibold" :class="'text-status-' + selected.status" x-text="statusLabel(selected)"></p>
                                <p class="text-sm mt-1" :class="'text-status-' + selected.status" x-text="selected.reason"></p>
                            </div>
                        </div>
                        <button type="button" x-ref="closeDetail" @click="close()" aria-label="Sluiten"
                            class="p-2 -m-2 rounded-lg hover:bg-white/60 text-slate-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-5 space-y-5">
                    <div>
                        <h3 class="font-bold text-slate-900 mb-2">Getijden <span class="font-normal text-slate-500 text-sm">(IJmuiden, t.o.v. NAP)</span></h3>
                        <ul class="divide-y divide-stone-200 border border-stone-200 rounded-lg">
                            <template x-for="tide in selected.tides" :key="tide.time">
                                <li class="flex justify-between px-3 py-2 text-sm" :class="tide.type === 'Eb' ? 'font-semibold' : 'text-slate-600'">
                                    <span x-text="`${tide.type === 'Eb' ? 'Laagwater' : 'Hoogwater'} ${tide.time}`"></span>
                                    <span x-text="`${tide.cm} cm`"></span>
                                </li>
                            </template>
                            <li x-show="!selected.tides.length" class="px-3 py-2 text-sm text-slate-500">Geen getijdendata</li>
                        </ul>
                        <p x-show="selected.extra_low" class="mt-2 text-sm text-sky-800 bg-sky-50 rounded-lg px-3 py-2">Extra laag water: het strand is breed en meestal hard.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                            <div class="text-xs text-amber-800 font-semibold mb-1">Zonsopgang</div>
                            <div class="font-bold text-amber-900" x-text="selected.sunrise"></div>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3">
                            <div class="text-xs text-slate-600 font-semibold mb-1">Wind om 11:00</div>
                            <template x-if="selected.wind">
                                <div>
                                    <div class="font-bold text-slate-900" x-text="`${selected.wind.direction} ${selected.wind.beaufort} Bft (${selected.wind.kmh} km/h)`"></div>
                                    <div class="text-slate-600" x-show="selected.wind.gust_kmh" x-text="`Stoten ${selected.wind.gust_kmh} km/h`"></div>
                                    <div class="font-semibold" x-show="selected.wind.effect"
                                        :class="selected.wind.effect === 'rugwind' ? 'text-emerald-700' : (selected.wind.effect === 'tegenwind' ? 'text-rose-700' : 'text-slate-700')"
                                        x-text="ucfirst(selected.wind.effect) + ' op het strand'"></div>
                                </div>
                            </template>
                            <div x-show="!selected.wind" class="text-slate-500">Nog geen verwachting (max. 16 dagen vooruit)</div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</body>

</html>

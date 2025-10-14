<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title x-data="todayStatus"
        x-text="loading ? 'NH100 Route Planner' : (result ? (result.rideable ? 'NH100 Route Planner - Route berijdbaar vandaag' : 'NH100 Route Planner - Route niet geschikt vandaag') : 'NH100 Route Planner')"
        x-init="init()">NH100 Route Planner</title>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/nh100.js'])
    @livewireStyles
</head>

<body class="bg-neutral-50 min-h-screen">
    @livewire('tide-data')
    @livewire('wind-data')

    <div class="container mx-auto px-4 py-6 md:py-10 max-w-7xl">
        <!-- Header -->
        <div class="bg-white border border-stone-200 rounded-xl shadow-sm p-6 md:p-8 mb-6 animate-fadeIn">
            <div class="border-l-4 border-amber-600 pl-4 mb-6">
                <h1 class="text-3xl md:text-5xl font-bold text-slate-900 mb-2 tracking-tight">
                    NH100 Route Planner
                </h1>
                <p class="text-slate-600 text-sm md:text-base">
                    Noordhollands Duinreservaat
                </p>
            </div>
            <p class="text-slate-700 leading-relaxed text-base">
                De NH100 is het favoriete offroad trainingsrondje door het Noordhollands Duinreservaat,
                over het strand en door de bossen van Schoorl en Bergen. Deze planner helpt je bepalen
                wanneer de route berijdbaar is op basis van getijden en seizoensrestricties. De route is bedacht door <a
                    href="http://nh100.nl/" target="_blank" class="text-amber-700 hover:text-amber-800 font-medium underline decoration-2 underline-offset-2"> Laurens ten Dam en Nikki Terpstra</a>.
            </p>

            <!-- Vandaag Status -->
            <div x-data="todayStatus" class="mt-6">
                <template x-if="loading">
                    <div class="flex items-center justify-center py-4">
                        <div class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-slate-500 text-sm">Status wordt geladen...</span>
                        </div>
                    </div>
                </template>

                <template x-if="!loading && result">
                    <div
                        :class="{
                            'bg-emerald-50 border-l-4 border-emerald-600 rounded-lg p-4 md:p-5': result.status === 'green',
                            'bg-amber-50 border-l-4 border-amber-500 rounded-lg p-4 md:p-5': result.status === 'amber',
                            'bg-rose-50 border-l-4 border-rose-600 rounded-lg p-4 md:p-5': !result.status || result.status === 'red'
                        }">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div class="flex items-start gap-4">
                                <div :class="{
                                    'bg-emerald-600': result.status === 'green',
                                    'bg-amber-500': result.status === 'amber',
                                    'bg-rose-600': !result.status || result.status === 'red'
                                }" class="flex-shrink-0 w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="result.status === 'green'">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <svg class="w-5 h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="result.status === 'amber'">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <svg class="w-5 h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!result.status || result.status === 'red'">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 :class="{
                                        'text-lg md:text-xl font-bold text-emerald-900': result.status === 'green',
                                        'text-lg md:text-xl font-bold text-amber-900': result.status === 'amber',
                                        'text-lg md:text-xl font-bold text-rose-900': !result.status || result.status === 'red'
                                    }"
                                        x-text="result.status === 'green' ? 'Route berijdbaar vandaag' : (result.status === 'amber' ? 'Route mogelijk berijdbaar' : 'Route niet geschikt vandaag')">
                                    </h3>
                                    <p :class="{
                                        'text-sm md:text-base text-emerald-800 mt-1': result.status === 'green',
                                        'text-sm md:text-base text-amber-800 mt-1': result.status === 'amber',
                                        'text-sm md:text-base text-rose-800 mt-1': !result.status || result.status === 'red'
                                    }"
                                        x-text="result.reason"></p>
                                </div>
                            </div>

                            <!-- Getijden Info Compact -->
                            <template x-if="result.allTides && result.allTides.length > 0">
                                <div class="flex flex-wrap gap-2 text-xs md:text-sm font-medium">
                                    <template x-for="(tide, i) in result.allTides" :key="tide.time">
                                        <div :class="{
                                                'bg-emerald-100 text-emerald-800 px-3 py-1.5 rounded-full': result.status === 'green',
                                                'bg-amber-100 text-amber-800 px-3 py-1.5 rounded-full': result.status === 'amber',
                                                'bg-rose-100 text-rose-800 px-3 py-1.5 rounded-full': !result.status || result.status === 'red'
                                            }">
                                            <span x-text="`${tide.tideType}: ${formatTime(tide.time)}`"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Route Info met Afbeelding en Links -->
            <div class="mt-8 grid md:grid-cols-3 gap-6">
                <!-- Route Afbeelding met Windroos -->
                <div class="bg-stone-50 border border-stone-200 rounded-xl p-5 flex flex-col gap-5 col-span-2">
                    <!-- Route Afbeelding -->
                    <a href="https://www.komoot.com/nl-nl/tour/296286553" target="_blank" class="flex-1 flex items-center justify-center group">
                        <img src="{{ asset('images/nh100.png') }}" alt="NH100 Route"
                            class="rounded-lg shadow-md max-h-80 object-contain group-hover:shadow-xl transition-shadow duration-300"
                            onerror="this.style.display='none'" />
                    </a>
                    
                    <!-- Weer Card -->
                    <div x-data="windRose" class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl p-5 shadow-sm border border-slate-200">
                        <template x-if="loading">
                            <div class="flex items-center justify-center py-8">
                                <div class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-slate-500 text-sm">Weergegevens laden...</span>
                                </div>
                            </div>
                        </template>

                        <template x-if="!loading && error">
                            <div class="text-center text-slate-500 text-sm py-8" x-text="error"></div>
                        </template>

                        <template x-if="!loading && !error && windData">
                            <div class="space-y-4">
                                <!-- Header met titel en temperatuur -->
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-xl font-bold text-slate-800">Weer onderweg</h4>
                                        <p class="text-sm text-slate-600 mt-0.5">Huidige omstandigheden</p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-3xl font-bold text-slate-900" x-text="`${windData.temperature}°C`"></div>
                                    </div>
                                </div>

                                <!-- Compacte layout: Windroos + Info cards -->
                                <div class="flex flex-col md:flex-row items-center gap-4">
                                    <!-- Windroos SVG -->
                                    <div class="relative flex-shrink-0">
                                        <svg width="120" height="120" viewBox="0 0 120 120" class="transform">
                                            <!-- Achtergrond cirkel met gradient -->
                                            <defs>
                                                <radialGradient id="bgGradient" cx="50%" cy="50%" r="50%">
                                                    <stop offset="0%" style="stop-color:#f8fafc;stop-opacity:1" />
                                                    <stop offset="100%" style="stop-color:#e2e8f0;stop-opacity:1" />
                                                </radialGradient>
                                            </defs>
                                            <circle cx="60" cy="60" r="52" fill="url(#bgGradient)" stroke="#475569" stroke-width="2"/>
                                            
                                            <!-- Windrichtingen -->
                                            <g>
                                                <text x="60" y="18" text-anchor="middle" font-size="12" font-weight="bold" fill="#1e293b">N</text>
                                                <text x="105" y="63" text-anchor="middle" font-size="11" fill="#475569">O</text>
                                                <text x="60" y="107" text-anchor="middle" font-size="11" fill="#475569">Z</text>
                                                <text x="15" y="63" text-anchor="middle" font-size="11" fill="#475569">W</text>
                                            </g>
                                            
                                            <!-- Hulplijnen -->
                                            <line x1="60" y1="20" x2="60" y2="100" stroke="#94a3b8" stroke-width="1" opacity="0.4"/>
                                            <line x1="20" y1="60" x2="100" y2="60" stroke="#94a3b8" stroke-width="1" opacity="0.4"/>
                                            <line x1="30" y1="30" x2="90" y2="90" stroke="#94a3b8" stroke-width="1" opacity="0.3"/>
                                            <line x1="90" y1="30" x2="30" y2="90" stroke="#94a3b8" stroke-width="1" opacity="0.3"/>
                                            
                                            <!-- Windpijl (rotated based on wind direction) -->
                                            <g :style="`transform: rotate(${windData.direction_degrees}deg); transform-origin: 60px 60px;`" class="transition-transform duration-1000">
                                                <!-- Pijlschaduw -->
                                                <line x1="60" y1="60" x2="60" y2="26" stroke="#0f172a" stroke-width="3.5" stroke-linecap="round" opacity="0.15"/>
                                                <!-- Pijlschacht -->
                                                <line x1="60" y1="60" x2="60" y2="25" stroke="#ea580c" stroke-width="3.5" stroke-linecap="round"/>
                                                <!-- Pijlpunt -->
                                                <polygon points="60,19 54,30 66,30" fill="#ea580c"/>
                                                <!-- Pijlstaart -->
                                                <line x1="54" y1="86" x2="60" y2="75" stroke="#ea580c" stroke-width="2.5"/>
                                                <line x1="66" y1="86" x2="60" y2="75" stroke="#ea580c" stroke-width="2.5"/>
                                            </g>
                                            
                                            <!-- Centrum punt -->
                                            <circle cx="60" cy="60" r="5" fill="#334155" stroke="white" stroke-width="2"/>
                                        </svg>
                                    </div>
                                    
                                    <!-- Info cards -->
                                    <div class="flex-1 w-full">
                                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-2.5">
                                            <!-- Temperatuur card (alleen op kleine schermen zichtbaar als duplicate, anders verborgen) -->
                                            <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm md:hidden">
                                                <div class="text-xs text-slate-600 mb-1.5 font-medium">Temperatuur</div>
                                                <div class="text-lg font-bold text-slate-900">
                                                    <span x-text="`${windData.temperature}°C`"></span>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm">
                                                <div class="text-xs text-slate-600 mb-1.5 font-medium">Richting</div>
                                                <div class="text-sm font-bold text-slate-900">
                                                    <span x-text="windData.direction_text"></span>
                                                </div>
                                            </div>
                                            <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm">
                                                <div class="text-xs text-slate-600 mb-1.5 font-medium">Kracht</div>
                                                <div class="text-sm font-bold text-slate-900">
                                                    <span x-text="`${windData.beaufort} Bft`"></span>
                                                </div>
                                            </div>
                                            <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm">
                                                <div class="text-xs text-slate-600 mb-1.5 font-medium">Snelheid</div>
                                                <div class="text-sm font-bold text-slate-900">
                                                    <span x-text="`${windData.speed_kmh} km/h`"></span>
                                                </div>
                                            </div>
                                            <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm" x-show="windData.gust_kmh">
                                                <div class="text-xs text-slate-600 mb-1.5 font-medium">Windstoten</div>
                                                <div class="text-sm font-bold text-amber-700">
                                                    <span x-text="`${windData.gust_kmh} km/h`"></span>
                                                </div>
                                            </div>
                                            <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-lg p-3 shadow-sm col-span-2 lg:col-span-1" x-show="windData.sunrise">
                                                <div class="text-xs text-amber-800 font-semibold mb-1.5">Zonsopgang</div>
                                                <div class="text-sm font-bold text-amber-900" x-text="formatSunrise(windData.sunrise)"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>


                <!-- Route Links en Info -->
                <div class="space-y-4">
                    <div class="flex flex-col gap-3">
                        <!-- Komoot Link -->
                        <a href="https://www.komoot.com/nl-nl/tour/296286553" target="_blank"
                            class="block bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl p-4 shadow-sm transition-all hover:shadow-md border border-emerald-700">
                            <div class="flex items-center gap-3">
                                <div class="bg-white rounded-lg p-2.5 flex-shrink-0">
                                    <img src="{{ asset('images/komoot.jpg') }}" alt="Komoot" class="w-8 h-8">
                                </div>
                                <div>
                                    <h3 class="font-bold text-base">Bekijk officiële route</h3>
                                    <p class="text-xs text-emerald-100 mt-0.5">Op Komoot</p>
                                </div>
                            </div>
                        </a>
                        
                        <!-- GPX Download -->
                        <a href="{{ asset('gpx/2025-09-15_296286553_NH100-2026(1509).gpx') }}"
                            download="2025-09-15_296286553_NH100-2026(1509).gpx"
                            class="flex items-center gap-3 px-4 py-3 bg-slate-700 hover:bg-slate-800 text-white rounded-xl shadow-sm transition-all hover:shadow-md border border-slate-800 group">
                            <div class="bg-white rounded-lg p-2.5 flex-shrink-0">
                                <svg class="w-6 h-6 text-slate-700"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <polyline points="7 11 12 16 17 11" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <line x1="12" y1="4" x2="12" y2="16"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div>
                                <span class="font-bold text-base">Download GPX</span>
                                <p class="text-xs text-slate-300 mt-0.5">Voor je GPS-apparaat</p>
                            </div>
                        </a>
                        
                        <!-- PWN Duinkaart Link -->
                        <a href="https://www.pwn.nl/duinkaartkopen#" target="_blank"
                            class="block bg-sky-600 hover:bg-sky-700 text-white rounded-xl p-4 shadow-sm transition-all hover:shadow-md border border-sky-700">
                            <div class="flex items-center gap-3">
                                <div class="bg-white rounded-lg p-2.5 flex-shrink-0">
                                    <img src="{{ asset('images/pwn.svg') }}" alt="PWN Duinkaart" class="w-8 h-8">
                                </div>
                                <div>
                                    <h3 class="font-bold text-base">Koop Duinkaart</h3>
                                    <p class="text-xs text-sky-100 mt-0.5">Verplicht - €2,00 per dag</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Belangrijke info -->
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                        <h3 class="font-bold text-amber-900 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Belangrijke restricties
                        </h3>
                        <ul class="text-sm text-amber-900 space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="text-amber-600 mt-0.5">•</span>
                                <span>Je moet voor <strong>10:30</strong> uit het duinreservaat zijn</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-amber-600 mt-0.5">•</span>
                                <span>Tussen 1 okt - 1 mei: strand hele dag toegankelijk</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-amber-600 mt-0.5">•</span>
                                <span>Strandgedeelte alleen bij laagwater goed te fietsen</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-amber-600 mt-0.5">•</span>
                                <span>Duinkaart verplicht (<a href="https://www.pwn.nl/duinkaartkopen#" target="_blank"
                                    class="text-amber-800 underline font-medium decoration-2">€2,00 per dag</a>)</span>
                            </li>
                        </ul>
                    </div>
                </div>




            </div>
        </div>

        <!-- Kalender View -->
        <div x-data="calendar" class="bg-white border border-stone-200 rounded-xl shadow-sm p-6 md:p-8 animate-fadeIn">
            <!-- Kalender Header met Navigatie -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 pb-4 border-b border-stone-200">
                <div class="flex items-center gap-3">
                    <button @click="previousMonth()"
                        class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 transition-colors border border-slate-300">
                        <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-900" x-text="currentMonthYear"></h2>
                    <button @click="nextMonth()"
                        class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 transition-colors border border-slate-300">
                        <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </button>
                </div>
                <div class="text-sm text-slate-600 font-medium">
                    <span x-text="`${days.length} dagen`"></span>
                </div>
            </div>

            <template x-if="loading">
                <div class="flex items-center justify-center py-12">
                    <div class="flex items-center gap-2">
                        <svg class="animate-spin h-6 w-6 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-slate-500">Kalender laden...</span>
                    </div>
                </div>
            </template>

            <template x-if="!loading">
                <div>
                    <!-- Mobiele Lijstweergave -->
                    <div class="md:hidden space-y-3">
                        <template x-for="(day, index) in mobileDays" :key="index">
                            <div
                                :class="{
                                    'bg-emerald-50 border-l-4 border-emerald-600 rounded-lg p-4 shadow-sm': day.result.status === 'green',
                                    'bg-amber-50 border-l-4 border-amber-500 rounded-lg p-4 shadow-sm': day.result.status === 'amber',
                                    'bg-rose-50 border-l-4 border-rose-600 rounded-lg p-4 shadow-sm': !day.result.status || day.result.status === 'red',
                                    'ring-2 ring-slate-400': day.isToday
                                }">
                                <!-- Header: Datum + Status -->
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div :class="{
                                            'bg-emerald-600': day.result.status === 'green',
                                            'bg-amber-500': day.result.status === 'amber',
                                            'bg-rose-600': !day.result.status || day.result.status === 'red'
                                        }" class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="day.result.status === 'green'">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="day.result.status === 'amber'">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!day.result.status || day.result.status === 'red'">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-bold text-base"
                                                :class="{
                                                    'text-emerald-900': day.result.status === 'green',
                                                    'text-amber-900': day.result.status === 'amber',
                                                    'text-rose-900': !day.result.status || day.result.status === 'red'
                                                }"
                                                x-text="formatDateFull(day.date)"></div>
                                            <div class="text-xs text-slate-600 mt-0.5" x-text="getWeekday(day.date)"></div>
                                        </div>
                                    </div>
                                    <template x-if="day.isToday">
                                        <div class="bg-slate-800 text-white px-2 py-1 rounded-md text-xs font-bold">VANDAAG</div>
                                    </template>
                                </div>

                                <!-- Status bericht -->
                                <template x-if="day.result.reason">
                                    <div class="mb-3 text-sm font-medium"
                                        :class="{
                                            'text-emerald-800': day.result.status === 'green',
                                            'text-amber-800': day.result.status === 'amber',
                                            'text-rose-800': !day.result.status || day.result.status === 'red'
                                        }"
                                        x-text="day.result.reason"></div>
                                </template>

                                <!-- Getijden informatie -->
                                <template x-if="day.result.allTides && day.result.allTides.length > 0">
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="tide in day.result.allTides" :key="tide.time">
                                            <div :class="{
                                                    'bg-emerald-100 text-emerald-800 px-3 py-1.5 rounded-full text-xs font-medium': day.result.status === 'green',
                                                    'bg-amber-100 text-amber-800 px-3 py-1.5 rounded-full text-xs font-medium': day.result.status === 'amber',
                                                    'bg-rose-100 text-rose-800 px-3 py-1.5 rounded-full text-xs font-medium': !day.result.status || day.result.status === 'red'
                                                }">
                                                <span x-text="`${tide.tideType}: ${formatTime(tide.time)}`"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Desktop Kalender Grid -->
                    <div class="hidden md:block">
                        <!-- Weekdag headers -->
                        <div class="grid grid-cols-7 gap-2 mb-3">
                            <template x-for="day in ['Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za', 'Zo']" :key="day">
                                <div class="font-bold text-center p-2 bg-slate-100 border border-slate-200 text-slate-700 rounded-lg text-sm"
                                    x-text="day"></div>
                            </template>
                        </div>

                        <!-- Kalender grid -->
                        <div class="grid grid-cols-7 gap-2">
                            <!-- Empty cells for days before eerste dag van de maand -->
                            <template x-for="i in firstDayOfWeek" :key="`empty-${i}`">
                                <div class="bg-stone-50 border border-stone-200 rounded-lg p-2 min-h-32"></div>
                            </template>

                            <!-- Calendar days -->
                            <template x-for="(day, index) in days" :key="index">
                                <div
                                    :class="{
                                        'bg-emerald-50 border-emerald-500 border-2 rounded-lg p-2 min-h-32 hover:shadow-md transition-shadow cursor-pointer': day.result.status === 'green',
                                        'bg-amber-50 border-amber-400 border-2 rounded-lg p-2 min-h-32 hover:shadow-md transition-shadow cursor-pointer': day.result.status === 'amber',
                                        'bg-rose-50 border-rose-500 border-2 rounded-lg p-2 min-h-32 hover:shadow-md transition-shadow cursor-pointer': !day.result.status || day.result.status === 'red',
                                        'ring-2 ring-slate-500 ring-offset-1': day.isToday
                                    }">
                                    <div class="flex justify-between items-start mb-2">
                                        <div :class="{
                                            'font-bold text-emerald-900 text-base': day.result.status === 'green',
                                            'font-bold text-amber-900 text-base': day.result.status === 'amber',
                                            'font-bold text-rose-900 text-base': !day.result.status || day.result.status === 'red'
                                        }"
                                            x-text="day.date.getDate()"></div>
                                        <div :class="{
                                            'bg-emerald-600': day.result.status === 'green',
                                            'bg-amber-500': day.result.status === 'amber',
                                            'bg-rose-600': !day.result.status || day.result.status === 'red'
                                        }" class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center">
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="day.result.status === 'green'">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="day.result.status === 'amber'">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01"></path>
                                            </svg>
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!day.result.status || day.result.status === 'red'">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <template x-if="day.isToday">
                                        <div class="text-[10px] bg-slate-800 text-white px-1.5 py-0.5 rounded mb-1.5 text-center font-bold">
                                            VANDAAG
                                        </div>
                                    </template>
                                    <!-- Toon reden bij rode dagen -->
                                    <template
                                        x-if="day.result && (day.result.status === 'red' || !day.result.status) && day.result.reason">
                                        <div class="text-[10px] bg-rose-100 text-rose-900 px-1.5 py-0.5 rounded mb-1.5 text-center truncate font-medium"
                                            x-text="day.result.reason"></div>
                                    </template>
                                    <div
                                        :class="{
                                            'text-[11px] text-emerald-700 space-y-0.5 font-medium': day.result.status === 'green',
                                            'text-[11px] text-amber-700 space-y-0.5 font-medium': day.result.status === 'amber',
                                            'text-[11px] text-rose-700 space-y-0.5 font-medium': !day.result.status || day.result.status === 'red'
                                        }">
                                        <!-- Alle getijden chronologisch -->
                                        <template x-if="day.result.allTides && day.result.allTides.length > 0">
                                            <template x-for="tide in day.result.allTides" :key="tide.time">
                                                <div :class="tide.tideType === 'Eb' ? 'font-bold' : ''"
                                                    x-text="`${tide.tideType}: ${formatTime(tide.time)}`"></div>
                                            </template>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <footer class="mt-12 pt-8 border-t border-stone-200">
            <div class="text-center text-slate-600 text-sm">
                <p>© 2025 NH100 Route Planner</p>
                <p class="mt-1">Ontwikkeld door <a href="https://oggel-codelabs.nl" target="_blank"
                        class="text-amber-700 hover:text-amber-800 font-medium underline decoration-2">Oggel Codelabs</a></p>
            </div>
        </footer>
    </div>

    @livewireScripts

    <script>
        // Alpine.js components
        document.addEventListener('alpine:init', () => {
            // Wind Rose Component
            Alpine.data('windRose', () => ({
                loading: true,
                windData: null,
                error: null,

                async init() {
                    console.log('🌤️ Wind Rose component initializing...');
                    
                    // Listen for wind updates
                    window.addEventListener('wind-updated', (event) => {
                        console.log('🌤️ Received wind-updated event:', event.detail.windData);
                        this.windData = event.detail.windData;
                        this.loading = false;
                        this.error = null;
                    });

                    // Load data
                    await this.loadWindData();
                },

                async loadWindData() {
                    // First check if data is immediately available
                    if (window.windData && typeof window.windData === 'object' && window.windData.speed_kmh) {
                        console.log('🌤️ Weather data immediately available:', window.windData);
                        this.windData = window.windData;
                        this.loading = false;
                        this.error = null;
                        return;
                    }

                    // Wait for data with shorter intervals
                    console.log('🌤️ Waiting for weather data...');
                    const maxAttempts = 50;
                    let attempts = 0;

                    while ((!window.windData || typeof window.windData !== 'object' || !window.windData.speed_kmh) && attempts < maxAttempts) {
                        await new Promise(resolve => setTimeout(resolve, 50));
                        attempts++;

                        if (attempts % 10 === 0) {
                            console.log(`🌤️ Still waiting for weather data... (attempt ${attempts}/${maxAttempts})`);
                        }

                        // Check again
                        if (window.windData && typeof window.windData === 'object' && window.windData.speed_kmh) {
                            console.log('🌤️ Weather data loaded after waiting:', window.windData);
                            this.windData = window.windData;
                            this.loading = false;
                            this.error = null;
                            return;
                        }
                    }

                    // If we get here, data is not available
                    console.error('🌤️ Weather data not available after waiting');
                    this.error = 'Weergegevens niet beschikbaar';
                    this.loading = false;
                },

                formatSunrise(sunriseStr) {
                    if (!sunriseStr) return '';
                    
                    try {
                        // Parse ISO8601 date string (e.g., "2025-10-14T08:06")
                        const date = new Date(sunriseStr);
                        const hours = date.getHours().toString().padStart(2, '0');
                        const minutes = date.getMinutes().toString().padStart(2, '0');
                        return `${hours}:${minutes}`;
                    } catch (e) {
                        console.error('Error parsing sunrise time:', e);
                        return sunriseStr;
                    }
                }
            }));

            // Today Status Component
            Alpine.data('todayStatus', () => ({
                loading: true,
                result: null,

                async init() {
                    // Wait for tides data from Livewire
                    await this.loadTides();

                    // Listen for tide updates
                    window.addEventListener('tides-updated', (event) => {
                        this.calculateToday(event.detail.tides);
                    });
                },

                async loadTides() {
                    // Check if data is already available
                    if (window.tidesData && window.tidesData.length > 0) {
                        console.log('Tides already available:', window.tidesData.length);
                        this.calculateToday(window.tidesData);
                        return;
                    }

                    // Wait a bit for Livewire to load
                    const maxAttempts = 30;
                    let attempts = 0;

                    while ((!window.tidesData || window.tidesData.length === 0) && attempts <
                        maxAttempts) {
                        await new Promise(resolve => setTimeout(resolve, 100));
                        attempts++;

                        if (attempts % 10 === 0) {
                            console.log('Waiting for tides...', attempts);
                        }
                    }

                    if (window.tidesData && window.tidesData.length > 0) {
                        console.log('Tides loaded after waiting:', window.tidesData.length);
                        this.calculateToday(window.tidesData);
                    } else {
                        console.log('Falling back to simulated data');
                        // Fallback to simulated data
                        const tides = window.NH100.generateSimulatedTides();
                        this.calculateToday(tides);
                    }
                },

                calculateToday(tides) {
                    const today = new Date();
                    this.result = window.NH100.isRouteRideable(today, tides);
                    this.loading = false;
                },

                formatTime(time) {
                    return window.NH100.formatTime(time);
                }
            }));

            // Calendar Component
            Alpine.data('calendar', () => ({
                loading: true,
                days: [],
                firstDayOfWeek: 0,
                currentDate: new Date(),
                maxDays: 450, // ~15 maanden vooruit om alle beschikbare CSV data te tonen

                get currentMonthYear() {
                    const monthNames = [
                        'januari', 'februari', 'maart', 'april', 'mei', 'juni',
                        'juli', 'augustus', 'september', 'oktober', 'november', 'december'
                    ];
                    const month = monthNames[this.currentDate.getMonth()];
                    const year = this.currentDate.getFullYear();
                    return `${month} ${year}`;
                },

                get mobileDays() {
                    // Filter alleen vandaag en toekomstige dagen
                    return this.days.filter(day => day.daysFromToday >= 0);
                },

                async init() {
                    // Wait for tides data from Livewire
                    await this.loadTides();

                    // Listen for tide updates
                    window.addEventListener('tides-updated', (event) => {
                        this.calculateDays(event.detail.tides);
                    });
                },

                previousMonth() {
                    const newDate = new Date(this.currentDate);
                    newDate.setMonth(newDate.getMonth() - 1);
                    this.currentDate = newDate;
                    console.log('Previous month:', this.currentMonthYear);
                    this.calculateDays(window.tidesData);
                },

                nextMonth() {
                    const newDate = new Date(this.currentDate);
                    newDate.setMonth(newDate.getMonth() + 1);
                    this.currentDate = newDate;
                    console.log('Next month:', this.currentMonthYear);
                    this.calculateDays(window.tidesData);
                },

                async loadTides() {
                    // Check if data is already available
                    if (window.tidesData && window.tidesData.length > 0) {
                        console.log('Calendar: Tides already available:', window.tidesData.length);
                        this.calculateDays(window.tidesData);
                        return;
                    }

                    // Wait for Livewire to load real API data
                    const maxAttempts = 50; // Increased attempts for API data
                    let attempts = 0;

                    while ((!window.tidesData || window.tidesData.length === 0) && attempts <
                        maxAttempts) {
                        await new Promise(resolve => setTimeout(resolve, 200));
                        attempts++;

                        if (attempts % 10 === 0) {
                            console.log('Calendar: Waiting for API tides...', attempts);
                        }
                    }

                    if (window.tidesData && window.tidesData.length > 0) {
                        console.log('Calendar: API tides loaded:', window.tidesData.length);
                        this.calculateDays(window.tidesData);
                    } else {
                        console.log('Calendar: No API data available, using simulated data');
                        // Only use simulated data as last resort
                        const tides = window.NH100.generateSimulatedTides();
                        this.calculateDays(tides);
                    }
                },

                calculateDays(tides) {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    // Start from geselecteerde maand (eerste dag van de maand) zodat ook verleden zichtbaar is
                    const firstDayOfMonth = new Date(this.currentDate);
                    firstDayOfMonth.setDate(1);

                    // Bepaal eerste weekdag voor de eerste dag van de maand (maandag = 0, zondag = 6)
                    const dayOfWeek = firstDayOfMonth.getDay();
                    this.firstDayOfWeek = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
                    this.days = [];

                    // Calculate last day of the month
                    const lastDayOfMonth = new Date(firstDayOfMonth.getFullYear(), firstDayOfMonth
                        .getMonth() + 1, 0);

                    // Loop door alle dagen van de maand, inclusief verleden
                    let currentDate = new Date(firstDayOfMonth);
                    while (currentDate <= lastDayOfMonth) {
                        const daysFromToday = Math.floor((currentDate - today) / (1000 * 60 * 60 * 24));

                        // Only add if within maxDays limit
                        if (daysFromToday <= this.maxDays) {
                            const result = window.NH100.isRouteRideable(currentDate, tides);

                            this.days.push({
                                date: new Date(currentDate),
                                result: result,
                                isToday: daysFromToday === 0,
                                daysFromToday: daysFromToday
                            });
                        }

                        // Move to next day
                        currentDate.setDate(currentDate.getDate() + 1);
                    }

                    this.loading = false;
                },

                formatTime(time) {
                    return window.NH100.formatTime(time);
                },

                formatDateFull(date) {
                    const day = date.getDate();
                    const monthNames = [
                        'januari', 'februari', 'maart', 'april', 'mei', 'juni',
                        'juli', 'augustus', 'september', 'oktober', 'november', 'december'
                    ];
                    const month = monthNames[date.getMonth()];
                    return `${day} ${month}`;
                },

                getWeekday(date) {
                    const weekdays = ['Zondag', 'Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag'];
                    return weekdays[date.getDay()];
                }
            }));
        });
    </script>

    <style>
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

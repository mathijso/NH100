<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title x-data="todayStatus"
        x-text="loading ? 'NH100 Route Planner' : (result ? (result.rideable ? '✅ NH100 Route Planner - Route berijdbaar vandaag!' : '❌ NH100 Route Planner - Route niet geschikt vandaag') : 'NH100 Route Planner')"
        x-init="init()">NH100 Route Planner</title>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/nh100.js'])
    @livewireStyles
</head>

<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    @livewire('tide-data')
    @livewire('wind-data')

    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 mb-6 animate-fadeIn">
            <h1 class="text-3xl md:text-4xl font-bold text-indigo-900 mb-4">
                🚴‍♂️ NH100 Route Planner
            </h1>
            <p class="text-gray-600 leading-relaxed">
                De NH100 is het favoriete offroad trainingsrondje door het Noordhollands Duinreservaat,
                over het strand en door de bossen van Schoorl en Bergen. Deze planner helpt je bepalen
                wanneer de route berijdbaar is op basis van getijden en seizoensrestricties. De route is bedacht door <a
                    href="http://nh100.nl/" target="_blank" class="underline"> Laurens ten Dam en Nikki Terpstra</a>.
            </p>

            <!-- Vandaag Status -->
            <div x-data="todayStatus" class="mt-4 mb-4">
                <template x-if="loading">
                    <div class="flex items-center justify-center py-2">
                        <div class="animate-pulse text-gray-400 text-sm">Gegevens laden...</div>
                    </div>
                </template>

                <template x-if="!loading && result">
                    <div
                        :class="{
                            'bg-green-50 border border-green-200 rounded-lg p-3': result.status === 'green',
                            'bg-yellow-50 border border-yellow-200 rounded-lg p-3': result.status === 'amber',
                            'bg-red-50 border border-red-200 rounded-lg p-3': !result.status || result
                                .status === 'red'
                        }">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl"
                                    x-text="result.status === 'green' ? '✅' : (result.status === 'amber' ? '🟧' : '❌')"></span>
                                <div>
                                    <h3 :class="{
                                        'text-lg font-bold text-green-900': result.status === 'green',
                                        'text-lg font-bold text-yellow-900': result.status === 'amber',
                                        'text-lg font-bold text-red-900': !result.status || result.status === 'red'
                                    }"
                                        x-text="result.status === 'green' ? 'Route berijdbaar vandaag!' : (result.status === 'amber' ? 'Route mogelijk, afhankelijk van timing/snelheid' : 'Route niet geschikt vandaag')">
                                    </h3>
                                    <p :class="{
                                        'text-sm text-green-700': result.status === 'green',
                                        'text-sm text-yellow-700': result.status === 'amber',
                                        'text-sm text-red-700': !result.status || result.status === 'red'
                                    }"
                                        x-text="result.reason"></p>
                                </div>
                            </div>

                            <!-- Getijden Info Compact - Chronologisch gesorteerd -->
                            <!-- Laat de getijden inline zien -->
                            <template x-if="result.allTides && result.allTides.length > 0">
                                <div class="text-xs">
                                    <template x-for="(tide, i) in result.allTides" :key="tide.time">
                                        <span
                                            :class="{
                                                'text-green-700': result.status === 'green',
                                                'text-yellow-700': result.status === 'amber',
                                                'text-red-700': !result.status || result.status === 'red'
                                            }">
                                            <span x-text="` ${tide.tideType}: ${formatTime(tide.time)}`"></span>
                                            <span x-show="i !== result.allTides.length - 1"> - </span>
                                        </span>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Route Info met Afbeelding en Links -->
            <div class="mt-6 grid md:grid-cols-3 gap-6">
                <!-- Route Afbeelding met Windroos -->
                <div class="bg-gray-50 rounded-xl p-4 flex flex-col gap-4 col-span-2">
                    <!-- Route Afbeelding -->
                    <a href="https://www.komoot.com/nl-nl/tour/296286553" target="_blank" class="flex-1 flex items-center justify-center">
                        <img src="{{ asset('images/nh100.png') }}" alt="NH100 Route"
                            class="rounded-lg shadow-lg max-h-80 object-contain hover:scale-105 transition-transform duration-300"
                            onerror="this.style.display='none'" />
                    </a>
                    
                    <!-- Weer Card -->
                    <div x-data="windRose" class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-4 shadow-lg border border-blue-100">
                        <template x-if="loading">
                            <div class="flex items-center justify-center py-8">
                                <div class="animate-pulse text-gray-400 text-sm">Weergegevens laden...</div>
                            </div>
                        </template>

                        <template x-if="!loading && error">
                            <div class="text-center text-gray-500 text-sm py-8" x-text="error"></div>
                        </template>

                        <template x-if="!loading && !error && windData">
                            <div class="space-y-3">
                                <!-- Header met titel en temperatuur -->
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-800">Weer onderweg</h4>
                                        <p class="text-xs text-gray-500">Nu</p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold text-blue-600" x-text="`${windData.temperature}°C`"></div>
                                    </div>
                                </div>

                                <!-- Compacte layout: Windroos + Info cards -->
                                <div class="flex flex-col md:flex-row items-center gap-3">
                                    <!-- Windroos SVG - kleiner gemaakt -->
                                    <div class="relative flex-shrink-0">
                                        <svg width="110" height="110" viewBox="0 0 110 110" class="transform">
                                            <!-- Achtergrond cirkel met gradient -->
                                            <defs>
                                                <radialGradient id="bgGradient" cx="50%" cy="50%" r="50%">
                                                    <stop offset="0%" style="stop-color:#e0f2fe;stop-opacity:1" />
                                                    <stop offset="100%" style="stop-color:#bae6fd;stop-opacity:1" />
                                                </radialGradient>
                                            </defs>
                                            <circle cx="55" cy="55" r="48" fill="url(#bgGradient)" stroke="#0ea5e9" stroke-width="2"/>
                                            
                                            <!-- Windrichtingen -->
                                            <g class="text-gray-600">
                                                <text x="55" y="15" text-anchor="middle" font-size="11" font-weight="bold" fill="#0284c7">N</text>
                                                <text x="97" y="58" text-anchor="middle" font-size="10" fill="#64748b">O</text>
                                                <text x="55" y="102" text-anchor="middle" font-size="10" fill="#64748b">Z</text>
                                                <text x="13" y="58" text-anchor="middle" font-size="10" fill="#64748b">W</text>
                                            </g>
                                            
                                            <!-- Hulplijnen -->
                                            <line x1="55" y1="18" x2="55" y2="92" stroke="#94a3b8" stroke-width="1" opacity="0.4"/>
                                            <line x1="18" y1="55" x2="92" y2="55" stroke="#94a3b8" stroke-width="1" opacity="0.4"/>
                                            <line x1="27" y1="27" x2="83" y2="83" stroke="#94a3b8" stroke-width="1" opacity="0.3"/>
                                            <line x1="83" y1="27" x2="27" y2="83" stroke="#94a3b8" stroke-width="1" opacity="0.3"/>
                                            
                                            <!-- Windpijl (rotated based on wind direction) -->
                                            <g :style="`transform: rotate(${windData.direction_degrees}deg); transform-origin: 55px 55px;`" class="transition-transform duration-1000">
                                                <!-- Pijlschaduw -->
                                                <line x1="55" y1="55" x2="55" y2="23" stroke="#1e293b" stroke-width="3" stroke-linecap="round" opacity="0.2"/>
                                                <!-- Pijlschacht -->
                                                <line x1="55" y1="55" x2="55" y2="22" stroke="#dc2626" stroke-width="3" stroke-linecap="round"/>
                                                <!-- Pijlpunt -->
                                                <polygon points="55,17 50,27 60,27" fill="#dc2626"/>
                                                <!-- Pijlstaart -->
                                                <line x1="50" y1="80" x2="55" y2="70" stroke="#dc2626" stroke-width="2"/>
                                                <line x1="60" y1="80" x2="55" y2="70" stroke="#dc2626" stroke-width="2"/>
                                            </g>
                                            
                                            <!-- Centrum punt -->
                                            <circle cx="55" cy="55" r="4" fill="#1e40af" stroke="white" stroke-width="2"/>
                                        </svg>
                                    </div>
                                    
                                    <!-- Info cards in 3x2 grid op desktop, 2 kolommen op mobile -->
                                    <div class="flex-1 w-full">
                                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-2">
                                            <!-- Temperatuur card (alleen op kleine schermen zichtbaar als duplicate, anders verborgen) -->
                                            <div class="bg-white rounded-lg p-2 shadow-sm md:hidden">
                                                <div class="text-xs text-gray-500 mb-1">Temperatuur</div>
                                                <div class="text-base font-bold text-blue-600">
                                                    <span x-text="`${windData.temperature}°C`"></span>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-white rounded-lg p-2 shadow-sm">
                                                <div class="text-xs text-gray-500 mb-1">Richting</div>
                                                <div class="text-sm font-bold text-gray-800">
                                                    <span x-text="windData.direction_text"></span>
                                                </div>
                                            </div>
                                            <div class="bg-white rounded-lg p-2 shadow-sm">
                                                <div class="text-xs text-gray-500 mb-1">Kracht</div>
                                                <div class="text-sm font-bold text-gray-800">
                                                    <span x-text="`${windData.beaufort} Bft`"></span>
                                                </div>
                                            </div>
                                            <div class="bg-white rounded-lg p-2 shadow-sm">
                                                <div class="text-xs text-gray-500 mb-1">Snelheid</div>
                                                <div class="text-sm font-bold text-gray-800">
                                                    <span x-text="`${windData.speed_kmh} km/h`"></span>
                                                </div>
                                            </div>
                                            <div class="bg-white rounded-lg p-2 shadow-sm" x-show="windData.gust_kmh">
                                                <div class="text-xs text-gray-500 mb-1">Windstoten</div>
                                                <div class="text-sm font-bold text-orange-600">
                                                    <span x-text="`${windData.gust_kmh} km/h`"></span>
                                                </div>
                                            </div>
                                            <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-lg p-2 shadow-sm border border-amber-200 col-span-2 lg:col-span-1" x-show="windData.sunrise">
                                                <div class="text-xs text-amber-700 font-medium mb-1">Zonsopgang</div>
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
                    <!-- Komoot Link -->
                    <div class="flex flex-col gap-4">
                        <!-- Komoot Link -->
                        <a href="https://www.komoot.com/nl-nl/tour/296286553" target="_blank"
                            class="flex-1 block bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-xl p-4 shadow-lg transition-all hover:shadow-xl min-w-[0]">
                            <div class="flex items-center gap-3">
                                <div class="bg-white rounded-lg p-2">
                                    <img src="{{ asset('images/komoot.jpg') }}" alt="Komoot" class="w-10 h-10">
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg">Bekijk officiele route</h3>
                                </div>
                            </div>
                        </a>
                        <a href="{{ asset('gpx/2025-09-15_296286553_NH100-2026(1509).gpx') }}"
                            download="2025-09-15_296286553_NH100-2026(1509).gpx"
                            class="inline-flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-neutral-500 to-neutral-600 hover:from-neutral-600 hover:to-neutral-700 text-white text-lg rounded-xl shadow-lg font-semibold transition-all duration-200 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-neutral-400 group">
                            <div class="bg-white rounded-lg p-4">
                                <svg class="w-6 h-6  text-black group-hover:text-neutral-100 transition-colors"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <polyline points="7 11 12 16 17 11" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <line x1="12" y1="4" x2="12" y2="16"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            Download GPX route
                        </a>
                        <!-- PWN Duinkaart Link -->
                        <a href="https://www.pwn.nl/duinkaartkopen#" target="_blank"
                            class="flex-1 block bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl p-4 shadow-lg transition-all hover:shadow-xl min-w-[0]">
                            <div class="flex items-center gap-3">
                                <div class="bg-white rounded-lg p-2">
                                    <img src="{{ asset('images/pwn.svg') }}" alt="PWN Duinkaart" class="w-10 h-10">
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg">Koop Duinkaart</h3>
                                    <p class="text-sm text-blue-50">Verplicht - €2,00</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class=" bg-amber-50 border-l-4 border-amber-400 p-4 rounded">
                        <h3 class="font-semibold text-amber-800 mb-2">📋 Belangrijke restricties:</h3>
                        <ul class="text-sm text-amber-700 space-y-1">
                            <li>• Je moet voor 10:30 uit het duinreservaat zijn</li>
                            <li>• Tussen 1 okt - 1 mei: strand hele dag toegankelijk</li>
                            <li>• Strandgedeelte alleen bij laagwater goed te fietsen</li>
                            <li>• Duinkaart verplicht (<a href="https://www.pwn.nl/duinkaartkopen#" target="_blank"
                                    class="text-blue-500 underline">€2,00 per dag</a>)</li>
                        </ul>
                    </div>
                </div>




            </div>
        </div>

        <!-- Kalender View -->
        <div x-data="calendar" class="bg-white rounded-2xl shadow-xl p-6 md:p-8 animate-fadeIn">
            <!-- Kalender Header met Navigatie -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <button @click="previousMonth()"
                        class="p-2 rounded-lg bg-indigo-100 hover:bg-indigo-200 transition-colors">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <h2 class="text-2xl font-bold text-indigo-900" x-text="currentMonthYear"></h2>
                    <button @click="nextMonth()"
                        class="p-2 rounded-lg bg-indigo-100 hover:bg-indigo-200 transition-colors">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </button>
                </div>
                <div class="text-sm text-gray-600">
                    <span x-text="`${days.length} dagen`"></span>
                </div>
            </div>

            <template x-if="loading">
                <div class="flex items-center justify-center py-8">
                    <div class="animate-pulse text-gray-400">Kalender laden...</div>
                </div>
            </template>

            <template x-if="!loading">
                <div>
                    <!-- Mobiele Lijstweergave -->
                    <div class="md:hidden space-y-2">
                        <template x-for="(day, index) in mobileDays" :key="index">
                            <div
                                :class="{
                                    'bg-green-50 border-l-4 border-green-500 rounded-lg p-4 shadow-sm': day.result.status === 'green',
                                    'bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-4 shadow-sm': day.result.status === 'amber',
                                    'bg-red-50 border-l-4 border-red-500 rounded-lg p-4 shadow-sm': !day.result.status || day.result.status === 'red',
                                    'ring-2 ring-indigo-500': day.isToday
                                }">
                                <!-- Header: Datum + Status -->
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl"
                                            x-text="day.result.status === 'green' ? '✅' : (day.result.status === 'amber' ? '🟧' : '❌')"></span>
                                        <div>
                                            <div class="font-bold text-lg"
                                                :class="{
                                                    'text-green-900': day.result.status === 'green',
                                                    'text-yellow-900': day.result.status === 'amber',
                                                    'text-red-900': !day.result.status || day.result.status === 'red'
                                                }"
                                                x-text="formatDateFull(day.date)"></div>
                                            <div class="text-xs text-gray-500" x-text="getWeekday(day.date)"></div>
                                        </div>
                                    </div>
                                    <template x-if="day.isToday">
                                        <div class="bg-black text-white px-2 py-1 rounded text-xs font-bold">NU</div>
                                    </template>
                                </div>

                                <!-- Status bericht -->
                                <template x-if="day.result.reason">
                                    <div class="mb-2 text-sm font-medium"
                                        :class="{
                                            'text-green-800': day.result.status === 'green',
                                            'text-yellow-800': day.result.status === 'amber',
                                            'text-red-800': !day.result.status || day.result.status === 'red'
                                        }"
                                        x-text="day.result.reason"></div>
                                </template>

                                <!-- Getijden informatie -->
                                <template x-if="day.result.allTides && day.result.allTides.length > 0">
                                    <div class="space-y-1">
                                        <template x-for="tide in day.result.allTides" :key="tide.time">
                                            <div class="flex items-center gap-2 text-sm"
                                                :class="{
                                                    'text-green-700': day.result.status === 'green',
                                                    'text-yellow-700': day.result.status === 'amber',
                                                    'text-red-700': !day.result.status || day.result.status === 'red'
                                                }">
                                                <span class="font-semibold min-w-[50px]" x-text="tide.tideType"></span>
                                                <span x-text="formatTime(tide.time)"></span>
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
                        <div class="grid grid-cols-7 gap-1 mb-2">
                            <template x-for="day in ['Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za', 'Zo']" :key="day">
                                <div class="font-semibold text-center p-2 bg-gray-50 border-1 border-gray-200 text-dark rounded-lg text-sm md:text-base"
                                    x-text="day"></div>
                            </template>
                        </div>

                        <!-- Kalender grid -->
                        <div class="grid grid-cols-7 gap-1">
                            <!-- Empty cells for days before eerste dag van de maand -->
                            <template x-for="i in firstDayOfWeek" :key="`empty-${i}`">
                                <div class="bg-gray-100 rounded-lg p-2 min-h-24"></div>
                            </template>

                            <!-- Calendar days -->
                            <template x-for="(day, index) in days" :key="index">
                                <div
                                    :class="{
                                        'bg-green-50 border-green-400 border-2 rounded-lg p-2 min-h-24 hover:shadow-lg transition-shadow': day
                                            .result.status === 'green',
                                        'bg-yellow-50 border-yellow-400 border-2 rounded-lg p-2 min-h-24 hover:shadow-lg transition-shadow': day
                                            .result.status === 'amber',
                                        'bg-red-50 border-red-400 border-2 rounded-lg p-2 min-h-24 hover:shadow-lg transition-shadow':
                                            !day.result.status || day.result.status === 'red',
                                        'ring-2 ring-indigo-500': day.isToday
                                    }">
                                    <div class="flex justify-between items-start mb-1">
                                        <div :class="{
                                            'font-bold text-green-900 text-sm md:text-base': day.result
                                                .status === 'green',
                                            'font-bold text-yellow-900 text-sm md:text-base': day.result
                                                .status === 'amber',
                                            'font-bold text-red-900 text-sm md:text-base': !day.result.status || day.result
                                                .status === 'red'
                                        }"
                                            x-text="day.date.getDate()"></div>
                                        <span class="text-lg"
                                            x-text="day.result.status === 'green' ? '✅' : (day.result.status === 'amber' ? '🟧' : '❌')"></span>
                                    </div>
                                    <template x-if="day.isToday">
                                        <div class="text-xs bg-black text-white px-1 py-0.5 rounded mb-1 text-center">Nu
                                        </div>
                                    </template>
                                    <!-- Toon reden bij rode dagen in plaats van +d/-d badge -->
                                    <template
                                        x-if="day.result && (day.result.status === 'red' || !day.result.status) && day.result.reason">
                                        <div class="text-[11px] bg-red-100 text-red-800 px-1 py-0.5 rounded mb-1 text-center truncate"
                                            x-text="day.result.reason"></div>
                                    </template>
                                    <div
                                        :class="{
                                            'text-xs text-green-600 space-y-0.5': day.result.status === 'green',
                                            'text-xs text-yellow-600 space-y-0.5': day.result.status === 'amber',
                                            'text-xs text-red-600 space-y-0.5': !day.result.status || day.result
                                                .status === 'red'
                                        }">
                                        <!-- Alle getijden chronologisch -->
                                        <template x-if="day.result.allTides && day.result.allTides.length > 0">
                                            <template x-for="tide in day.result.allTides" :key="tide.time">
                                                <div :class="tide.tideType === 'Eb' ? 'font-semibold' : ''"
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

        <footer>


            <div class="mt-8 text-center text-gray-500 text-sm">
                <p>© 2025 NH100 Route Planner. Ontwikkeld door <a href="https://oggel-codelabs.nl" target="_blank"
                        class="text-blue-500">Oggel Codelabs</a></p>
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

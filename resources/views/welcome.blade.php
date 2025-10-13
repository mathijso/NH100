<!DOCTYPE html>
<html lang="nl">

    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title x-data="todayStatus" x-text="loading ? 'NH100 Route Planner' : (result ? (result.rideable ? '✅ NH100 Route Planner - Route berijdbaar vandaag!' : '❌ NH100 Route Planner - Route niet geschikt vandaag') : 'NH100 Route Planner')" x-init="init()">NH100 Route Planner</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/nh100.js'])
    @livewireStyles
</head>

<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    @livewire('tide-data')
    
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 mb-6 animate-fadeIn">
            <h1 class="text-3xl md:text-4xl font-bold text-indigo-900 mb-4">
                🚴‍♂️ NH100 Route Planner
            </h1>
            <p class="text-gray-600 leading-relaxed">
                De NH100 is het favoriete offroad trainingsrondje door het Noordhollands Duinreservaat,
                over het strand en door de bossen van Schoorl en Bergen. Deze planner helpt je bepalen
                wanneer de route berijdbaar is op basis van getijden en seizoensrestricties. De route is bedacht door <a href="http://nh100.nl/" target="_blank" class="underline"> Laurens ten Dam en Nikki Terpstra</a>. 
            </p>

            <!-- Vandaag Status -->
            <div x-data="todayStatus" class="mt-4 mb-4">
                <template x-if="loading">
                    <div class="flex items-center justify-center py-2">
                        <div class="animate-pulse text-gray-400 text-sm">Gegevens laden...</div>
                    </div>
                </template>

                <template x-if="!loading && result">
                    <div :class="{
                            'bg-green-50 border border-green-200 rounded-lg p-3': result.status === 'green',
                            'bg-yellow-50 border border-yellow-200 rounded-lg p-3': result.status === 'amber',
                            'bg-red-50 border border-red-200 rounded-lg p-3': !result.status || result.status === 'red'
                        }">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl" x-text="result.status === 'green' ? '✅' : (result.status === 'amber' ? '🟧' : '❌')"></span>
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
                                        }" x-text="result.reason"></p>
                                </div>
                            </div>
                            
                            <!-- Getijden Info Compact - Chronologisch gesorteerd -->
                            <!-- Laat de getijden inline zien -->
                            <template x-if="result.allTides && result.allTides.length > 0">
                                <div class="text-xs">
                                    <template x-for="(tide, i) in result.allTides" :key="tide.time">
                                        <span :class="{
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
            <div class="mt-6 grid md:grid-cols-2 gap-6">
                <!-- Route Afbeelding -->
                <div class="bg-gray-50 rounded-xl p-4 flex items-center justify-center">
                    <a href="https://www.komoot.com/nl-nl/tour/296286553" target="_blank">
                    <img src="{{ asset('images/nh100.png') }}" 
                         alt="NH100 Route" 
                         class="rounded-lg shadow-lg max-h-96 object-contain hover:scale-105 transition-transform duration-300"
                         onerror="this.style.display='none'" />
                        </a>
                </div>

                <!-- Route Links en Info -->
                <div class="space-y-4">
                    <!-- Komoot Link -->
                    <div class="flex flex-row gap-4">
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

                    <!-- Route Stats -->
                    <div class="mt-6 bg-amber-50 border-l-4 border-amber-400 p-4 rounded">
                        <h3 class="font-semibold text-amber-800 mb-2">📋 Belangrijke restricties:</h3>
                        <ul class="text-sm text-amber-700 space-y-1">
                            <li>• Je moet voor 10:30 uit het duinreservaat zijn, start vroeg</li>
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
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <h2 class="text-2xl font-bold text-indigo-900" x-text="currentMonthYear"></h2>
                    <button @click="nextMonth()" 
                            class="p-2 rounded-lg bg-indigo-100 hover:bg-indigo-200 transition-colors">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
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
                            <div :class="{
                                    'bg-green-50 border-green-400 border-2 rounded-lg p-2 min-h-24 hover:shadow-lg transition-shadow': day.result.status === 'green',
                                    'bg-yellow-50 border-yellow-400 border-2 rounded-lg p-2 min-h-24 hover:shadow-lg transition-shadow': day.result.status === 'amber',
                                    'bg-red-50 border-red-400 border-2 rounded-lg p-2 min-h-24 hover:shadow-lg transition-shadow': !day.result.status || day.result.status === 'red',
                                    'ring-2 ring-indigo-500': day.isToday
                                }">
                                <div class="flex justify-between items-start mb-1">
                                    <div :class="{
                                            'font-bold text-green-900 text-sm md:text-base': day.result.status === 'green',
                                            'font-bold text-yellow-900 text-sm md:text-base': day.result.status === 'amber',
                                            'font-bold text-red-900 text-sm md:text-base': !day.result.status || day.result.status === 'red'
                                        }" 
                                         x-text="day.date.getDate()"></div>
                                    <span class="text-lg" x-text="day.result.status === 'green' ? '✅' : (day.result.status === 'amber' ? '🟧' : '❌')"></span>
                                </div>
                                <template x-if="day.isToday">
                                    <div class="text-xs bg-black text-white px-1 py-0.5 rounded mb-1 text-center">Nu</div>
                                </template>
                                <!-- Toon reden bij rode dagen in plaats van +d/-d badge -->
                                <template x-if="day.result && (day.result.status === 'red' || !day.result.status) && day.result.reason">
                                    <div class="text-[11px] bg-red-100 text-red-800 px-1 py-0.5 rounded mb-1 text-center truncate" x-text="day.result.reason"></div>
                                </template>
                                <div :class="{
                                        'text-xs text-green-600 space-y-0.5': day.result.status === 'green',
                                        'text-xs text-yellow-600 space-y-0.5': day.result.status === 'amber',
                                        'text-xs text-red-600 space-y-0.5': !day.result.status || day.result.status === 'red'
                                    }">
                                    <!-- Alle getijden chronologisch -->
                                    <template x-if="day.result.allTides && day.result.allTides.length > 0">
                                        <template x-for="tide in day.result.allTides" :key="tide.time">
                                            <div :class="tide.tideType === 'Eb' ? 'font-semibold' : ''" x-text="`${tide.tideType}: ${formatTime(tide.time)}`"></div>
                                        </template>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <footer>
        

        <div class="mt-8 text-center text-gray-500 text-sm">
            <p>© 2025 NH100 Route Planner. Ontwikkeld door <a href="https://oggel-codelabs.nl" target="_blank" class="text-blue-500">Oggel Codelabs</a></p>
        </div>
        </footer>
    </div>

    @livewireScripts

    <script>
        // Alpine.js components
        document.addEventListener('alpine:init', () => {
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
                    
                    while ((!window.tidesData || window.tidesData.length === 0) && attempts < maxAttempts) {
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
                    
                    while ((!window.tidesData || window.tidesData.length === 0) && attempts < maxAttempts) {
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
                    const lastDayOfMonth = new Date(firstDayOfMonth.getFullYear(), firstDayOfMonth.getMonth() + 1, 0);
                    
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

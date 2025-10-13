<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NH100 Route Planner</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/nh100.js'])
    @livewireStyles
</head>

<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 mb-6 animate-fadeIn">
            <h1 class="text-3xl md:text-4xl font-bold text-indigo-900 mb-4">
                🚴‍♂️ NH100 Route Planner
            </h1>
            <p class="text-gray-600 leading-relaxed">
                De NH100 is het favoriete offroad trainingsrondje door het Noordhollands Duinreservaat,
                over het strand en door de bossen van Schoorl en Bergen. Deze planner helpt je bepalen
                wanneer de route berijdbaar is op basis van getijden en seizoensrestricties.
            </p>

            <!-- Route Info met Afbeelding en Links -->
            <div class="mt-6 grid md:grid-cols-2 gap-6">
                <!-- Route Afbeelding -->
                <div class="bg-gray-50 rounded-xl p-4 flex items-center justify-center">
                    <img src="{{ asset('images/nh100.png') }}" 
                         alt="NH100 Route" 
                         class="rounded-lg shadow-lg max-h-96 object-contain"
                         onerror="this.style.display='none'" />
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

        <!-- Vandaag Status -->
        <div x-data="todayStatus" class="bg-white rounded-2xl shadow-xl p-6 md:p-8 mb-6 animate-fadeIn">
            <h2 class="text-2xl font-bold text-indigo-900 mb-4">Vandaag</h2>
            
            <template x-if="loading">
                <div class="flex items-center justify-center py-8">
                    <div class="animate-pulse text-gray-400">Gegevens laden...</div>
                </div>
            </template>

            <template x-if="!loading && result">
                <div>
                    <div :class="`bg-${result.rideable ? 'green' : 'red'}-50 border-2 border-${result.rideable ? 'green' : 'red'}-200 rounded-xl p-6`">
                        <div class="flex items-center justify-center mb-4">
                            <span class="text-6xl" x-text="result.rideable ? '✅' : '❌'"></span>
                        </div>
                        <h3 :class="`text-2xl font-bold text-${result.rideable ? 'green' : 'red'}-900 text-center mb-2`"
                            x-text="result.rideable ? 'Route kan gereden worden!' : 'Route niet geschikt vandaag'">
                        </h3>
                        <p :class="`text-${result.rideable ? 'green' : 'red'}-700 text-center`" x-text="result.reason"></p>
                        
                        <!-- Getijden Info -->
                        <template x-if="result.lowTides.length > 0 || result.highTides.length > 0">
                            <div :class="`mt-4 pt-4 border-t border-${result.rideable ? 'green' : 'red'}-200 space-y-2`">
                                <template x-if="result.lowTides.length > 0">
                                    <div>
                                        <p :class="`text-sm font-semibold text-${result.rideable ? 'green' : 'red'}-700 mb-1`">🌊 Eb:</p>
                                        <template x-for="tide in result.lowTides" :key="tide.time">
                                            <p :class="`text-sm text-${result.rideable ? 'green' : 'red'}-600 ml-4`" 
                                               x-text="`• ${formatTime(tide.time)} (${tide.height.toFixed(2)}m)`"></p>
                                        </template>
                                    </div>
                                </template>
                                
                                <template x-if="result.highTides.length > 0">
                                    <div>
                                        <p :class="`text-sm font-semibold text-${result.rideable ? 'green' : 'red'}-700 mb-1`">🌊 Vloed:</p>
                                        <template x-for="tide in result.highTides" :key="tide.time">
                                            <p :class="`text-sm text-${result.rideable ? 'green' : 'red'}-600 ml-4`" 
                                               x-text="`• ${formatTime(tide.time)} (${tide.height.toFixed(2)}m)`"></p>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Aanbevolen tijdschema -->
                    <template x-if="result.rideable">
                        <div class="mt-4 bg-blue-50 rounded-lg p-4">
                            <h4 class="font-semibold text-blue-900 mb-2">⏰ Aanbevolen tijdschema:</h4>
                            <ul class="text-sm text-blue-700 space-y-1">
                                <li>• 07:00 - Start in duinreservaat</li>
                                <li>• 10:30 - Vertrek uit duinreservaat</li>
                                <li>• 10:30-12:00 - Strandgedeelte (optimale eb)</li>
                                <li>• 12:00+ - MTB parcours Schoorl en Bergen</li>
                            </ul>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Kalender View -->
        <div x-data="calendar" class="bg-white rounded-2xl shadow-xl p-6 md:p-8 animate-fadeIn">
            <h2 class="text-2xl font-bold text-indigo-900 mb-6">Kalender Overzicht - Komende 30 Dagen</h2>
            
            <template x-if="loading">
                <div class="flex items-center justify-center py-8">
                    <div class="animate-pulse text-gray-400">Kalender laden...</div>
                </div>
            </template>

            <template x-if="!loading">
                <div>
                    <!-- Weekdag headers -->
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        <template x-for="day in ['Zo', 'Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za']" :key="day">
                            <div class="font-semibold text-center p-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg text-sm md:text-base" 
                                 x-text="day"></div>
                        </template>
                    </div>

                    <!-- Kalender grid -->
                    <div class="grid grid-cols-7 gap-1">
                        <!-- Empty cells for days before today -->
                        <template x-for="i in firstDayOfWeek" :key="`empty-${i}`">
                            <div class="bg-gray-100 rounded-lg p-2 min-h-24"></div>
                        </template>

                        <!-- Calendar days -->
                        <template x-for="(day, index) in days" :key="index">
                            <div :class="`${day.result.rideable ? 'bg-green-50 border-green-400' : 'bg-red-50 border-red-400'} border-2 rounded-lg p-2 min-h-24 hover:shadow-lg transition-shadow ${index === 0 ? 'ring-2 ring-indigo-500' : ''}`">
                                <div class="flex justify-between items-start mb-1">
                                    <div :class="`font-bold ${day.result.rideable ? 'text-green-900' : 'text-red-900'} text-sm md:text-base`" 
                                         x-text="day.date.getDate()"></div>
                                    <span class="text-lg" x-text="day.result.rideable ? '✅' : '❌'"></span>
                                </div>
                                <template x-if="index === 0">
                                    <div class="text-xs bg-indigo-500 text-white px-1 py-0.5 rounded mb-1 text-center">Nu</div>
                                </template>
                                <div :class="`text-xs ${day.result.rideable ? 'text-green-600' : 'text-red-600'} space-y-0.5`">
                                    <template x-if="day.result.lowTides.length > 0">
                                        <div class="font-semibold" x-text="`Eb: ${formatTime(day.result.lowTides[0].time)}`"></div>
                                    </template>
                                    <template x-if="day.result.highTides.length > 0">
                                        <div x-text="`Vloed: ${formatTime(day.result.highTides[0].time)}`"></div>
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
            
            <p class="mt-2">⚠️ Controleer altijd de actuele omstandigheden voor je vertrekt - Getijdendata voor Egmond aan Zee</p>
        </div>
        
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
                    const tides = await window.NH100.fetchTideData();
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
                
                async init() {
                    const tides = await window.NH100.fetchTideData();
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    
                    this.firstDayOfWeek = today.getDay();
                    
                    for (let i = 0; i < 30; i++) {
                        const date = new Date(today);
                        date.setDate(today.getDate() + i);
                        
                        const result = window.NH100.isRouteRideable(date, tides);
                        
                        this.days.push({
                            date: date,
                            result: result
                        });
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

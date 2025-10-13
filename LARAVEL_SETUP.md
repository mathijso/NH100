# NH100 Laravel Setup - Single Page App

## ✅ Migratie Compleet

De vanilla HTML app is succesvol gemigreerd naar een Laravel single page applicatie met moderne tooling.

## 🛠️ Technologie Stack

- **Laravel 11** - Backend framework
- **Livewire 3** - Server-side rendering en reactivity
- **Alpine.js 3** - Client-side interactiviteit
- **Tailwind CSS v4** - Styling
- **Vite** - Asset bundling en HMR (Hot Module Replacement)

## 📁 Project Structuur

```
NH100/
├── resources/
│   ├── css/
│   │   └── app.css              # Tailwind configuratie
│   ├── js/
│   │   ├── app.js               # Alpine.js setup
│   │   └── nh100.js             # Getijden berekeningen en logica
│   └── views/
│       └── welcome.blade.php    # Hoofdpagina met Alpine components
├── public/
│   └── images/                  # Route afbeeldingen en logos
│       ├── nh100.png
│       ├── komoot.jpg
│       └── pwn.svg
└── routes/
    └── web.php                  # Route definitie
```

## 🚀 Ontwikkeling

### 1. Dependencies installeren

```bash
# PHP dependencies
composer install

# Node dependencies
npm install
```

### 2. Development server starten

Open **twee** terminals:

**Terminal 1 - Laravel dev server:**
```bash
php artisan serve
```

**Terminal 2 - Vite dev server (met HMR):**
```bash
npm run dev
```

De app is nu beschikbaar op: `http://localhost:8000`

### 3. Productie build

```bash
npm run build
```

## 🎨 Styling met Tailwind CSS v4

Tailwind CSS v4 is geconfigureerd met de `@tailwindcss/vite` plugin. Alle utility classes zijn beschikbaar in de Blade templates.

**Belangrijk:** Tailwind v4 gebruikt een nieuwe syntax in `app.css`:
- `@import 'tailwindcss'` in plaats van `@tailwind` directives
- `@source` directives om te specificeren welke bestanden gescand moeten worden
- `@theme` voor custom theme configuratie

## 🔄 Alpine.js Components

De applicatie gebruikt twee Alpine.js components:

### 1. `todayStatus` Component
Toont de route status voor vandaag met:
- Eb en vloed tijden
- Route berijdbaarheid
- Aanbevolen tijdschema

### 2. `calendar` Component
Toont een 30-dagen kalender met:
- Weekdag headers
- Dagelijkse route status
- Getijden informatie per dag

## 📊 Getijden Data

De getijden logica is geëxtraheerd naar `resources/js/nh100.js` en beschikbaar via `window.NH100`:

**Functies:**
- `fetchTideData()` - Haalt getijden data op (nu gesimuleerd)
- `isRouteRideable(date, tides)` - Controleert of route berijdbaar is
- `formatTime(date)` - Formatteert tijd in NL formaat
- `formatDate(date)` - Formatteert datum in NL formaat
- `isWinterSeason(date)` - Check winterseizoen (1 okt - 1 mei)
- `getTidesForDay(date, tides)` - Haalt eb/vloed voor specifieke dag op

## 🔮 Volgende Stappen

### 1. Echte Getijden API Integratie

Maak een Livewire component of Laravel controller voor API calls:

```php
// app/Http/Controllers/TideController.php
public function getTides()
{
    // Call external API (WorldTides, Rijkswaterstaat, etc.)
    $response = Http::get('https://api.tides.com/...', [
        'lat' => 52.6167,
        'lon' => 4.6333,
    ]);
    
    return response()->json($response->json());
}
```

Update `nh100.js`:
```javascript
export async function fetchTideData() {
    const response = await fetch('/api/tides');
    return response.json();
}
```

### 2. Livewire Component voor Kalender

Converteer de Alpine.js calendar component naar een Livewire component voor server-side rendering:

```bash
php artisan make:livewire TideCalendar
```

```php
// app/Livewire/TideCalendar.php
class TideCalendar extends Component
{
    public $days = [];
    
    public function mount()
    {
        // Fetch tide data and calculate rideable days
        $this->days = $this->calculateDays();
    }
}
```

### 3. Database voor Getijden Caching

Maak een migration voor tide data caching:

```bash
php artisan make:migration create_tides_table
```

```php
Schema::create('tides', function (Blueprint $table) {
    $table->id();
    $table->datetime('time');
    $table->enum('type', ['Low', 'High']);
    $table->decimal('height', 5, 2);
    $table->timestamps();
});
```

### 4. Notificaties met Laravel Notifications

Stuur notificaties voor goede fiets dagen:

```bash
php artisan make:notification GoodRideDayNotification
```

### 5. PWA (Progressive Web App)

Installeer Laravel PWA package voor offline functionaliteit:

```bash
composer require silviolleite/laravel-pwa
```

## 📱 Responsive Design

De app is volledig responsive:
- **Mobiel**: 1 kolom layout, compacte kalender
- **Tablet**: 2-3 kolommen, verbeterde spacing
- **Desktop**: Volledige 7-kolommen kalender grid

## 🎯 Alpine.js + Livewire Combinatie

De huidige setup gebruikt Alpine.js voor client-side interactiviteit. Je kunt later Livewire components toevoegen voor:

1. **Server-side data handling** - Echte API calls via Laravel backend
2. **Real-time updates** - WebSocket support met Laravel Echo
3. **Form handling** - Route favoriten opslaan
4. **User authentication** - Login/register voor persoonlijke voorkeuren

**Voorbeeld Livewire + Alpine combo:**

```blade
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    
    <div x-show="open">
        @livewire('tide-details', ['date' => today()])
    </div>
</div>
```

## 🐛 Debugging

**Vite issues:**
```bash
# Clear cache
rm -rf node_modules
npm install
npm run build
```

**Laravel cache:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 📝 Opmerkingen

- De huidige versie gebruikt **gesimuleerde getijden data**
- Images worden geladen via `asset('images/...')` helper
- Alpine.js components initialiseren via `document.addEventListener('alpine:init')`
- Livewire is geïnstalleerd maar nog niet actief gebruikt (klaar voor toekomstige features)

## 🎉 Klaar voor Ontwikkeling!

Je hebt nu een moderne Laravel single page app met:
- ✅ Hot Module Replacement (HMR) via Vite
- ✅ Alpine.js voor interactiviteit
- ✅ Livewire klaar voor server-side rendering
- ✅ Tailwind CSS v4 voor styling
- ✅ Gestructureerde JavaScript modules
- ✅ Responsive design

**Start met:** `npm run dev` en `php artisan serve` en begin met bouwen! 🚴‍♂️


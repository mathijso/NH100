# 🚀 Quick Start - NH100 met Marea API

## ✅ Setup Complete!

Je NH100 Route Planner is nu geconfigureerd met **echte getijden data** van de Marea API!

## 🏃 Start de App

**Terminal 1 - Laravel:**
```bash
php artisan serve
```

**Terminal 2 - Vite (HMR):**
```bash
npm run dev
```

Of gebruik het convenience script:
```bash
./start-dev.sh
```

## 🌐 Open in Browser

```
http://localhost:8000
```

## 🎯 Wat te Verwachten

### Data Bron Indicator
Onderaan de pagina zie je welke data bron wordt gebruikt:

- ✓ **Marea API - Station Petten Zuid** - Beste! Echte meetdata (18km van Egmond)
- ✓ **Marea API (FES2014 Model)** - Goed! Global ocean model (fallback)
- ✓ **Marea API (EOT20 Model)** - Goed! Alternatief model  
- 🎲 **Gesimuleerd** - Fallback bij API problemen

### Features
- **Vandaag Status** - Direct zien of de route berijdbaar is
- **30-Dagen Kalender** - Plan vooruit met weekoverzicht
- **Eb & Vloed Tijden** - Complete getijden informatie
- **Seizoen Check** - Automatisch winter/zomer restricties

## 🔧 Configuratie

Je `.env` is geconfigureerd met:

```bash
MAREA_API_ENABLED=true
MAREA_API_KEY=53c401a9-b364-4d7a-b089-11acdc97004a
MAREA_STATION_ID=GESLA3:eb1c86d10b  # Petten Zuid - 18km van Egmond
```

### Switchen naar Gesimuleerde Data

Als je tijdelijk gesimuleerde data wilt:

```bash
# In .env
MAREA_API_ENABLED=false
```

En dan:
```bash
php artisan config:clear
```

## 📊 API Usage

Je hebt:
- **100 gratis requests** van Marea API
- **Caching van 6 uur** - efficiënt gebruik
- **Automatische fallback** bij problemen

Check je usage op: https://api.marea.ooo/account

## 🐛 Troubleshooting

### API werkt niet?

**Check logs:**
```bash
tail -f storage/logs/laravel.log
```

**Zoek naar:**
- `Marea API call successful` ✓
- `Loaded real tide data` ✓
- `Marea API call failed` ⚠️

### Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Test API Direct

**Station call (primair):**
```bash
curl -H "x-marea-api-token: 53c401a9-b364-4d7a-b089-11acdc97004a" \
  "https://api.marea.ooo/v2/tides?station_id=GESLA3:eb1c86d10b&duration=1440"
```

**Coördinaten call (fallback):**
```bash
curl -H "x-marea-api-token: 53c401a9-b364-4d7a-b089-11acdc97004a" \
  "https://api.marea.ooo/v2/tides?latitude=52.618694&longitude=4.618250&duration=1440"
```

## 📚 Documentatie

- **[MAREA_API_SETUP.md](MAREA_API_SETUP.md)** - Complete API documentatie
- **[LARAVEL_SETUP.md](LARAVEL_SETUP.md)** - Laravel setup en architectuur
- **[README.md](README.md)** - Algemene project info
- **[FEATURES.md](FEATURES.md)** - Feature lijst

## 🎉 Klaar!

Je app gebruikt nu:
- ✅ Laravel 11 backend
- ✅ Livewire 3 components
- ✅ Alpine.js interactiviteit
- ✅ Tailwind CSS v4
- ✅ Marea API - Station Petten Zuid
- ✅ Echte meetdata (18km van Egmond)
- ✅ 6-uur caching
- ✅ Automatic fallback naar coördinaten/simulatie

**Start met:** `npm run dev` en `php artisan serve`

**Open:** http://localhost:8000

**Check:** Data bron indicator onderaan de pagina

## 🚴‍♂️ Enjoy!

Plan je perfecte NH100 ride met echte getijden data! 🌊

---

**Vragen?**
- Laravel: Check `LARAVEL_SETUP.md`
- Marea API: Check `MAREA_API_SETUP.md`
- Issues: Check logs in `storage/logs/laravel.log`


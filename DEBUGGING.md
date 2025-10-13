# 🐛 Debugging Guide - NH100 Route Planner

## Console Logging

De app heeft nu uitgebreide console logging. Open je browser console (F12) om te zien wat er gebeurt.

### Verwachte Console Output

Bij correct laden zie je:

```javascript
TideData component initialized
Tides count: 141
Source: STATION
Tides already available: 141
Calendar: Tides already available: 141
```

### Problemen Oplossen

#### 1. "Gegevens laden..." blijft staan

**Symptoom:** De pagina toont "Gegevens laden..." en verandert niet.

**Check in console:**
```javascript
console.log(window.tidesData);
console.log(window.tidesSource);
```

**Mogelijke oorzaken:**

**A. Livewire laadt niet:**
```
// Console toont:
undefined
undefined
```
**Fix:**
```bash
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

**B. Livewire laadt maar Alpine niet:**
```
// Console toont:
TideData component initialized
Tides count: 0
```
**Fix:** Check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

**C. API error:**
```
// Laravel log toont:
Marea API call failed
```
**Fix:** Check .env:
```bash
MAREA_API_ENABLED=true
MAREA_API_KEY=your-key-here
MAREA_STATION_ID=GESLA3:eb1c86d10b
```

#### 2. Data laadt maar UI update niet

**Check console voor:**
```
Waiting for tides... 10
Waiting for tides... 20
Falling back to simulated data
```

Dit betekent dat Alpine de data niet kan vinden.

**Fix:**
1. Hard refresh: Cmd+Shift+R (Mac) of Ctrl+Shift+R (Windows)
2. Clear browser cache
3. Rebuild assets: `npm run build`

#### 3. Simulated data in plaats van API

**Console toont:**
```
Source: simulated
```

**Check:**

1. **Is API enabled?**
```bash
grep MAREA_API_ENABLED .env
# Should show: MAREA_API_ENABLED=true
```

2. **Is API key set?**
```bash
grep MAREA_API_KEY .env | wc -c
# Should show number > 20
```

3. **Check logs:**
```bash
tail -20 storage/logs/laravel.log
```

Look for:
- ✅ `Loaded real tide data` = Good!
- ❌ `Marea API call failed` = API problem
- ❌ `Marea API exception` = Network/config problem

## Quick Diagnostics

### 1. Check Environment

```bash
cd /Users/mathijsoggel/Code/NH100

# Check .env
grep MAREA .env

# Expected output:
# MAREA_API_ENABLED=true
# MAREA_API_KEY=53c401a9-b364-4d7a-b089-11acdc97004a
# MAREA_STATION_ID=GESLA3:eb1c86d10b
```

### 2. Test Livewire

```bash
# Watch logs in real-time
tail -f storage/logs/laravel.log
```

Then refresh page and look for:
```
Marea API station call successful
Loaded real tide data {"count":141,"source":"STATION"}
```

### 3. Test Alpine.js

Open browser console (F12) and run:

```javascript
// Check if data is available
console.log('Tides:', window.tidesData?.length);
console.log('Source:', window.tidesSource);

// Manual calculation test
if (window.NH100 && window.tidesData) {
    const result = window.NH100.isRouteRideable(new Date(), window.tidesData);
    console.log('Route rideable:', result);
}
```

### 4. Test API Directly

```bash
curl -H "x-marea-api-token: 53c401a9-b364-4d7a-b089-11acdc97004a" \
  "https://api.marea.ooo/v2/tides?station_id=GESLA3:eb1c86d10b&duration=1440" \
  | jq '.extremes | length'

# Should show number of tide extremes (e.g., 4-6 per day)
```

## Common Fixes

### Fix 1: Clear All Caches

```bash
cd /Users/mathijsoggel/Code/NH100

# Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Rebuild assets
npm run build

# Restart servers
# Ctrl+C both terminals, then:
php artisan serve
npm run dev
```

### Fix 2: Reset Livewire

```bash
# Remove Livewire cache
rm -rf storage/framework/views/*
rm -rf bootstrap/cache/*

php artisan view:clear
```

### Fix 3: Force Browser Refresh

1. Open DevTools (F12)
2. Right-click refresh button
3. Choose "Empty Cache and Hard Reload"

Or:
- Mac: Cmd + Shift + R
- Windows/Linux: Ctrl + Shift + R

### Fix 4: Check Network Tab

1. Open DevTools (F12)
2. Go to Network tab
3. Refresh page
4. Look for:
   - ✅ `livewire/update` requests (200 OK)
   - ✅ JavaScript files loading
   - ❌ Any 500 errors

## Logging Levels

### Increase Logging

Edit `app/Livewire/TideData.php`:

```php
// Add more logging
Log::info('TideData mounting...', [
    'use_real_api' => $this->useRealApi,
    'api_enabled' => config('services.marea.enabled'),
]);
```

Edit `app/Services/MareaApiService.php`:

```php
// Before API call
Log::info('Calling Marea API', [
    'station_id' => $stationId,
    'duration' => $duration,
]);

// After API call
Log::info('API Response', [
    'status' => $response->status(),
    'body_length' => strlen($response->body()),
]);
```

## Browser Console Commands

### Check Alpine.js

```javascript
// Is Alpine loaded?
console.log('Alpine:', window.Alpine);

// Check components
Alpine.devtools();

// Force data update
window.dispatchEvent(new CustomEvent('tides-updated', { 
    detail: { 
        tides: window.tidesData, 
        source: window.tidesSource 
    }
}));
```

### Manual Data Load

```javascript
// If all else fails, load simulated data manually
window.tidesData = window.NH100.generateSimulatedTides();
window.tidesSource = 'simulated';
window.dispatchEvent(new CustomEvent('tides-updated', { 
    detail: { 
        tides: window.tidesData, 
        source: 'simulated' 
    }
}));
```

## Success Indicators

### ✅ Everything Working

**Browser Console:**
```
TideData component initialized
Tides count: 141
Source: STATION
Tides already available: 141
Calendar: Tides already available: 141
```

**Laravel Log:**
```
Marea API station call successful
station_id: GESLA3:eb1c86d10b
source: STATION
Loaded real tide data {"count":141,"source":"STATION"}
```

**Page Shows:**
```
✓ Marea API - Station Petten Zuid
```

### ❌ Problems

**Stuck on "Gegevens laden...":**
- Check console for errors
- Check Laravel logs
- Try clearing all caches
- Hard refresh browser

**Shows "Gesimuleerd":**
- API not enabled or failing
- Check .env configuration
- Check API key validity
- Check network connectivity

## Get Help

If still stuck, gather this info:

```bash
# 1. Environment check
grep MAREA .env

# 2. Last 20 log lines
tail -20 storage/logs/laravel.log

# 3. Console output
# Copy from browser console (F12)

# 4. Network tab
# Screenshot of Network tab showing any errors
```

Then check:
1. [MAREA_API_SETUP.md](MAREA_API_SETUP.md) - API configuration
2. [QUICK_START.md](QUICK_START.md) - Basic setup
3. Marea API status: https://api.marea.ooo

## Emergency Fallback

If nothing works, use simulated data:

```bash
# In .env
MAREA_API_ENABLED=false

# Clear and restart
php artisan config:clear
```

Page will work with simulated data while you debug the API integration.


{{--
    Visitor statistics, counted on our own hub (hub.oggel-codelabs.nl) instead of
    Simple Analytics: no cookie, no third-party script, the IP address is never
    stored. One beacon per page view, also after a wire:navigate. Only the
    production hosts are counted by the hub, so local and test traffic is dropped
    there. ?notrack switches counting off on this device for good (?track switches
    it back on); automated browsers announce themselves through navigator.webdriver
    and are skipped. A conversion: window.bezoek_event('booking_done').
--}}
<script>
    (function () {
        if (window.bezoek_event) {
            return;
        }

        var optOut = false;
        try {
            var params = new URLSearchParams(window.location.search);
            if (params.has('notrack')) {
                localStorage.setItem('ocl_notrack', '1');
            }
            if (params.has('track')) {
                localStorage.removeItem('ocl_notrack');
            }
            optOut = localStorage.getItem('ocl_notrack') === '1';
        } catch (e) {}

        var vorige = null;

        function stuur(gebeurtenis) {
            if (optOut || navigator.webdriver || !navigator.sendBeacon) {
                return;
            }
            var query = new URLSearchParams(window.location.search);
            navigator.sendBeacon('https://hub.oggel-codelabs.nl/api/v1/b', JSON.stringify({
                h: window.location.hostname,
                p: window.location.pathname,
                // After a wire:navigate the referrer is still the first one.
                r: vorige === null ? document.referrer : '',
                b: query.get('ref') || query.get('utm_source') || '',
                w: window.innerWidth,
                e: gebeurtenis || ''
            }));
        }

        function tel() {
            if (window.location.pathname === vorige) {
                return;
            }
            stuur();
            vorige = window.location.pathname;
        }

        window.bezoek_event = function (naam) {
            stuur(naam);
        };

        tel();
        document.addEventListener('livewire:navigated', tel);
    })();
</script>

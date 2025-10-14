<div>
    <script>
        // Make weather data globally available
        @if($windData)
            window.windData = @json($windData);
            console.log('🌤️ Weather data loaded from Livewire:', window.windData);
            window.dispatchEvent(new CustomEvent('wind-updated', { detail: { windData: window.windData } }));
        @else
            console.warn('⚠️ No weather data available from Livewire');
            @if($error)
                console.error('Weather data error:', @json($error));
            @endif
        @endif
    </script>
</div>


<div>
    <script>
        // Make weather data globally available immediately
        @if($windData)
            // Set data immediately so it's available before Alpine initializes
            window.windData = @json($windData);
            console.log('🌤️ Weather data set:', window.windData);
            
            // Dispatch event for components that are already listening
            document.addEventListener('DOMContentLoaded', function() {
                console.log('🌤️ Dispatching wind-updated event');
                window.dispatchEvent(new CustomEvent('wind-updated', { detail: { windData: window.windData } }));
            });
            
            // Also dispatch immediately in case Alpine is already ready
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('wind-updated', { detail: { windData: window.windData } }));
            }, 100);
        @else
            console.warn('⚠️ No weather data available from Livewire');
            @if($error)
                console.error('Weather data error:', @json($error));
            @endif
        @endif
    </script>
</div>


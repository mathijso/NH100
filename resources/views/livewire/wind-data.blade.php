<div>
    <script>
        // Make wind data globally available
        @if($windData)
            window.windData = @json($windData);
            console.log('🌬️ Wind data loaded from Livewire:', window.windData);
            window.dispatchEvent(new CustomEvent('wind-updated', { detail: { windData: window.windData } }));
        @else
            console.warn('⚠️ No wind data available from Livewire');
            @if($error)
                console.error('Wind data error:', @json($error));
            @endif
        @endif
    </script>
</div>


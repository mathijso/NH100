<?php

namespace App\Livewire;

use App\Services\WindDataService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class WindData extends Component
{
    public $windData = null;
    public $loading = true;
    public $error = null;

    protected $windService;

    public function mount()
    {
        $this->loadWindData();
    }

    public function loadWindData()
    {
        $this->loading = true;
        $this->error = null;

        try {
            $windService = app(WindDataService::class);
            $this->windData = $windService->getCurrentWindData();

            if ($this->windData === null) {
                $this->error = 'Kon windgegevens niet ophalen. Controleer je API key.';
            }

            Log::info('Wind data loaded', [
                'data' => $this->windData,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading wind data', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error = 'Fout bij ophalen windgegevens.';
            $this->windData = null;
        }

        $this->loading = false;

        // Dispatch browser event to update Alpine components
        $this->dispatch('wind-loaded', [
            'windData' => $this->windData,
        ]);
    }

    public function refresh()
    {
        $this->loadWindData();
    }

    public function render()
    {
        return view('livewire.wind-data');
    }
}


<?php

namespace App\Livewire;

use App\Services\MareaApiService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class TideData extends Component
{
    public $tides = [];
    public $loading = true;
    public $error = null;
    public $source = 'simulated';
    public $useRealApi = true;
    
    protected $mareaService;

    public function mount()
    {
        $this->useRealApi = config('services.marea.enabled', false);
        $this->loadTideData();
    }

    public function loadTideData()
    {
        $this->loading = true;
        $this->error = null;

        try {
            if ($this->useRealApi) {
                $this->loadRealTideData();
            } else {
                $this->loadSimulatedTideData();
            }
        } catch (\Exception $e) {
            Log::error('Error loading tide data', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $this->error = 'Kon getijdendata niet laden. Gebruik gesimuleerde data.';
            $this->loadSimulatedTideData();
        }

        $this->loading = false;
        
        // Dispatch browser event to update Alpine components  
        $this->dispatch('tides-loaded', [
            'tides' => $this->tides,
            'source' => $this->source
        ]);
    }

    private function loadRealTideData()
    {
        $mareaService = app(MareaApiService::class);
        $data = $mareaService->getEgmondTides(35);

        if ($data && !empty($data['tides'])) {
            $this->tides = $data['tides'];
            $this->source = $data['source'];
            
            Log::info('Loaded real tide data', [
                'count' => count($this->tides),
                'source' => $this->source,
            ]);
        } else {
            throw new \Exception('No tide data received from API');
        }
    }

    private function loadSimulatedTideData()
    {
        $this->tides = $this->generateSimulatedTides();
        $this->source = 'simulated';
        
        Log::info('Loaded simulated tide data', [
            'count' => count($this->tides),
        ]);
    }

    private function generateSimulatedTides(): array
    {
        $tides = [];
        $now = now()->startOfDay();

        for ($day = 0; $day < 35; $day++) {
            $currentDate = $now->copy()->addDays($day);
            $timeShift = $day * 50; // minuten shift per dag

            // Eerste eb
            $tide1 = $currentDate->copy()->addHours(6)->addMinutes($timeShift % 60);
            $tides[] = [
                'time' => $tide1->toIso8601String(),
                'type' => 'Low',
                'height' => 0.3 + (rand(0, 20) / 100),
            ];

            // Tweede eb (ongeveer 12.5 uur later)
            $tide2 = $currentDate->copy()->addHours(18)->addMinutes(30 + ($timeShift % 60));
            $tides[] = [
                'time' => $tide2->toIso8601String(),
                'type' => 'Low',
                'height' => 0.3 + (rand(0, 20) / 100),
            ];

            // Eerste hoog water
            $high1 = $currentDate->copy()->addHours(0)->addMinutes($timeShift % 60);
            $tides[] = [
                'time' => $high1->toIso8601String(),
                'type' => 'High',
                'height' => 1.8 + (rand(0, 40) / 100),
            ];

            // Tweede hoog water
            $high2 = $currentDate->copy()->addHours(12)->addMinutes(30 + ($timeShift % 60));
            $tides[] = [
                'time' => $high2->toIso8601String(),
                'type' => 'High',
                'height' => 1.8 + (rand(0, 40) / 100),
            ];
        }

        // Sort by time
        usort($tides, function ($a, $b) {
            return strtotime($a['time']) - strtotime($b['time']);
        });

        return $tides;
    }

    public function refresh()
    {
        $this->loadTideData();
    }

    public function render()
    {
        return view('livewire.tide-data');
    }
}

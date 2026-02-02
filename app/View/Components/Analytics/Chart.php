<?php

namespace App\View\Components\Analytics;

use Illuminate\View\Component;

class Chart extends Component
{
    public string $id;
    public string $type;
    public string $endpoint;
    public int $height;
    public array $xAxis;
    public array $yAxis;
    public array $dataA;
    public array $dataB;

    public function __construct(
        string $id,
        string $type = 'bar',
        string $endpoint,
        int $height = 400,
        array $xAxis = [],
        array $yAxis = [],
        array $dataA = [],
        array $dataB = []
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->endpoint = $endpoint;
        $this->height = $height;
        $this->xAxis = $xAxis;
        $this->yAxis = $yAxis;
        $this->dataA = $dataA;
        $this->dataB = $dataB;
    }

    public function render()
    {
        return view('components.analytics.chart');
    }
}

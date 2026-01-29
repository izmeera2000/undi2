<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ReportChart extends Component
{
    /**
     * Create a new component instance.
     */

    public $title;
    public $url;
    public $chartId;

    public function __construct($title, $url, $chartId)
    {
        //

        $this->title = $title;
        $this->url = $url;
        $this->chartId = $chartId;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.report-chart');
    }
}

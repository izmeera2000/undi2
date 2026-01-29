<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class modern_stat_card extends Component
{

    public $col;
    public $title;
    public $icon;
    public $subtitle;
    public $number;
    public $change;
    public $changeClass;

    public function __construct(
        $col = 'col-12 col-md-4 col-xxl',
        $title = 'Title',
        $icon = 'bi bi-bag-check',
        $number = '0',
        $subtitle = 'Since last month',
        $change = '0%',
        $changeClass = 'positive'
    ) {
        $this->col = $col;
        $this->title = $title;
        $this->icon = $icon;
        $this->number = $number;
        $this->subtitle = $subtitle;
        $this->change = $change;
        $this->changeClass = $changeClass;
    }

    public function render(): View|Closure|string
    {
        return view('components.modern_stat_card');
    }


}

<?php

namespace App\View\Components\Sidebar;

use Illuminate\View\Component;

class NavHeading extends Component
{
    public $label;

    public function __construct($label)
    {
        $this->label = $label;
    }

    public function render()
    {
        return view('components.sidebar.nav-heading');
    }
}
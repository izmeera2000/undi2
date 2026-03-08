<?php

namespace App\View\Components\Sidebar;

use Illuminate\View\Component;

class NavLink extends Component
{
    public $route;
    public $icon;
    public $label;
    public $active;

    public function __construct($route, $label, $icon = null, $active = null)
    {
        $this->route = $route;
        $this->label = $label;
        $this->icon = $icon;

        // If $active not passed, check if current route matches the route or its wildcard
        $this->active = $active ?? request()->routeIs($route . '*');
    }

    public function render()
    {
        return view('components.sidebar.nav-link');
    }
}
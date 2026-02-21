<?php

namespace App\View\Components\Sidebar;

use Illuminate\View\Component;

class NavGroup extends Component
{
    public $pattern;
    public $icon;       // Optional CSS class
    public $label;
    public $open;
    public $iconSlot;   // Optional Blade slot for custom HTML

    /**
     * @param string $pattern Route pattern to match for active/open
     * @param string|null $icon Icon class (e.g., 'ph-light ph-gauge')
     * @param string $label Label text
     * @param string|null $iconSlot Optional custom icon slot (HTML)
     */
    public function __construct($pattern, $label, $icon = null, $iconSlot = null)
    {
        $this->pattern = $pattern;
        $this->label = $label;
        $this->icon = $icon;
        $this->iconSlot = $iconSlot;
        $this->open = request()->routeIs($pattern);
    }

    public function render()
    {
        return view('components.sidebar.nav-group');
    }
}
<?php

use Livewire\Component;

new class extends Component {
    public string $name;

    public function mount()
    {
        $this->name = ucfirst(auth()->user()->name);
    }
}; ?>

<div class="widget-banner-promo light h-100 shadow-sm">
    <div class="widget-banner-content">
        <p class="widget-banner-text">Good Day,</p>
        <h4 class="widget-banner-title">{{ $name }}</h4>
        <div class="welcome-date">
            <i class="bi bi-calendar3"></i>
            <span id="currentDate">--</span>

            <i class="bi bi-clock ms-3"></i>
            <span id="currentTime">--</span>
        </div>
    </div>

    <script>
        const updateDateTime = () => {
            const now = new Date();

            document.getElementById('currentDate').textContent = now.toLocaleDateString('en-MY', {
                weekday: 'long',
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });

            document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-MY', {
                hour12: true,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: 'Asia/Kuala_Lumpur'
            });
        };

        updateDateTime();
        const timer = setInterval(updateDateTime, 1000);

        // Clean up timer if component is removed
        // $cleanup(() => clearInterval(timer));
    </script>


</div>
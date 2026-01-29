<div class="{{ $col }}">
  <div class="card stat-card card-lift h-100">
    <div class="card-body">
      <div class="stat-card-header">
        <span class="stat-card-label">{{ $title }}</span>

        <div class="stat-card-icon primary">
          <i class="{{ $icon }}"></i>
        </div>
      </div>

      <div class="stat-card-value">{{ $number }}</div>

      @if($change)
        <div class="stat-card-change {{ $changeClass }}">
          <i class="bi bi-arrow-up"></i> {{ $change }}
          <span>{{ $subtitle }}</span>
        </div>
      @endif

    </div>
  </div>
</div>

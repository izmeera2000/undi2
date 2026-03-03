<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Http; // Or use your Models

new class extends Component {
    // Filters
    public $type = '';
    public $series = '';
    public $parlimen = '';
    public $dun = '';
    public $dm = '';

    // Data from Controller/Initial Load
    public $pilihanRayaTypes = [];
    public $pilihanRayaSeries = [];

    // PDF Status
    public $pdfExists = false;
    public $lastGenerated = null;
    public $pdfFiles = [];

    public function mount($pilihanRayaTypes, $pilihanRayaSeries)
    {
        $this->pilihanRayaTypes = $pilihanRayaTypes;
        $this->pilihanRayaSeries = $pilihanRayaSeries;
    }

    // Reset children when parents change
    public function updatedType()
    {
        $this->resetFilters();
    }
    public function updatedSeries()
    {
        $this->resetFilters();
    }
    public function updatedParlimen()
    {
        $this->reset(['dun', 'dm']);
    }
    public function updatedDun()
    {
        $this->reset('dm');
    }

    protected function resetFilters()
    {
        $this->reset(['parlimen', 'dun', 'dm']);
    }

    #[Computed]
    public function hierarchy()
    {
        if (!$this->type || !$this->series)
            return [];

        $cacheKey = "hierarchy-{$this->type}-{$this->series}";

        return cache()->remember($cacheKey, 3600, function () {
            // Try-catch to prevent the app from crashing if the URL is unreachable
            try {
                $url = route('pengundi.ajax.pru.hierarchy');

$response = Http::withCookies([
    'laravel_session' => request()->cookie('laravel_session'),
    // add other cookies if needed
], config('session.domain'))
->get($url, [
    'type' => $this->type,
    'series' => $this->series,
]);
                $data = $response->json();

                // --- LOGGING ---
                logger("Hierarchy API Call to: " . $url);
                logger("Response Data:", $data ?? ['error' => 'No data returned']);
                // ---------------

                return $data;
            } catch (\Exception $e) {
                logger("Hierarchy API Error: " . $e->getMessage());
                return [];
            }
        });
    }


    #[Computed]
    public function parlimenOptions()
    {
        return collect($this->hierarchy)->unique('parlimen_id')->values();
    }

    #[Computed]
    public function dunOptions()
    {
        if (!$this->parlimen)
            return [];
        return collect($this->hierarchy)
            ->where('parlimen_id', $this->parlimen)
            ->unique('kod_dun')
            ->values();
    }

    #[Computed]
    public function dmOptions()
    {
        if (!$this->dun)
            return [];
        return collect($this->hierarchy)
            ->where('kod_dun', $this->dun)
            ->unique('koddm')
            ->values();
    }

    #[Computed]
    public function tableData()
    {
        if (!$this->dm)
            return [];

        // Mimic your pengundi.list_data POST request
        return Http::asForm()->post(route('pengundi.list_data'), [
            'parlimen' => $this->parlimen,
            'dun' => $this->dun,
            'dm' => $this->dm,
            'type' => $this->type,
            'series' => $this->series,
        ])->json()['data'] ?? [];
    }

    public function generatePdf()
    {
        // Call your existing PDF generation logic
        $response = Http::post(route('pengundi.list_data_pdf'), [
            'parlimen' => $this->parlimen,
            'dun' => $this->dun,
            'dm' => $this->dm,
            'type' => $this->type,
            'series' => $this->series,
        ])->json();

        if ($response['success']) {
            $this->dispatch('notify', message: $response['message'], type: 'success');
            $this->checkPdfStatus();
        }
    }

    public function checkPdfStatus()
    {
        $res = Http::post(route('pengundi.list.check_pdf'), [
            'parlimen' => $this->parlimen,
            'dun' => $this->dun,
            'dm' => $this->dm,
            'type' => $this->type,
            'series' => $this->series,
        ])->json();

        $this->pdfExists = $res['exists'] ?? false;
        $this->lastGenerated = $res['last_modified'] ?? null;
        $this->pdfFiles = $res['files'] ?? [];
    }
}; ?>


<div>
    <div class="card">
        <div class="card-body">
            {{-- Filter Row 1 --}}
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Pilihan Raya</label>
                    <select wire:model.live="type" class="form-select">
                        <option value="">-- Pilih Jenis --</option>
                        @foreach($pilihanRayaTypes as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Series Pilihan Raya</label>
                    <select wire:model.live="series" class="form-select">
                        <option value="">-- Pilih Series --</option>
                        @foreach($pilihanRayaSeries as $s)
                            <option value="{{ $s }}">{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Filter Row 2 --}}
            <div class="row mb-4 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Parlimen</label>
                    <select wire:model.live="parlimen" class="form-select" @disabled(!$this->type || !$this->series)>
                        <option value="">-- Pilih Parlimen --</option>
                        @foreach($this->parlimenOptions as $p)
                            <option value="{{ $p['parlimen_id'] }}">{{ $p['namapar'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">DUN</label>
                    <select wire:model.live="dun" class="form-select" @disabled(!$this->parlimen)>
                        <option value="">-- Pilih DUN --</option>
                        @foreach($this->dunOptions as $d)
                            <option value="{{ $d['kod_dun'] }}">{{ $d['namadun'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">DM</label>
                    <select wire:model.live="dm" class="form-select" @disabled(!$this->dun)>
                        <option value="">-- Pilih DM --</option>
                        @foreach($this->dmOptions as $dmOpt)
                            <option value="{{ $dmOpt['koddm'] }}">{{ $dmOpt['namadm'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    @if($this->dm)
                        <div class="btn-group">
                            <button wire:click="generatePdf" wire:loading.attr="disabled" class="btn btn-primary">
                                <span wire:loading wire:target="generatePdf"
                                    class="spinner-border spinner-border-sm"></span>
                                Generate PDF
                            </button>
                            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                                data-bs-toggle="dropdown"></button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" wire:click.prevent="checkPdfStatus"
                                        data-bs-toggle="modal" data-bs-target="#pdfModal">View Files</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><span
                                        class="dropdown-item-text text-muted small">{{ $lastGenerated ?? 'No PDF generated' }}</span>
                                </li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Reactive Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2">Lokaliti</th>
                            <th colspan="7" class="text-center">Saluran</th>
                            <th rowspan="2">Total</th>
                        </tr>
                        <tr>
                            @foreach(range(1, 7) as $i) <th>{{ $i }}</th> @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->tableData as $row)
                            <tr>
                                <td>{{ $row['nama_lokaliti'] }}</td>
                                @foreach(range(1, 7) as $i)
                                    <td>
                                        @php $key = "saluran_$i";
                                        $link = "link_$key"; @endphp
                                        @if(isset($row[$link]))
                                            <a href="{{ $row[$link] }}">{{ $row[$key] ?? 0 }}</a>
                                        @else
                                            {{ $row[$key] ?? 0 }}
                                        @endif
                                    </td>
                                @endforeach
                                <td class="fw-bold">{{ $row['total'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">Sila pilih semua filter untuk lihat data
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(!empty($this->tableData))
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="8" class="text-end">GRAND TOTAL:</td>
                                <td>{{ collect($this->tableData)->sum('total') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- PDF Modal --}}
    <div wire:ignore.self class="modal fade" id="pdfModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Available PDFs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        @forelse($pdfFiles as $file)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $file['name'] }}</strong><br>
                                    <small class="text-muted">{{ $file['last_modified'] }}</small>
                                </div>
                                <div class="btn-group">
                                    <a href="{{ $file['url'] }}" target="_blank" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ $file['url'] }}" download class="btn btn-sm btn-success">Download</a>
                                </div>
                            </div>
                        @empty
                            <p class="text-center">No files found.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
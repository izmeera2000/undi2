@extends('layouts.app')

@section('title', 'Pengundi Excel Import')

@section('breadcrumb')
    @php
        $crumbs = [
            ['label' => 'Pengundi', 'url' => route('pengundi.analysis')],
            ['label' => 'Excel Import', 'url' => route('pengundi.pasteimport')],
        ];
    @endphp
@endsection

@section('content')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>

    <div class="card">
        <div class="card-body">

            <div id="successMsg" class="alert alert-success d-none"></div>
            <div id="errorMsg" class="alert alert-danger d-none"></div>

            <form id="pasteForm" method="POST">
                @csrf

                {{-- ===================== DROPDOWNS ===================== --}}
                <div class="row mb-4">

                    <div class="col-md-3">
                        <label class="form-label">Parlimen</label>
                        <select id="parlimenSelect" class="form-select" required>
                            <option value="">-- Pilih Parlimen --</option>
                            @foreach($parlimens as $par)
                                <option value="{{ $par->id }}">
                                    {{ $par->namapar }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">DUN</label>
                        <select id="dunSelect" class="form-select" disabled required>
                            <option value="">-- Pilih DUN --</option>
                            @foreach($duns as $dun)
                                <option value="{{ $dun->kod_dun }}" data-parent="{{ $dun->parlimen_id }}">
                                    {{ $dun->namadun }} ({{ $dun->kod_dun }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">DM</label>
                        <select id="dmSelect" class="form-select" disabled required>
                            <option value="">-- Pilih DM --</option>
                            @foreach($dms as $dm)
                                <option value="{{ $dm->koddm }}" data-parent="{{ $dm->dun->kod_dun  }}">{{ $dm->koddm }}</option>

                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Lokaliti</label>
                        <select id="lokalitiSelect" name="kod_lokaliti" class="form-select" disabled required>
                            <option value="">-- Pilih Lokaliti --</option>
                            @foreach($lokalitis as $loc)
                                <option value="{{ $loc->kod_lokaliti }}" data-parent="{{ $loc->dm->koddm }}">{{ $loc->kod_lokaliti }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                {{-- ===================== YEAR ===================== --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Tahun Undian</label>
                        <input type="number" name="tarikh_undian" class="form-control" value="{{ now()->year }}" required>
                    </div>
                </div>

                {{-- ===================== HANDSONTABLE ===================== --}}
                <div class="mb-3">
                    <label class="form-label">Paste Excel Data Below</label>
                    <div id="excelTable"></div>
                    <input type="hidden" name="paste_data" id="pasteDataHidden">
                </div>

                <button type="submit" class="btn btn-success" id="submitBtn">
                    Import Data
                </button>

            </form>

            <div id="loading" class="mt-3 d-none">
                <div class="spinner-border spinner-border-sm"></div>
                Processing...
            </div>

        </div>
    </div>

    {{-- ===================== SCRIPT ===================== --}}
    <script>
        $(document).ready(function() {

            // ==========================================================
            // CLEAN CASCADE FUNCTION
            // ==========================================================

            function setupCascade(parent, child) {
                parent.addEventListener('change', function () {
                    const value = this.value;
                    // console.log(`Parent ${parent.id} changed to:`, value);

                    // Reset child
                    child.value = "";
                    child.disabled = true;

                    Array.from(child.options).forEach(opt => {
                        if (!opt.value) return; // skip placeholder
                        opt.hidden = opt.dataset.parent != value;
                        // if (!opt.hidden) console.log(`Showing option:`, opt.value, 'for parent', value);
                    });

                    if (value) child.disabled = false;

                    // Trigger next level reset
                    child.dispatchEvent(new Event('change'));
                });
            }



            const par = document.getElementById('parlimenSelect');
            const dun = document.getElementById('dunSelect');
            const dm = document.getElementById('dmSelect');
            const lokaliti = document.getElementById('lokalitiSelect');

            setupCascade(par, dun);
            setupCascade(dun, dm);
            setupCascade(dm, lokaliti);


            // ==========================================================
            // HANDSONTABLE
            // ==========================================================

            const hot = new Handsontable(document.getElementById('excelTable'), {
                data: [],
                rowHeaders: true,
                colHeaders: [
                    'NOKP BARU',
                    'NOKP LAMA',
                    'NAMA',
                    'JANTINA',
                    'BANGSA',
                    'UMUR',
                    'TAHUN LAHIR',
                    'ALAMAT SPR',
                    'POSKOD',
                    'BANDAR',
                    'NEGERI'
                ],
                columns: [
                    { type: 'text' },
                    { type: 'text' },
                    { type: 'text' },
                    { type: 'dropdown', source: ['L', 'P'], allowInvalid: false },
                    { type: 'text' },
                    { type: 'numeric' },
                    { type: 'numeric' },
                    { type: 'text' },
                    { type: 'text' },
                    { type: 'text' },
                    { type: 'text' }
                ],
                minRows: 20,
                minSpareRows: 1,
                stretchH: 'all',
                height: 500,
                licenseKey: 'non-commercial-and-evaluation',
                contextMenu: true,
            });


            // ==========================================================
            // FORM SUBMIT
            // ==========================================================

            const form = document.getElementById('pasteForm');
            const submitBtn = document.getElementById('submitBtn');
            const loading = document.getElementById('loading');
            const successMsg = document.getElementById('successMsg');
            const errorMsg = document.getElementById('errorMsg');

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                successMsg.classList.add('d-none');
                errorMsg.classList.add('d-none');

                if (!lokaliti.value) {
                    alert('Please select Lokaliti.');
                    return;
                }

                const rows = hot.getData().filter(row => row[0]);

                if (rows.length === 0) {
                    alert('No data to import.');
                    return;
                }

                document.getElementById('pasteDataHidden').value = JSON.stringify(rows);

                submitBtn.disabled = true;
                loading.classList.remove('d-none');
                const formData = new FormData(form);
                // console.log(formData);
                // Assuming `hot` is already initialized
                console.log('Before loading data:', hot.getData()); // Log current data in the Handsontable instance

 

                // Optionally log the entire `hot` Handsontable instance
                console.log('Handsontable instance:', hot);

                fetch("{{ route('pengundi.pasteimport.submit') }}", {
                    method: 'POST',
                    body: formData,
                })
                    .then(res => res.json())
                    .then(data => {

                        if (data.success) {
                            successMsg.innerText = data.success;
                            successMsg.classList.remove('d-none');
                hot.loadData([]);
                        } else {
                            throw data;
                        }

                    })
                    .catch(err => {
                        errorMsg.innerText = err.error || 'Import failed';
                        errorMsg.classList.remove('d-none');
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        loading.classList.add('d-none');
                    });

            });

        });
    </script>

@endsection
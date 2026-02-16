@extends('layouts.app')

@section('content')
<div class="container mx-auto py-4">
    <h2 class="text-xl font-semibold mb-4">Select Parlimen or DUN</h2>

    <!-- Dropdown form to select type and code -->
    <div class="mb-4">
        <label for="type" class="block text-sm font-medium">Select Type</label>
        <select id="type" name="type" class="block w-full mt-1">
            <option value="parlimen">Parlimen</option>
            <option value="dun">DUN</option>
        </select>
    </div>

    <div class="mb-4">
        <label for="code" class="block text-sm font-medium">Select Parlimen or DUN</label>
        <select id="code" name="code" class="block w-full mt-1">
            <!-- Default option, will be updated based on the 'type' -->
            <option value="">Select a Parlimen/DUN</option>
        </select>
    </div>

    <!-- Button to trigger the fetch request -->
    <button id="fetchGeoJson" class="btn btn-primary  text-white py-2 px-4 rounded">Fetch GeoJSON</button>
</div>

<script>
    // Fetch the dropdown options based on selected type
    document.getElementById('type').addEventListener('change', function() {
        const type = this.value;
        const codeSelect = document.getElementById('code');
        
        let options = [];
        
        if (type === 'parlimen') {
            options = @json($parlimen); // Passing array from controller
        } else if (type === 'dun') {
            options = @json($dun); // Passing array from controller
        }

        // Clear current options
        codeSelect.innerHTML = '<option value="">Select a Parlimen/DUN</option>';

        // Add new options
        options.forEach(option => {
            const optionElement = document.createElement('option');
            optionElement.value = option;
            optionElement.textContent = option;
            codeSelect.appendChild(optionElement);
        });
    });

    // Trigger change to load initial options
    document.getElementById('type').dispatchEvent(new Event('change'));

    // Event listener for the "Fetch GeoJSON" button
    document.getElementById('fetchGeoJson').addEventListener('click', function() {
        const type = document.getElementById('type').value;
        const code = document.getElementById('code').value;

        // Check if both fields are selected
        if (!type || !code) {
            alert('Please select both Type and Parlimen/DUN');
            return;
        }

        // Prepare the data to send via fetch
        const data = {
            type: type,
            code: code
        };

        // Make the fetch request to send the data
        fetch("{{ route('map.fetch') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Success:', data); // Log the response from the server
            alert('GeoJSON fetched successfully! Check console for details.');
        })
        .catch((error) => {
            console.error('Error:', error); // Log any error
            alert('Failed to fetch GeoJSON.');
        });
    });
</script>
@endsection

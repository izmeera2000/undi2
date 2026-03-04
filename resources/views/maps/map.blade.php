@extends('layouts.app')

@section('content')
    <div class="container mx-auto py-4">
        <h2 class="text-xl font-semibold mb-4">OpenStreetMap with Leaflet - All Maps</h2>

        <!-- Map Container -->
        <div id="map" style="height: 500px;"></div>
    </div>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize the map centered on a default location (Kelantan)
            var map = L.map('map').setView([6.14882, 102.1187], 10); // Adjusted zoom level for Kelantan

            // Add OpenStreetMap tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Function to generate a random color
            function getRandomColor() {
                var letters = '0123456789ABCDEF';
                var color = '#';
                for (var i = 0; i < 6; i++) {
                    color += letters[Math.floor(Math.random() * 16)];
                }
                return color;
            }

            // Loop through all maps (DUNs) and add GeoJSON data to the map
            @foreach($maps as $map)
                @php
                    $geojson = json_encode($map->geojson); // Encode GeoJSON data to JavaScript format
                @endphp

                // Generate a random color for each DUN
                var randomColor = getRandomColor();

                // Define GeoJSON style for each DUN with random color
                var geoJsonStyle = {
                    color: randomColor,      // Border color
                    weight: 2,               // Border width
                    opacity: 1,              // Border opacity
                    fillColor: randomColor,  // Fill color
                    fillOpacity: 0.5         // Fill opacity
                };

                // Add GeoJSON layer with styling
                var geoJsonLayer = L.geoJSON({!! $geojson !!}, {
                    style: geoJsonStyle,
                    onEachFeature: function (feature, layer) {
                        // Add popup with information about the feature (DUN)
                        layer.bindPopup("<strong>State:</strong> " + feature.properties.state + "<br><strong>Parlimen:</strong> " + feature.properties.parlimen);
                    }
                }).addTo(map);

                // Fit map bounds to include the GeoJSON data (zoom to feature bounds)
                map.fitBounds(geoJsonLayer.getBounds());
            @endforeach
        });


    </script>
@endsection
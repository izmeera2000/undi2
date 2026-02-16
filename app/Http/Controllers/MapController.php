<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Map;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;





class MapController extends Controller
{
    public function showPage()
    {
        // Data for dropdown options (you can adjust this to get them from your database)
        $parlimen = [
            'P.019 Tumpat',
            'P.020 Pengkalan Chepa',
            'P.021 Kota Bharu',
            'P.022 Pasir Mas',
            'P.023 Rantau Panjang',
            'P.024 Kubang Kerian',
            'P.025 Bachok',
            'P.026 Ketereh',
            'P.027 Tanah Merah',
            'P.028 Pasir Puteh',
            'P.029 Machang',
            'P.030 Jeli',
            'P.031 Kuala Krai',
            'P.032 Gua Musang'
        ];

        $dun = [
            'N.01 Pengkalan Kubor',
            'N.02 Kelaboran',
            'N.03 Pasir Pekan',
            'N.04 Wakaf Bharu',
            'N.05 Kijang',
            'N.06 Chempaka',
            'N.07 Panchor',
            'N.08 Tanjong Mas',
            'N.09 Kota Lama',
            'N.10 Bunut Payong',
            'N.11 Tendong',
            'N.12 Pengkalan Pasir',
            'N.13 Meranti',
            'N.14 Chetok',
            'N.15 Gual Periok',
            'N.16 Apam Putra',
            'N.17 Salor',
            'N.18 Pasir Tumboh',
            'N.19 Demit',
            'N.20 Tawang',
            'N.21 Pantai Irama',
            'N.22 Jelawat',
            'N.23 Melor',
            'N.24 Kadok',
            'N.25 Kok Lanas',
            'N.26 Bukit Panau',
            'N.27 Gual Ipoh',
            'N.28 Kemahang',
            'N.29 Selising',
            'N.30 Limbongan',
            'N.31 Semerak',
            'N.32 Gaal',
            'N.33 Pulai Chondong',
            'N.34 Temangan',
            'N.35 Kemuning',
            'N.36 Bukit Bunga',
            'N.37 Air Lanas',
            'N.38 Kuala Balah',
            'N.39 Mengkebang',
            'N.40 Guchil',
            'N.41 Manek Urai',
            'N.42 Dabong',
            'N.43 Nenggiri',
            'N.44 Paloh',
            'N.45 Galas',
        ];

        return view('maps.fetchmap', compact('parlimen', 'dun'));
    }

    public function showPage2()
    {

        $maps = Map::all(); // Assuming this fetches all maps from your database

        // Loop through each map and decode the geojson column
        foreach ($maps as $map) {
            // Decode the GeoJSON string to an array (for storing in the JavaScript view)
            $map->geojson = json_decode($map->geojson, true);
        }

        return view('maps.map', compact('maps'));  // Pass the maps to the view

    }
    public function fetchAndStoreGeoJson(Request $request)
    {
        $selectedType = $request->input('type'); // 'parlimen' or 'dun'
        $selectedCode = $request->input('code'); // Example: 'P.021 Kota Bharu' or 'N.11 Tendong'

        // Determine the URL based on the type (Parlimen or DUN)
        $url = '';
        $encodedCode = urlencode($selectedCode); // Path encoding, where spaces become %20
        $encodedQueryCode = str_replace('%20', '+', $encodedCode); // Query encoding, replace %20 with + for query

        if ($selectedType === 'parlimen') {
            // For Parlimen, path needs %20 for spaces, but query should use + for spaces
            $url = "https://open.dosm.gov.my/_next/data/7D3Ymi8cKyR5nRijAGt8M/ms-MY/dashboard/kawasanku/Kelantan/parlimen/{$selectedCode}.json?state=Kelantan&id={$encodedQueryCode}";
        } elseif ($selectedType === 'dun') {
            // For DUN, path needs %20 for spaces, but query should use + for spaces
            $url = "https://open.dosm.gov.my/_next/data/7D3Ymi8cKyR5nRijAGt8M/ms-MY/dashboard/kawasanku/Kelantan/dun/{$selectedCode}.json?state=Kelantan&id={$encodedQueryCode}";
        }

        // Debugging: Log the URL to check the request
        // Log::debug("Fetching GeoJSON from URL: " . $url);


        try {
            // Fetch the GeoJSON data from the URL
            $response = Http::get($url);

            // Log the response status and body for debugging
            Log::debug("Response Status: " . $response->status());
            Log::debug("Response Body: " . $response->body());

            if ($response->successful()) {
                // Parse the response to get the geojson structure
                $data = $response->json();
                $geojson = $data['pageProps']['geojson'];  // This is the geojson you need

                // Now save the data
                $mapData = new Map();
                $mapData->type = $selectedType;
                $mapData->code = $selectedCode;
                $mapData->geojson = json_encode($geojson); // Store GeoJSON as string
                $mapData->date = now();  // Optional: Store current timestamp
                $mapData->save();

                return response()->json([
                    'status' => 'success',
                    'geojson' => $geojson // Return parsed JSON for frontend use
                ]);
            } else {
                return response()->json(['status' => 'error', 'message' => $selectedCode], 500);
            }
        } catch (\Exception $e) {
            // Catch any exceptions and log them
            Log::error("Error fetching GeoJSON: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }
}

@extends('layouts.app')

@section('title', 'Undi - Dashboard')


@section('content')
  <!-- Welcome & Stats Row -->
  <div class="row g-4 mb-4">


    <form action="{{ route('pengundi.import.paste') }}" method="POST">
    @csrf
    <label for="data">Paste Excel Data (from clipboard):</label>
    <textarea name="data" id="data" rows="10" cols="50" placeholder="Paste copied data from Excel here..." required></textarea>

    <button type="submit">Import Data</button>
</form>
    
{{-- <form action="/pengundi/import" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" required>
    <button type="submit">Upload Excel</button>
</form>

<a href="/pengundi/transfer"
   onclick="return confirm('Pindahkan data ke jadual rasmi?')"
   class="btn btn-primary">
   Transfer Pengundi
</a>


<form action="{{ route('members.upload') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" required>
    <button type="submit">Upload Excel</button>
</form>


<a href="/members/transfer"
   onclick="return confirm('Pindahkan data ke jadual rasmi?')"
   class="btn btn-primary">
   Transfer Member
</a>

 --}}

{{-- 

<form action="{{ route('mail.send') }}" method="POST">
    @csrf
    <input type="email" name="to" placeholder="Recipient Email">
    <input type="text" name="subject" placeholder="Subject">
    <textarea name="body" placeholder="Email Body"></textarea>
    <button type="submit">Send Email</button>
</form>
 --}}



  </div>
 

@endsection
 
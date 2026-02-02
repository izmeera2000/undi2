@extends('layouts.app')

@section('title', 'Undi - Dashboard')


@section('content')
  <!-- Welcome & Stats Row -->
  <div class="row g-4 mb-4">
    
<form action="/pengundi/import" method="POST" enctype="multipart/form-data">
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



  </div>
 

@endsection
 
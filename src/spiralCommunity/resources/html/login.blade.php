@extends('template.base')

@section('content')
<a href="{{ url('auth.entra') }}" class="btn btn-primary">
    Microsoft Entra IDでログイン
</a>
@endsection
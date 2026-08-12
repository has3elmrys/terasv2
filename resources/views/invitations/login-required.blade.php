@extends('invitations.layout')

@section('content')
    <h1>Join {{ $invite->team->name }}</h1>

    <p>Akun dengan email <strong>{{ $invite->email }}</strong> udah ada. Login dulu, terus buka link ini lagi.</p>

    <a href="/login" class="button-link">Login</a>
@endsection

@extends('invitations.layout')

@section('content')
    <h1>Join {{ $invite->team->name }}</h1>

    @if ($errors->any())
        <div class="errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('invitations.store', $invite->token) }}">
        @csrf

        <div class="field">
            <label>Email</label>
            <input type="text" value="{{ $invite->email }}" disabled>
        </div>

        <div class="field">
            <label>Nama</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </div>

        <div class="field">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <div class="field">
            <label>Konfirmasi Password</label>
            <input type="password" name="password_confirmation" required>
        </div>

        <button type="submit">Gabung</button>
    </form>
@endsection

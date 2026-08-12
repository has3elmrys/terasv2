<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invite</title>
    @filamentStyles
</head>
<body class="fi-body min-h-screen bg-gray-50 dark:bg-gray-950 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <x-filament::section>
            <x-slot name="heading">
                Join {{ $invite->team->name }}
            </x-slot>

            @if ($errors->any())
                <ul class="text-danger-600 text-sm mb-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('invitations.store', $invite->token) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="text-sm font-medium">Email</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" value="{{ $invite->email }}" disabled />
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label class="text-sm font-medium">Nama</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="text" name="name" value="{{ old('name') }}" required />
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label class="text-sm font-medium">Password</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="password" name="password" required />
                    </x-filament::input.wrapper>
                </div>

                <div>
                    <label class="text-sm font-medium">Konfirmasi Password</label>
                    <x-filament::input.wrapper>
                        <x-filament::input type="password" name="password_confirmation" required />
                    </x-filament::input.wrapper>
                </div>

                <x-filament::button type="submit" class="w-full">
                    Gabung
                </x-filament::button>
            </form>
        </x-filament::section>
    </div>

    @filamentScripts
</body>
</html>

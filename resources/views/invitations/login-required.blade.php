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

            <p class="text-sm mb-4">
                Akun dengan email <strong>{{ $invite->email }}</strong> udah ada. Login dulu, terus buka link ini lagi.
            </p>

            <x-filament::link href="/login">
                Login
            </x-filament::link>
        </x-filament::section>
    </div>

    @filamentScripts
</body>
</html>

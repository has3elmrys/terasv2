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
            <p class="text-sm">{{ $message }}</p>
        </x-filament::section>
    </div>

    @filamentScripts
</body>
</html>

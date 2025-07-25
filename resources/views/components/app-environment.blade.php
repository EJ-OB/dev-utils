@php
    $env = Config::string('app.env');
    $bg = match ($env) {
        'local' => 'bg-success-500',
        'develop' => 'bg-info-500',
        'staging' => 'bg-warning-500',
        'production' => 'bg-danger-500',
        default => 'bg-primary-500',
    };
@endphp

<span class="text-white text-xs font-semibold rounded-full px-2 py-1 {{ $bg }}">
    {{ ucfirst(Config::string('app.env')) }}
</span>

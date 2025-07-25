import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Clusters/ProfileSettings/**/*.php',
        './resources/views/filament/clusters/profile-settings/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './resources/views/**/*.blade.php',
    ],
}

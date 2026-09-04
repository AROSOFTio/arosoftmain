<?php

return [
    'version' => env('AROMOTION_VERSION', '0.10.0-beta'),
    'channel' => env('AROMOTION_CHANNEL', 'beta'),
    'binary_path' => env(
        'AROMOTION_BINARY_PATH',
        storage_path('app/private/aromotion/AROMOTION-Windows-x64.exe')
    ),
    'download_name' => env('AROMOTION_DOWNLOAD_NAME', 'AROMOTION-Windows-x64.exe'),
    'release_notes' => [
        'AROMOTION Cloud account activation with Windows-protected device tokens.',
        'Connected-device heartbeat and project metadata synchronization with AROSOFT Labs.',
        'Queued effects rendering so recordings are never silently dropped.',
        'Lossless/near-lossless capture with professional cursor, zoom, 3D, captions, audio and webcam controls.',
        'Seven-day offline grace after a successful cloud validation.',
    ],
    'minimum_supported_version' => env('AROMOTION_MINIMUM_VERSION', '0.10.0-beta'),
    'support_email' => env('AROMOTION_SUPPORT_EMAIL', 'support@arosoftlabs.com'),
];

<?php

return [
    'version' => env('AROMOTION_VERSION', '0.9.8'),
    'channel' => env('AROMOTION_CHANNEL', 'beta'),
    'binary_path' => env(
        'AROMOTION_BINARY_PATH',
        storage_path('app/private/aromotion/AROMOTION-Windows-x64.exe')
    ),
    'download_name' => env('AROMOTION_DOWNLOAD_NAME', 'AROMOTION-Windows-x64.exe'),
    'release_notes' => [
        'Queued effects rendering so recordings are never silently dropped.',
        'Lossless/near-lossless capture with professional motion rendering.',
        'Smart cursor, click effects, zoom, 3D motion, captions, audio and webcam controls.',
        'Cloud account, device activation and project metadata sync.',
    ],
    'minimum_supported_version' => env('AROMOTION_MINIMUM_VERSION', '0.9.8'),
    'support_email' => env('AROMOTION_SUPPORT_EMAIL', 'support@arosoftlabs.com'),
];

<?php

return [
    'default' => 'app',
    'projects' => [
        'app' => [
            'credentials' => storage_path('app/' . env('FIREBASE_CREDENTIALS', 'game-day-valet-gdv-firebase-adminsdk-fbsvc-8dbc35550f.json')),
            'project_id' => env('FIREBASE_PROJECT_ID', 'gdv-project-id'),
        ],
    ],
];
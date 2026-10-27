<?php

return [
    'assignment_upload' => [
        // Keep this below the PHP request limits configured for local and shared hosting.
        'max_file_kb' => min(max((int) env('EDUKA_ASSIGNMENT_MAX_FILE_KB', 5120), 1), 5120),
        'default_file_types' => ['pdf'],
        'allowed_file_types' => ['pdf', 'zip', 'docx'],
    ],
];

<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'user.passwordResetTokenExpire' => 3600,
    'user.passwordMinLength' => 8,
    // Frequency for TaskMaintenance task in seconds
    'taskMaintenanceTaskFrequency' => 3600,
    // Finished task deletion age limit
    'finishedTaskDeletionAgeLimit' => 604800, // 7 * 24 * 3600
];

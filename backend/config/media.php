<?php

return [
    'event_image_disk' => env('EVENT_IMAGE_DISK', 'cloudinary'),
    'event_image_directory' => env('EVENT_IMAGE_DIRECTORY', 'services'),
    'profile_image_disk' => env('PROFILE_IMAGE_DISK', 'cloudinary'),
    'profile_image_directory' => env('PROFILE_IMAGE_DIRECTORY', 'profiles'),
    'chat_image_disk' => env('CHAT_IMAGE_DISK', 'cloudinary'),
    'chat_image_directory' => env('CHAT_IMAGE_DIRECTORY', 'chat-images'),
];

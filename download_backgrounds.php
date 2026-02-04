<?php

// Create images directory if it doesn't exist
$image_dir = __DIR__ . '/assets/images';
if (!file_exists($image_dir)) {
    mkdir($image_dir, true, 0777);
}

// Define image URLs and their local filenames
$images = [
    'purple-blue-gradient.jpg' => 'https://images.unsplash.com/photo-1557683311-eac922347aa1',
    'geometric-pattern.jpg' => 'https://images.unsplash.com/photo-1553356084-58ef4a67b2a7',
    'tech-abstract.jpg' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa',
    'blue-yellow-gradient.jpg' => 'https://images.unsplash.com/photo-1557683316-973673baf926',
    'dark-abstract.jpg' => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b'
];

// Download images
foreach ($images as $filename => $url) {
    $filepath = $image_dir . '/' . $filename;
    if (!file_exists($filepath)) {
        echo "Downloading {$filename}...\n";
        $image_content = file_get_contents($url);
        if ($image_content !== false) {
            file_put_contents($filepath, $image_content);
            echo "Successfully downloaded {$filename}\n";
        } else {
            echo "Failed to download {$filename}\n";
        }
    } else {
        echo "{$filename} already exists\n";
    }
}

echo "\nAll background images have been processed!\n";
echo "Please check the assets/images directory to ensure all images were downloaded correctly.\n";
?> 
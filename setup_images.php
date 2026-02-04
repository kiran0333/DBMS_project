<?php
// Create directories if they don't exist
$image_dir = __DIR__ . '/assets/images';
if (!file_exists($image_dir)) {
    mkdir($image_dir, 0777, true);
}

// List of required images and their descriptions
$required_images = [
    'money-transfer-bg.jpg' => [
        'description' => 'Hero section background image',
        'recommended_size' => '1920x1080',
        'suggested_content' => 'A modern digital payment or money transfer visualization with blue tones'
    ],
    'about-us.jpg' => [
        'description' => 'About section image',
        'recommended_size' => '800x600',
        'suggested_content' => 'Professional team or modern office environment'
    ]
];

// Create HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Setup Instructions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4">Required Images Setup</h1>
        <div class="alert alert-info">
            <h4 class="alert-heading">Instructions</h4>
            <p>To complete the website setup, you need to add the following images to the <code>assets/images/</code> directory:</p>
        </div>

        <div class="row">
            <?php foreach ($required_images as $filename => $details): ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($filename); ?></h5>
                        <p class="card-text">
                            <strong>Description:</strong> <?php echo htmlspecialchars($details['description']); ?><br>
                            <strong>Recommended Size:</strong> <?php echo htmlspecialchars($details['recommended_size']); ?><br>
                            <strong>Suggested Content:</strong> <?php echo htmlspecialchars($details['suggested_content']); ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="alert alert-warning mt-4">
            <h4 class="alert-heading">Image Requirements</h4>
            <ul>
                <li>Use high-quality, professional images</li>
                <li>Ensure you have the right to use the images</li>
                <li>Optimize images for web use (compress without significant quality loss)</li>
                <li>Maintain the recommended aspect ratios</li>
            </ul>
        </div>

        <div class="alert alert-success mt-4">
            <h4 class="alert-heading">Recommended Sources</h4>
            <p>You can find suitable images from these sources:</p>
            <ul>
                <li>Unsplash.com (Free, high-quality photos)</li>
                <li>Pexels.com (Free stock photos)</li>
                <li>Shutterstock.com (Premium stock photos)</li>
                <li>iStock.com (Premium stock photos)</li>
            </ul>
        </div>
    </div>
</body>
</html> 
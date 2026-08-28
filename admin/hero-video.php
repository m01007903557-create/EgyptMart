<?php
session_start();
require_once __DIR__ . '/../common.php';

if (!check_admin_login()) {
    header('Location: index.php');
    exit;
}

$message = '';
$messageType = '';
$uploadDir = realpath(__DIR__ . '/../uploads');

if ($uploadDir === false) {
    $uploadPath = __DIR__ . '/../uploads';
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    $uploadDir = realpath($uploadPath);
}

$videoFile = $uploadDir . '/home-hero-video.mp4';
$videoUrl = '../uploads/home-hero-video.mp4?v=' . time();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['home_hero_video']) || $_FILES['home_hero_video']['error'] !== UPLOAD_ERR_OK) {
        $message = 'Please choose a valid MP4 video file.';
        $messageType = 'error';
    } else {
        $file = $_FILES['home_hero_video'];
        $extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        $maxSize = 100 * 1024 * 1024;

        if ($extension !== 'mp4') {
            $message = 'Only MP4 video files are allowed.';
            $messageType = 'error';
        } elseif ((int)$file['size'] > $maxSize) {
            $message = 'Video size must be less than 100 MB.';
            $messageType = 'error';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
            if ($finfo) {
                finfo_close($finfo);
            }

            if ($mime !== 'video/mp4' && $mime !== 'application/octet-stream') {
                $message = 'Please upload a valid MP4 video.';
                $messageType = 'error';
            } elseif (move_uploaded_file($file['tmp_name'], $videoFile)) {
                chmod($videoFile, 0644);
                $message = 'Hero video updated successfully.';
                $messageType = 'success';
                $videoUrl = '../uploads/home-hero-video.mp4?v=' . time();
            } else {
                $message = 'Video could not be saved. Please check upload folder permission.';
                $messageType = 'error';
            }
        }
    }
}

$hasVideo = is_file($videoFile);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Homepage Hero Video</title>
    <style>
        body { background: #eef2f5; color: #1f2933; font-family: Arial, sans-serif; margin: 0; padding: 24px; }
        .wrap { background: #fff; border: 1px solid #d9e2ec; border-radius: 6px; box-shadow: 0 8px 24px rgba(15, 23, 42, .08); margin: 0 auto; max-width: 820px; padding: 24px; }
        h1 { font-size: 24px; margin: 0 0 18px; }
        .notice { border-radius: 4px; font-weight: 700; margin-bottom: 18px; padding: 12px 14px; }
        .notice.success { background: #e7f7ed; color: #137333; }
        .notice.error { background: #fdecea; color: #b42318; }
        label { display: block; font-weight: 700; margin-bottom: 8px; }
        input[type="file"] { border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; display: block; margin-bottom: 14px; padding: 10px; width: 100%; }
        button { background: #063548; border: 0; border-radius: 4px; color: #fff; cursor: pointer; font-size: 15px; font-weight: 700; padding: 11px 18px; }
        .hint { color: #667085; font-size: 13px; line-height: 1.5; margin: 8px 0 18px; }
        video { background: #111827; border-radius: 6px; display: block; margin: 18px 0; max-height: 360px; object-fit: cover; width: 100%; }
        .actions { align-items: center; display: flex; gap: 12px; margin-top: 18px; }
        .actions a { color: #0b5cab; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Homepage Hero Video</h1>
        <?php if ($message !== '') { ?>
            <div class="notice <?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php } ?>
        <?php if ($hasVideo) { ?>
            <video controls muted playsinline>
                <source src="<?php echo htmlspecialchars($videoUrl, ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
            </video>
        <?php } else { ?>
            <p class="hint">No homepage hero video has been uploaded yet.</p>
        <?php } ?>
        <form method="post" enctype="multipart/form-data">
            <label for="home_hero_video">Upload MP4 video</label>
            <input type="file" id="home_hero_video" name="home_hero_video" accept="video/mp4" required>
            <p class="hint">Recommended: compressed MP4, landscape format, under 100 MB. This replaces the homepage background video.</p>
            <button type="submit">Save Hero Video</button>
        </form>
        <div class="actions">
            <a href="welcome.php">Back to Admin</a>
            <a href="../index.php" target="_blank">View Homepage</a>
        </div>
    </div>
</body>
</html>

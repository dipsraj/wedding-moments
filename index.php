<?php
session_start();

// Configuration
$uploadDir = 'images/';
$thumbnailDir = 'thumbnails/';
$adminPassword = 'admin123'; // Change this password!

// Create directories if they don't exist
if (!file_exists($uploadDir)) {
  mkdir($uploadDir, 0777, true);
}
if (!file_exists($thumbnailDir)) {
  mkdir($thumbnailDir, 0777, true);
}

// Handle file upload (multiple files)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photos'])) {
  $files = $_FILES['photos'];
  $uploadedCount = 0;

  for ($i = 0; $i < count($files['name']); $i++) {
    if ($files['error'][$i] === UPLOAD_ERR_OK) {
      $fileName = time() . '_' . $i . '_' . basename($files['name'][$i]);
      $uploadPath = $uploadDir . $fileName;
      $thumbnailPath = $thumbnailDir . $fileName;

      if (move_uploaded_file($files['tmp_name'][$i], $uploadPath)) {
        createThumbnail($uploadPath, $thumbnailPath);
        $uploadedCount++;
      }
    }
  }

  if ($uploadedCount > 0) {
    header('Location: /?uploaded=' . $uploadedCount);
    exit;
  }
}

// Handle admin login with error feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_password'])) {
  if ($_POST['admin_password'] === $adminPassword) {
    $_SESSION['admin'] = true;
    header('Location: /');
    exit;
  } else {
    // Redirect with an error flag on failed login
    header('Location: /?login_error=1');
    exit;
  }
}

// Handle admin logout
if (isset($_GET['logout'])) {
  unset($_SESSION['admin']);
  header('Location: /');
  exit;
}

// Handle single image deletion (Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image']) && isset($_SESSION['admin'])) {
  $imageToDelete = basename($_POST['delete_image']);
  $imagePath = $uploadDir . $imageToDelete;
  $thumbnailPath = $thumbnailDir . $imageToDelete;

  if (file_exists($imagePath)) unlink($imagePath);
  if (file_exists($thumbnailPath)) unlink($thumbnailPath);

  header('Location: /');
  exit;
}

// Handle multiple image deletion (Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_images']) && isset($_SESSION['admin'])) {
  $imagesToDelete = $_POST['delete_images'];
  foreach ($imagesToDelete as $image) {
    $safeImageName = basename($image);
    $imagePath = $uploadDir . $safeImageName;
    $thumbnailPath = $thumbnailDir . $safeImageName;

    if (file_exists($imagePath)) unlink($imagePath);
    if (file_exists($thumbnailPath)) unlink($thumbnailPath);
  }
  header('Location: /');
  exit;
}

// Function to create thumbnail
function createThumbnail($source, $destination, $width = 400, $height = 300) {
  $info = getimagesize($source);
  $mime = $info['mime'];

  switch ($mime) {
    case 'image/jpeg': $image = imagecreatefromjpeg($source); break;
    case 'image/png': $image = imagecreatefrompng($source); break;
    case 'image/gif': $image = imagecreatefromgif($source); break;
    default: return false;
  }

  $originalWidth = imagesx($image);
  $originalHeight = imagesy($image);
  $ratio = max($width / $originalWidth, $height / $originalHeight); // Use max for cover effect
  $newWidth = $originalWidth * $ratio;
  $newHeight = $originalHeight * $ratio;

  // Center crop
  $x_mid = $newWidth / 2;
  $y_mid = $newHeight / 2;

  $thumbnail = imagecreatetruecolor($width, $height);

  if ($mime == 'image/png' || $mime == 'image/gif') {
    imagealphablending($thumbnail, false);
    imagesavealpha($thumbnail, true);
    $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
    imagefilledrectangle($thumbnail, 0, 0, $width, $height, $transparent);
  }
  imagecopyresampled($thumbnail, $image, 0, 0, ($x_mid - ($width/2)), ($y_mid - ($height/2)), $newWidth, $newHeight, $originalWidth, $originalHeight);


  switch ($mime) {
    case 'image/jpeg': imagejpeg($thumbnail, $destination, 85); break;
    case 'image/png': imagepng($thumbnail, $destination); break;
    case 'image/gif': imagegif($thumbnail, $destination); break;
  }

  imagedestroy($image);
  imagedestroy($thumbnail);
  return true;
}

// Get all images, sorted by modification time (newest first)
$images = [];
if (is_dir($uploadDir)) {
  $files = scandir($uploadDir);
  foreach ($files as $file) {
    if ($file != '.' && $file != '..' && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif'])) {
      $images[] = [ 'name' => $file, 'time' => filemtime($uploadDir . $file) ];
    }
  }
  usort($images, function($a, $b) { return $b['time'] - $a['time']; });
}

// Dynamic URL for OG tags
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$baseURL = $protocol . $host;
$pageURL = $baseURL . $_SERVER['REQUEST_URI'];
$ogImageURL = $baseURL . '/assets/us.jpg';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riya & Dipanjan's Wedding Photo Gallery</title>
    <meta name="description" content="Welcome to the shared wedding photo album for Riya and Dipanjan. Browse, upload, and share beautiful memories from our special day with friends and family.">
    <meta name="keywords" content="Riya and Dipanjan, wedding, wedding photos, photo gallery, shared album, wedding moments">
    <link rel="canonical" href="<?php echo $baseURL; ?>/">
    <link rel="icon" href="favicon.ico" type="image/x-icon">

    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $pageURL; ?>">
    <meta property="og:title" content="Riya & Dipanjan's Wedding Photo Gallery">
    <meta property="og:description" content="Browse, upload, and share beautiful memories from our special day with friends and family.">
    <meta property="og:image" content="<?php echo $ogImageURL; ?>">
    <meta property="og:site_name" content="Riya & Dipanjan's Wedding">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo $pageURL; ?>">
    <meta property="twitter:title" content="Riya & Dipanjan's Wedding Photo Gallery">
    <meta property="twitter:description" content="Browse, upload, and share beautiful memories from our special day with friends and family.">
    <meta property="twitter:image" content="<?php echo $ogImageURL; ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Merriweather:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root { --parallax-y: 0px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Quicksand', sans-serif;
            /* UPDATED: Body background changed to the new light gradient */
            background: linear-gradient(135deg, #fffaf2 0%, #f1e4cc 100%);
            min-height: 100vh;
            color: #333; /* UPDATED: Base text color set to dark gray for readability */
            overflow-x: hidden;
        }
        /* UPDATED: Glow animation changed from gold to red */
        @keyframes glow { 0%, 100% { transform: scale(1); box-shadow: 0 0 20px 5px rgba(195, 36, 36, 0.3), 0 0 30px 8px rgba(195, 36, 36, 0.2); opacity: 0.8; } 50% { transform: scale(1.1); box-shadow: 0 0 35px 8px rgba(195, 36, 36, 0.5), 0 0 50px 15px rgba(195, 36, 36, 0.3); opacity: 1; } }
        @keyframes swing { 0% { transform: translateY(var(--parallax-y)) rotate(4deg); } 50% { transform: translateY(var(--parallax-y)) rotate(-4deg); } 100% { transform: translateY(var(--parallax-y)) rotate(4deg); } }
        .parallax-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; }
        .parallax-element { position: absolute; background-repeat: no-repeat; background-size: contain; transition: transform 0.2s linear; transform: translateY(var(--parallax-y)); will-change: transform; }
        .alpona { opacity: 0.1; }
        /* UPDATED: Alpona SVG fill changed from white to dark red (#8B0000) to be visible on light bg */
        .alpona-1 { width: 200px; height: 200px; top: 10%; left: 5%; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cpath d='M50,5 C74.8,5 95,25.2 95,50 C95,74.8 74.8,95 50,95 C25.2,95 5,74.8 5,50 C5,25.2 25.2,5 50,5 Z M50,15 C30.7,15 15,30.7 15,50 C15,69.3 30.7,85 50,85 C69.3,85 85,69.3 85,50 C85,30.7 69.3,15 50,15 Z' fill='%238B0000'/%3E%3Ccircle cx='50' cy='50' r='10' fill='%238B0000'/%3E%3C/svg%3E"); }
        /* UPDATED: Alpona SVG fill changed from white to dark red (#8B0000) */
        .alpona-2 { width: 150px; height: 150px; top: 60%; right: 10%; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cpath d='M50 0 C-10 40, -10 60, 50 100 C110 60, 110 40, 50 0 Z' fill='%238B0000'/%3E%3C/svg%3E"); }
        /* UPDATED: Alpona SVG fill changed from white to dark red (#8B0000) and gold to bright red (#c32424) */
        .alpona-3 { width: 100px; height: 100px; bottom: 5%; left: 25%; opacity: 0.15; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='25' r='15' fill='%238B0000'/%3E%3Ccircle cx='50' cy='75' r='15' fill='%238B0000'/%3E%3Ccircle cx='25' cy='50' r='15' fill='%238B0000'/%3E%3Ccircle cx='75' cy='50' r='15' fill='%238B0000'/%3E%3Ccircle cx='50' cy='50' r='10' fill='%23c32424' fill-opacity='0.5'/%3E%3C/svg%3E"); }
        .glowing-light { border-radius: 50%; animation-name: glow; animation-timing-function: ease-in-out; animation-iteration-count: infinite; }
        .light-1 { width: 15px; height: 15px; top: 20%; right: 20%; animation-duration: 4s; }
        .light-2 { width: 10px; height: 10px; top: 80%; left: 15%; animation-duration: 5s; animation-delay: 1.5s; }
        .light-3 { width: 12px; height: 12px; top: 40%; left: 40%; animation-duration: 3.5s; animation-delay: 0.5s; }
        .swinging-curtain { transform-origin: top center; animation-name: swing; animation-timing-function: ease-in-out; animation-iteration-count: infinite; }
        /* UPDATED: Curtain gradient changed from gold to red */
        .curtain-1 { width: 2px; height: 150px; top: -20px; left: 30%; background: linear-gradient(to bottom, rgba(195, 36, 36, 0.7), transparent); animation-duration: 8s; }
        /* UPDATED: Curtain gradient changed from gold to red */
        .curtain-2 { width: 2px; height: 120px; top: -10px; right: 35%; background: linear-gradient(to bottom, rgba(195, 36, 36, 0.6), transparent); animation-duration: 10s; animation-delay: 1s; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; position: relative; z-index: 1; }

        /* UPDATED: Header base color changed from white to dark gray */
        .header { display: flex; flex-direction: column; align-items: center; text-align: center; margin-bottom: 2rem; color: #333; }
        /* UPDATED: Header image border changed from white to red */
        .header-image { width: 150px; height: 150px; border-radius: 50%; border: 4px solid rgba(195, 36, 36, 0.8); box-shadow: 0 4px 25px rgba(0,0,0,0.2); margin-bottom: 1.5rem; object-fit: cover; }
        /* UPDATED: Header h1 color changed from gold to red */
        .header h1 { font-family: 'Great Vibes', cursive; font-size: 4rem; font-weight: normal; line-height: 1; margin-bottom: 1.5rem; color: #c32424; text-shadow: 1px 1px 3px rgba(0,0,0,0.3); }
        /* UPDATED: Subtitle color changed from white to dark red */
        .header .subtitle { font-family: 'Quicksand', serif; font-size: 1.2rem; margin-bottom: 2rem; color: #8B0000; text-transform: uppercase; letter-spacing: 0.2em; font-weight: 700; text-shadow: 1px 1px 2px rgba(0,0,0,0.2); padding-bottom: 1rem; position: relative; }
        /* UPDATED: Subtitle underline and shadow changed from gold to red */
        .header .subtitle::after { content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 80px; height: 2px; background: #c32424; box-shadow: 0 0 10px #c32424; }
        /* UPDATED: Header paragraph color changed from white to dark gray */
        .header p { font-size: 1.1rem; line-height: 1.7; max-width: 600px; color: #333; }

        #countdown-timer {
            text-align: center;
            margin: -1rem auto 3rem;
            padding: 1.5rem;
            /* UPDATED: Background changed from transparent black to a subtle transparent red */
            background: rgba(195, 36, 36, 0.05);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            max-width: 800px;
        }
        #countdown-timer .timer-message {
            font-size: 1.1rem;
            /* UPDATED: Text color changed from white to dark gray */
            color: #333;
            margin-bottom: 1.5rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        #countdown-timer .timer-inner {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem 1.5rem;
            /* UPDATED: Text color changed from white to a medium gray for labels */
            color: #555;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        #countdown-timer .timer-inner.row-2 {
            margin-top: 1rem;
        }
        #countdown-timer .time-unit {
            display: flex;
            flex-direction: column;
            align-items: center;
            line-height: 1;
        }
        #countdown-timer .time-value {
            font-size: 2.5rem;
            font-weight: 700;
            /* UPDATED: Time value color changed from gold to red */
            color: #c32424;
        }
        #countdown-timer .time-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            opacity: 0.8;
        }
        #countdown-timer .total-days {
            width: 100%;
            margin-top: 1.5rem;
            font-size: 1rem;
            font-style: italic;
            opacity: 0.9;
        }
        #confetti-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2000;
            pointer-events: none;
        }
        .big-day-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 2001;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }
        .big-day-content {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            text-align: center;
            color: #333;
            font-size: 1.8rem;
            font-family: 'Great Vibes', cursive;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.8); } to { opacity: 1; transform: scale(1); } }


        /* UPDATED: Controls background changed from transparent black to subtle transparent red */
        .controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; padding: 1rem; background: rgba(195, 36, 36, 0.05); border-radius: 15px; }
        .left-controls { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;}
        .right-controls { display: flex; align-items: center; gap: 1rem; }
        /* UPDATED: Added a light red border for visibility against the light background */
        .checkbox-toggle { display: flex; align-items: center; gap: 0.5rem; background: rgba(255, 253, 243, 0.9); padding: 0.5rem 1rem; border-radius: 25px; backdrop-filter: blur(10px); border: 1px solid rgba(195, 36, 36, 0.2); }
        .checkbox-toggle label { cursor: pointer; color: #333; }
        /* UPDATED: Admin button re-themed with red */
        .admin-btn { background: rgba(195, 36, 36, 0.1); border: 1px solid rgba(195, 36, 36, 0.3); color: #8B0000; padding: 0.5rem 1rem; border-radius: 25px; cursor: pointer; backdrop-filter: blur(10px); transition: all 0.3s ease; text-decoration: none; display: inline-block; }
        .admin-btn:hover { background: rgba(195, 36, 36, 0.2); }

        /* UPDATED: View toggle button re-themed with red */
        .view-toggle-btn {
            display: none;
            background: rgba(195, 36, 36, 0.1);
            border: 1px solid rgba(195, 36, 36, 0.3);
            color: #8B0000;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        .view-toggle-btn:hover { background: rgba(195, 36, 36, 0.2); }
        /* UPDATED: SVG stroke color changed from white to red */
        .view-toggle-btn svg { width: 22px; height: 22px; stroke: #8B0000; }

        .gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 120px; transition: grid-template-columns 0.5s ease; }
        .gallery.two-columns { grid-template-columns: repeat(2, 1fr); }
        .gallery.single-column { grid-template-columns: 1fr; }

        .gallery-item { position: relative; background: rgba(255, 253, 243, 0.95); border-radius: 15px; overflow: hidden; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease, box-shadow 0.3s ease; }

        .gallery-item .view-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
            pointer-events: none;
        }
        .gallery-item .view-overlay svg {
            width: 40px;
            height: 40px;
            color: rgba(255,255,255,0.8);
            filter: drop-shadow(0 1px 3px rgba(0,0,0,0.6));
        }
        .no-touch .gallery-item:hover .view-overlay {
            opacity: 1;
        }
        .touch .gallery-item .view-overlay {
            opacity: 1;
            background: none;
        }
        .touch .gallery-item .view-overlay svg {
            width: 32px;
            height: 32px;
        }

        .gallery-item:hover { transform: translateY(-5px); box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15); }

        .gallery-item img { display: block; width: 100%; height: 250px; object-fit: cover; cursor: pointer; }

        .image-controls { position: absolute; top: 10px; right: 10px; display: flex; gap: 0.5rem; opacity: 0; transition: opacity 0.3s ease; z-index: 3; }
        .no-touch .gallery-item:hover .image-controls, .touch .gallery-item .image-controls { opacity: 1; }
        .control-btn { background: rgba(0, 0, 0, 0.7); color: white; border: none; width: 35px; height: 35px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.3s ease; }
        .control-btn:hover { background: rgba(0, 0, 0, 0.9); }

        .download-btn .icon-hover { display: none; }
        .download-btn:hover .icon-default { display: none; }
        .download-btn:hover .icon-hover { display: block; }
        .control-btn svg { vertical-align: middle; }

        label.checkbox-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 80px;
            height: 80px;
            display: flex;
            padding: 12px;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
            cursor: pointer;
            z-index: 2;
        }
        label.checkbox-overlay.show { opacity: 1; pointer-events: auto; }
        label.checkbox-overlay input {
            width: 28px;
            height: 28px;
            cursor: pointer;
            accent-color: #c32424; /* KEPT: This is already red */
        }

        /* KEPT: This button style already fits the new theme perfectly */
        .upload-btn { position: fixed; bottom: 30px; right: 30px; z-index: 100; padding: 1rem 1.5rem; background: #731414; border: 3px solid #ffe3e3; border-radius: 50px; color: #F1F1F1; font-size: 1.1rem; font-weight: 700; cursor: pointer; box-shadow: 0 12px 40px rgba(195, 36, 36, 0.4); transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 1px; animation: pulse 2s infinite; display: flex; align-items: center; gap: 0.5rem; }
        .upload-btn .icon { width: 24px; height: 24px; }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.03); } 100% { transform: scale(1); } }
        /* UPDATED: Hover border color changed from gold to light cream (matching page bg) */
        .upload-btn:hover { transform: scale(1.05); box-shadow: 0 16px 50px rgba(195, 36, 36, 0.6); background: linear-gradient(135deg, #c32424, #8B0000); color: white; border-color: #fffaf2; }

        .fixed-action-buttons-container { position: fixed; bottom: 30px; left: 30px; right: 30px; z-index: 100; display: flex; justify-content: flex-start; gap: 1rem; pointer-events: none; }
        .action-btn-fixed { padding: 1rem 1.5rem; border: none; border-radius: 25px; cursor: pointer; transition: all 0.3s ease; font-size: 1rem; font-weight: bold; opacity: 0; transform: translateY(20px); pointer-events: none; }
        .action-btn-fixed.show { opacity: 1; transform: translateY(0); pointer-events: auto; }
        /* UPDATED: Download button changed from green to red for theme consistency */
        #downloadSelected { background: #c32424; color: white; }
        #downloadSelected:hover { background: #8B0000; }
        #deleteSelected { background: #dc3545; color: white; } /* KEPT: This is a good danger-red */
        #deleteSelected:hover { background: #c82333; }

        .modal { display: none; opacity: 0; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.9); z-index: 1000; backdrop-filter: blur(10px); align-items: center; justify-content: center; padding: 1rem; transition: opacity 0.3s ease; }
        .modal-content-wrapper { position: relative; max-width: 90vw; max-height: 90vh; display: flex; flex-direction: column; align-items: center; }
        .modal img { max-width: 100%; max-height: calc(90vh - 80px); object-fit: contain; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); }
        .modal-actions-bar { display: flex; gap: 1rem; padding-top: 1.5rem; }
        .modal-action-btn { padding: 0.75rem 1.5rem; border-radius: 50px; border: none; cursor: pointer; font-weight: bold; color: white; transition: transform 0.2s, box-shadow 0.2s; display: flex; align-items: center; gap: 0.5rem; }
        .modal-action-btn:hover { transform: scale(1.05); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        /* UPDATED: Download button changed from green to red */
        .modal-action-btn.download { background: #c32424; }
        .modal-action-btn.delete { background: #dc3545; } /* KEPT: Red */

        .modal-nav { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(20, 20, 20, 0.5); color: white; width: 44px; height: 44px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px); border: 1px solid rgba(255, 255, 255, 0.2); transition: all 0.3s ease; z-index: 1001; }
        .modal-nav:hover { background: rgba(0, 0, 0, 0.7); transform: translateY(-50%) scale(1.1); }
        .modal-prev { left: 15px; }
        .modal-next { right: 15px; }
        .modal-close { position: absolute; top: 15px; right: 15px; background: rgba(20, 20, 20, 0.5); border: 1px solid rgba(255, 255, 255, 0.2); color: white; width: 38px; height: 38px; border-radius: 50%; cursor: pointer; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; z-index: 1001; transition: all 0.3s ease; }
        .modal-close:hover { background: rgba(0, 0, 0, 0.7); transform: scale(1.1); }

        .admin-modal, .upload-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 1000; backdrop-filter: blur(10px); }
        .admin-form, .upload-form { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); width: 90%; max-width: 400px; }
        .admin-form h3, .upload-form h3 { margin-bottom: 1.5rem; text-align: center; }
        .admin-form input { width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; }
        /* KEPT: Button is already red */
        .admin-form button, .upload-form button { width: 100%; padding: 0.75rem; background: linear-gradient(135deg, #c32424, #8B0000); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; margin-bottom: 0.5rem; }
        .file-input-wrapper { position: relative; margin-bottom: 1.5rem; }
        .file-input { position: absolute; opacity: 0; width: 100%; height: 100%; cursor: pointer; }
        .file-input-label { display: block; padding: 2rem; border: 2px dashed #ddd; border-radius: 8px; text-align: center; cursor: pointer; transition: all 0.3s ease; }
        /* KEPT: Hover state is already red-themed */
        .file-input-label:hover { border-color: #c32424; background: #fff9f9; }
        .selected-files { margin-bottom: 1rem; max-height: 150px; overflow-y: auto; }
        .file-item { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; background: #f8f9fa; border-radius: 5px; margin-bottom: 0.25rem; font-size: 0.9rem; }
        .file-item .file-name { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .file-item .file-size { color: #666; font-size: 0.8rem; margin-left: 0.5rem; }
        /* KEPT: Remove button is already red */
        .remove-file { background: #c32424; color: white; border: none; border-radius: 3px; padding: 0.25rem 0.5rem; cursor: pointer; font-size: 0.8rem; margin-left: 0.5rem; }
        .cancel-btn { background: #6c757d !important; }

        .toast-message { position: fixed; top: 20px; right: 20px; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); z-index: 1001; opacity: 0; transform: translateX(120%); transition: all 0.5s cubic-bezier(0.25, 1, 0.5, 1); }
        .toast-message.show { opacity: 1; transform: translateX(0); }
        .toast-success { background: #c32424; } /* KEPT: Already red */
        .toast-error { background: #dc3545; } /* KEPT: Already red */

        @media (max-width: 768px) {
            .header h1 { font-size: 3rem; }
            .header .subtitle { font-size: 1.3rem; letter-spacing: 0.1em; }
            .header p { font-size: 1rem; }
            .container { padding: 1rem; }
            #countdown-timer .time-value { font-size: 2rem; }
            #countdown-timer .time-label { font-size: 0.7rem; }
            .modal-prev { left: 5px; }
            .modal-next { right: 5px; }
            .modal-close { top: 5px; right: 5px; }
            .image-controls { opacity: 1; background: linear-gradient(to top, rgba(0,0,0,0.4) 0%, rgba(0,0,0,0) 100%); bottom: 0; top: auto; left: 0; right: 0; width: 100%; border-radius: 0 0 15px 15px; padding: 0.75rem; justify-content: flex-end; }
            .view-toggle-btn { display: flex; }
            .gallery { grid-template-columns: repeat(2, 1fr); }
            .gallery.single-column { grid-template-columns: 1fr; }
            .touch .gallery-item .view-overlay {
                display: none;
            }
        }
        @media (max-width: 480px) {
            .upload-btn { font-size: 0.9rem; padding: 0.8rem 1.2rem; right: 20px; bottom: 20px; }
            .fixed-action-buttons-container { left: 20px; right: 20px; bottom: 20px; justify-content: center; }
            .action-btn-fixed { flex-grow: 1; margin: 0 5px; padding: 0.8rem 1rem; font-size: 0.9rem; }
            .controls { flex-direction: column; align-items: stretch; gap: 0.8rem; }
            .left-controls { justify-content: space-between; }
            .right-controls { justify-content: center; }
            .admin-btn { width: 100%; text-align: center; }
            .gallery { grid-template-columns: repeat(3, 1fr); } /* UPDATED: Default to 3 columns */
            .gallery.two-columns { grid-template-columns: 1fr; } /* UPDATED: This now acts as the single-column toggle on mobile */
            .gallery-item { aspect-ratio: 1 / 1; } /* ADDED: Make items square */
            .gallery-item img { height: 100%; } /* ADDED: Override fixed height, fill square */
            #countdown-timer .timer-inner { gap: 0.5rem 1rem; }
            #countdown-timer .time-value { font-size: 1.5rem; }
            #countdown-timer .timer-inner.row-2 {
                gap: 1rem 1.2rem;
            }
        }
    </style>
</head>
<body>

<div class="parallax-container">
    <div class="parallax-element alpona alpona-1" data-speed="0.3"></div>
    <div class="parallax-element alpona alpona-2" data-speed="0.5"></div>
    <div class="parallax-element alpona alpona-3" data-speed="0.8"></div>
    <div class="parallax-element glowing-light light-1" data-speed="-0.2"></div>
    <div class="parallax-element glowing-light light-2" data-speed="0.2"></div>
    <div class="parallax-element glowing-light light-3" data-speed="-0.1"></div>
    <div class="parallax-element swinging-curtain curtain-1" data-speed="0.4"></div>
    <div class="parallax-element swinging-curtain curtain-2" data-speed="0.6"></div>
</div>

<canvas id="confetti-canvas"></canvas>


<div class="container">
    <div class="header">
        <img src="./assets/us.jpg" alt="Riya & Dipanjan's Wedding" class="header-image" onerror="this.style.display='none'">
        <div class="header-text">
            <h1>Riya & Dipanjan</h1>
            <h2 class="subtitle">Our Wedding Moments</h2>
            <p>
                Welcome, dear friends and family! We are so happy to share this day with you. This is our shared digital album, a place to capture and relive the beautiful memories we're all creating together. Please add the moments you've captured and enjoy Browse the photos shared by others.
            </p>
        </div>
    </div>

    <div id="countdown-timer"></div>

    <div class="controls">
        <div class="left-controls">
            <div class="checkbox-toggle">
                <input type="checkbox" id="enableCheckboxes" onchange="toggleCheckboxes()">
                <label for="enableCheckboxes">Enable multi-select</label>
            </div>
            <button class="view-toggle-btn" id="viewToggleBtn" title="Toggle Grid View">
                <svg class="grid-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect></svg>
                <svg class="list-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
            </button>
        </div>
        <div class="right-controls">
          <?php if (isset($_SESSION['admin'])): ?>
              <a href="/?logout=1" class="admin-btn">Admin Logout</a>
          <?php else: ?>
              <button class="admin-btn" onclick="showAdminLogin()">Admin Login</button>
          <?php endif; ?>
        </div>
    </div>

    <div class="gallery" id="gallery">
      <?php foreach ($images as $image): ?>
          <div class="gallery-item">
              <div class="view-overlay">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12,4.5C7,4.5,2.73,7.61,1,12c1.73,4.39,6,7.5,11,7.5s9.27-3.11,11-7.5C21.27,7.61,17,4.5,12,4.5z M12,17c-2.76,0-5-2.24-5-5s2.24-5,5-5s5,2.24,5,5S14.76,17,12,17z M12,9c-1.66,0-3,1.34-3,3s1.34,3,3,3s3-1.34,3-3S13.66,9,12,9z"/></svg>
              </div>
              <img src="<?php echo $thumbnailDir . htmlspecialchars($image['name']); ?>"
                   onclick="openModal('<?php echo $uploadDir . htmlspecialchars($image['name']); ?>', <?php echo array_search($image, $images); ?>)"
                   alt="Gallery Image">
              <label class="checkbox-overlay" onclick="event.stopPropagation();">
                  <input type="checkbox" class="image-checkbox" value="<?php echo htmlspecialchars($image['name']); ?>" onchange="updateActionButtons()">
              </label>
              <div class="image-controls">
                  <button class="control-btn download-btn" onclick="event.stopPropagation(); downloadImage('<?php echo $uploadDir . htmlspecialchars($image['name']); ?>', '<?php echo htmlspecialchars($image['name']); ?>')" title="Download">
                      <svg class="icon-default" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                      <svg class="icon-hover" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                  </button>
                <?php if (isset($_SESSION['admin'])): ?>
                    <button class="control-btn" onclick="event.stopPropagation(); confirmSingleDelete('<?php echo htmlspecialchars($image['name']); ?>')" title="Delete" style="background: rgba(220, 53, 69, 0.8);">&#x1F5D1;</button>
                <?php endif; ?>
              </div>
          </div>
      <?php endforeach; ?>
    </div>

  <?php if (!isset($_SESSION['admin'])): ?>
      <button class="upload-btn" onclick="showUploadModal()" title="Share Your Moments">
          <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 4v-2h14v2H5z"/></svg>
          Share Moments
      </button>
  <?php endif; ?>
</div>

<div id="toastContainer"></div>

<div class="big-day-modal" id="bigDayModal">
    <div class="big-day-content">
        The big day is finally here!
    </div>
</div>


<div class="fixed-action-buttons-container">
    <button class="action-btn-fixed" id="downloadSelected" onclick="downloadSelected()">Download Selected</button>
  <?php if (isset($_SESSION['admin'])): ?>
      <button class="action-btn-fixed" id="deleteSelected" onclick="deleteSelected()">Delete Selected</button>
  <?php endif; ?>
</div>

<div class="modal" id="imageModal">
    <button class="modal-close" onclick="closeModal()">×</button>
    <button class="modal-nav modal-prev" onclick="prevImage()">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/></svg>
    </button>
    <div class="modal-content-wrapper">
        <img id="modalImage" src="" alt="Full Size Image">
        <div class="modal-actions-bar">
            <button id="modalDownloadBtn" class="modal-action-btn download">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/></svg>
                <span>Download</span>
            </button>
          <?php if (isset($_SESSION['admin'])): ?>
              <button id="modalDeleteBtn" class="modal-action-btn delete">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg>
                  <span>Delete</span>
              </button>
          <?php endif; ?>
        </div>
    </div>
    <button class="modal-nav modal-next" onclick="nextImage()">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/></svg>
    </button>
</div>

<div class="admin-modal" id="adminModal">
    <div class="admin-form">
        <h3>Admin Login</h3>
        <form method="POST" action="/">
            <input type="password" name="admin_password" placeholder="Enter admin password" required>
            <button type="submit">Login</button>
            <button type="button" class="cancel-btn" onclick="hideAdminLogin()">Cancel</button>
        </form>
    </div>
</div>

<div class="upload-modal" id="uploadModal">
    <div class="upload-form">
        <h3>Upload Photos</h3>
        <form method="POST" enctype="multipart/form-data" action="/">
            <div class="file-input-wrapper">
                <input type="file" name="photos[]" class="file-input" accept="image/*" multiple required onchange="updateFileLabel(this)">
                <label class="file-input-label" id="fileLabel">
                    <div>&#x1F4F8;</div>
                    <div>Click to select photos or drag & drop</div>
                    <small>You can select multiple photos at once</small>
                </label>
            </div>
            <div id="selectedFiles" class="selected-files"></div>
            <button type="submit">Upload Photos</button>
            <button type="button" class="cancel-btn" onclick="hideUploadModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
  document.addEventListener('scroll', () => {
    window.requestAnimationFrame(() => {
      const scrolled = window.scrollY;
      document.querySelectorAll('.parallax-element').forEach(el => {
        el.style.setProperty('--parallax-y', `${-(scrolled * el.dataset.speed)}px`);
      });
    });
  });

  let currentImageIndex = 0;
  const images = <?php echo json_encode(array_column($images, 'name')); ?>;
  const imageDir = '<?php echo $uploadDir; ?>';
  const isAdmin = <?php echo isset($_SESSION['admin']) ? 'true' : 'false'; ?>;

  function toggleCheckboxes() {
    const isEnabled = document.getElementById('enableCheckboxes').checked;
    document.querySelectorAll('.checkbox-overlay').forEach(checkbox => {
      checkbox.classList.toggle('show', isEnabled);
      if (!isEnabled) {
        checkbox.querySelector('input').checked = false;
      }
    });
    updateActionButtons();
  }

  function updateActionButtons() {
    const selectedCount = document.querySelectorAll('.image-checkbox:checked').length;
    const isMultiSelectOn = document.getElementById('enableCheckboxes').checked;

    const downloadBtn = document.getElementById('downloadSelected');
    const showDownload = selectedCount > 0 && isMultiSelectOn;
    downloadBtn.classList.toggle('show', showDownload);
    if(showDownload) downloadBtn.textContent = `Download (${selectedCount})`;

    if (isAdmin) {
      const deleteBtn = document.getElementById('deleteSelected');
      const showDelete = selectedCount > 0 && isMultiSelectOn;
      deleteBtn.classList.toggle('show', showDelete);
      if(showDelete) deleteBtn.textContent = `Delete (${selectedCount})`;
    }
  }

  function updateModalActions(index) {
    const fileName = images[index];
    if (!fileName) return;

    const downloadBtn = document.getElementById('modalDownloadBtn');
    if (downloadBtn) {
      downloadBtn.onclick = () => downloadImage(imageDir + fileName, fileName);
    }

    if (isAdmin) {
      const deleteBtn = document.getElementById('modalDeleteBtn');
      if (deleteBtn) {
        deleteBtn.onclick = () => {
          closeModal();
          setTimeout(() => confirmSingleDelete(fileName), 100);
        };
      }
    }
  }

  function openModal(imageSrc, index) {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'flex';
    setTimeout(() => modal.style.opacity = 1, 10);

    document.getElementById('modalImage').src = imageSrc;
    currentImageIndex = index;
    updateModalActions(index);
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    const modal = document.getElementById('imageModal');
    modal.style.opacity = 0;
    setTimeout(() => {
      modal.style.display = 'none';
      document.body.style.overflow = 'auto';
    }, 300);
  }

  function nextImage() {
    currentImageIndex = (currentImageIndex + 1) % images.length;
    document.getElementById('modalImage').src = imageDir + images[currentImageIndex];
    updateModalActions(currentImageIndex);
  }

  function prevImage() {
    currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
    document.getElementById('modalImage').src = imageDir + images[currentImageIndex];
    updateModalActions(currentImageIndex);
  }

  function showAdminLogin() { document.getElementById('adminModal').style.display = 'block'; document.body.style.overflow = 'hidden'; }
  function hideAdminLogin() { document.getElementById('adminModal').style.display = 'none'; document.body.style.overflow = 'auto'; }
  function showUploadModal() {
    document.getElementById('uploadModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    const input = document.querySelector('input[name="photos[]"]');
    input.value = '';
    updateFileLabel(input);
  }
  function hideUploadModal() { document.getElementById('uploadModal').style.display = 'none'; document.body.style.overflow = 'auto'; }

  function downloadImage(imageSrc, fileName) {
    const link = document.createElement('a');
    link.href = imageSrc;
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  function downloadSelected() {
    document.querySelectorAll('.image-checkbox:checked').forEach(checkbox => {
      downloadImage(imageDir + checkbox.value, checkbox.value);
    });
  }

  function confirmSingleDelete(fileName) {
    createConfirmModal('Are you sure you want to delete this image?', () => {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '/';
      form.innerHTML = `<input type="hidden" name="delete_image" value="${fileName}">`;
      document.body.appendChild(form);
      form.submit();
    });
  }

  function deleteSelected() {
    const selectedCheckboxes = document.querySelectorAll('.image-checkbox:checked');
    if (selectedCheckboxes.length === 0) return;

    const message = `Are you sure you want to delete these ${selectedCheckboxes.length} images? This action cannot be undone.`;
    createConfirmModal(message, () => {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '/';
      selectedCheckboxes.forEach(checkbox => {
        form.innerHTML += `<input type="hidden" name="delete_images[]" value="${checkbox.value}">`;
      });
      document.body.appendChild(form);
      form.submit();
    });
  }

  function createConfirmModal(message, onConfirm) {
    const modal = document.createElement('div');
    modal.style.cssText = 'position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; z-index:2000; backdrop-filter: blur(5px);';
    modal.innerHTML = `
      <div style="background:white; padding:2rem; border-radius:15px; text-align:center; max-width: 400px; margin: 1rem; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <p style="margin-bottom: 1.5rem; font-size: 1.1rem; color: #333; line-height: 1.5;">${message}</p>
        <button id="confirmBtn" style="background:#dc3545; color:white; border:none; padding:0.75rem 1.5rem; border-radius:8px; cursor:pointer; margin: 0 0.25rem; font-weight: bold;">Yes, Delete</button>
        <button id="cancelBtn" style="background:#6c757d; color:white; border:none; padding:0.75rem 1.5rem; border-radius:8px; cursor:pointer; margin: 0 0.25rem;">Cancel</button>
      </div>
    `;
    document.body.appendChild(modal);
    document.getElementById('confirmBtn').onclick = () => {
      onConfirm();
      document.body.removeChild(modal);
    };
    document.getElementById('cancelBtn').onclick = () => document.body.removeChild(modal);
  }

  function updateFileLabel(input) {
    const label = document.getElementById('fileLabel');
    const selectedFilesDiv = document.getElementById('selectedFiles');
    const files = input.files;
    if (files.length > 0) {
      label.innerHTML = `<div>&#x1F4F8;</div><div>${files.length} photo${files.length > 1 ? 's' : ''} selected</div><small>Click to change selection</small>`;
      selectedFilesDiv.innerHTML = Array.from(files).map((file, index) => `
        <div class="file-item">
          <span class="file-name">${file.name}</span>
          <span class="file-size">${formatFileSize(file.size)}</span>
          <button type="button" class="remove-file" onclick="removeFile(event, ${index})">×</button>
        </div>`).join('');
    } else {
      label.innerHTML = `<div>&#x1F4F8;</div><div>Click to select photos or drag & drop</div><small>You can select multiple photos at once</small>`;
      selectedFilesDiv.innerHTML = '';
    }
  }

  function formatFileSize(bytes) { if (bytes === 0) return '0 Bytes'; const k = 1024; const i = Math.floor(Math.log(bytes) / Math.log(k)); return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + ['Bytes', 'KB', 'MB', 'GB'][i]; }

  function removeFile(event, index) {
    event.stopPropagation();
    const input = document.querySelector('input[name="photos[]"]');
    const dt = new DataTransfer();
    Array.from(input.files).forEach((file, i) => { if (i !== index) dt.items.add(file); });
    input.files = dt.files;
    updateFileLabel(input);
  }

  document.addEventListener('keydown', (e) => {
    const modal = document.getElementById('imageModal');
    if (modal.style.display === 'flex') {
      if (e.key === 'ArrowLeft') prevImage();
      else if (e.key === 'ArrowRight') nextImage();
      else if (e.key === 'Escape') closeModal();
    }
  });

  window.onclick = (event) => {
    const imageModal = document.getElementById('imageModal');
    if (event.target == imageModal) {
      closeModal();
    }
    if (event.target == document.getElementById('adminModal')) hideAdminLogin();
    if (event.target == document.getElementById('uploadModal')) hideUploadModal();
  }

  function showToast(message, type = 'success', duration = 4000) {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast-message toast-${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
      toast.classList.remove('show');
      toast.addEventListener('transitionend', () => toast.remove());
    }, duration);
  }

  document.addEventListener('DOMContentLoaded', () => {
    if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
      document.body.classList.add('touch');
    } else {
      document.body.classList.add('no-touch');
    }

    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.has('uploaded')) {
      const count = urlParams.get('uploaded');
      showToast(`✅ Successfully uploaded ${count} photo${count > 1 ? 's' : ''}!`, 'success');
      if (history.replaceState) {
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({path:cleanUrl},'',cleanUrl);
      }
    }

    if (urlParams.has('login_error')) {
      showToast('❌ Invalid password. Please try again.', 'error');
      showAdminLogin();
      if (history.replaceState) {
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({path:cleanUrl},'',cleanUrl);
      }
    }

    const viewToggleBtn = document.getElementById('viewToggleBtn');
    if (viewToggleBtn) {
      const gallery = document.getElementById('gallery');
      const gridIcon = viewToggleBtn.querySelector('.grid-icon');
      const listIcon = viewToggleBtn.querySelector('.list-icon');

      viewToggleBtn.addEventListener('click', () => {
        let isSingleColumn = gallery.classList.contains('single-column');

        if (window.innerWidth <= 480) { // On very small screens, toggle between 3 and 1
          gallery.classList.toggle('two-columns'); // This class now maps to 1-column in CSS
          let isOneCol = gallery.classList.contains('two-columns');

          // UPDATED: Swapped the icon logic here to be correct
          gridIcon.style.display = isOneCol ? 'block' : 'none'; // Show 3-col icon when 1-col is active
          listIcon.style.display = isOneCol ? 'none' : 'block'; // Show 1-col icon when 3-col is active

        } else { // On tablet/desktop, toggle between multi-column and 1
          gallery.classList.toggle('single-column');
          isSingleColumn = gallery.classList.contains('single-column');
          gridIcon.style.display = isSingleColumn ? 'block' : 'none'; // Show grid icon when 1-col is active
          listIcon.style.display = isSingleColumn ? 'none' : 'block'; // Show list icon when grid is active
        }
      });
    }

    const countdownDate = new Date("2026-01-23T00:00:00").getTime();
    const countdownElement = document.getElementById('countdown-timer');
    let countdownInterval = null;

    const confettiCanvas = document.getElementById('confetti-canvas');
    const confettiCtx = confettiCanvas.getContext('2d');
    let confettiParticles = [];
    const confettiColors = ['#FFD700', '#FF6347', '#ADFF2F', '#87CEFA', '#DA70D6'];

    function startConfetti() {
      confettiCanvas.width = window.innerWidth;
      confettiCanvas.height = window.innerHeight;
      for (let i = 0; i < 150; i++) {
        confettiParticles.push({
          x: Math.random() * confettiCanvas.width,
          y: Math.random() * confettiCanvas.height - confettiCanvas.height,
          size: Math.random() * 8 + 2,
          speed: Math.random() * 5 + 2,
          color: confettiColors[Math.floor(Math.random() * confettiColors.length)],
          angle: Math.random() * 360,
          spin: Math.random() < 0.5 ? -1 : 1
        });
      }
      animateConfetti();
    }

    function animateConfetti() {
      confettiCtx.clearRect(0, 0, confettiCanvas.width, confettiCanvas.height);
      confettiParticles.forEach((p, i) => {
        p.y += p.speed;
        p.x += Math.sin(p.angle * Math.PI / 180);
        p.angle += p.spin * 2;

        confettiCtx.fillStyle = p.color;
        confettiCtx.beginPath();
        confettiCtx.rect(p.x, p.y, p.size, p.size);
        confettiCtx.fill();

        if (p.y > confettiCanvas.height) {
          confettiParticles.splice(i, 1);
        }
      });
      if (confettiParticles.length > 0) {
        requestAnimationFrame(animateConfetti);
      }
    }


    function updateCountdown() {
      const now = new Date();
      const target = new Date(countdownDate);
      const distance = target - now;

      if (distance < 0) {
        clearInterval(countdownInterval);
        startConfetti();
        const bigDayModal = document.getElementById('bigDayModal');
        bigDayModal.style.display = 'flex';
        setTimeout(() => {
          bigDayModal.style.display = 'none';
          countdownElement.style.display = 'none';
        }, 4000);
        return;
      }

      const totalDays = Math.floor(distance / (1000 * 60 * 60 * 24));

      let months = (target.getFullYear() - now.getFullYear()) * 12 + (target.getMonth() - now.getMonth());

      let tempNow = new Date(now);
      tempNow.setMonth(tempNow.getMonth() + months);

      if (tempNow > target) {
        months--;
      }

      tempNow = new Date(now);
      tempNow.setMonth(tempNow.getMonth() + months);

      let remainingDistance = target - tempNow;
      let remainingDays = Math.floor(remainingDistance / (1000 * 60 * 60 * 24));

      const weeks = Math.floor(remainingDays / 7);
      const days = remainingDays % 7;
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      let timerHTML = '<div class="timer-message">The countdown to our wedding begins!</div>';

      let topRow = '';
      if (months > 0) topRow += `<div class="time-unit"><span class="time-value">${months}</span><span class="time-label">Months</span></div>`;
      if (weeks > 0) topRow += `<div class="time-unit"><span class="time-value">${weeks}</span><span class="time-label">Weeks</span></div>`;
      if (days > 0) topRow += `<div class="time-unit"><span class="time-value">${days}</span><span class="time-label">Days</span></div>`;

      let bottomRow = '';
      if (hours > 0) bottomRow += `<div class="time-unit"><span class="time-value">${hours}</span><span class="time-label">Hours</span></div>`;
      if (minutes > 0) bottomRow += `<div class="time-unit"><span class="time-value">${minutes}</span><span class="time-label">Minutes</span></div>`;
      bottomRow += `<div class="time-unit"><span class="time-value">${seconds}</span><span class="time-label">Seconds</span></div>`;

      if(topRow) {
        timerHTML += `<div class="timer-inner">${topRow}</div>`;
      }
      timerHTML += `<div class="timer-inner row-2">${bottomRow}</div>`;

      if(totalDays > 0) {
        timerHTML += `<div class="total-days">( Total ${totalDays} days left )</div>`;
      }

      countdownElement.innerHTML = timerHTML;
    }

    updateCountdown();
    countdownInterval = setInterval(updateCountdown, 1000);

  });
</script>
</body>
</html>

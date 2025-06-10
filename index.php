<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modern Photo Gallery</title>
  <style>
      * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
      }

      body {
          font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
          background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
          min-height: 100vh;
          color: #333;
      }

      .container {
          max-width: 1200px;
          margin: 0 auto;
          padding: 2rem;
      }

      .header {
          text-align: center;
          margin-bottom: 3rem;
          background: rgba(255, 255, 255, 0.95);
          backdrop-filter: blur(10px);
          padding: 2rem;
          border-radius: 20px;
          box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      }

      .header h1 {
          font-size: 2.5rem;
          margin-bottom: 1rem;
          background: linear-gradient(135deg, #667eea, #764ba2);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          background-clip: text;
      }

      .header p {
          font-size: 1.1rem;
          line-height: 1.6;
          color: #666;
          max-width: 600px;
          margin: 0 auto;
      }

      .controls {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 2rem;
          flex-wrap: wrap;
          gap: 1rem;
      }

      .checkbox-toggle {
          display: flex;
          align-items: center;
          gap: 0.5rem;
          background: rgba(255, 255, 255, 0.9);
          padding: 0.5rem 1rem;
          border-radius: 25px;
          backdrop-filter: blur(10px);
      }

      .checkbox-toggle input {
          margin: 0;
      }

      .admin-btn {
          background: rgba(255, 255, 255, 0.2);
          border: 1px solid rgba(255, 255, 255, 0.3);
          color: white;
          padding: 0.5rem 1rem;
          border-radius: 25px;
          cursor: pointer;
          backdrop-filter: blur(10px);
          transition: all 0.3s ease;
      }

      .admin-btn:hover {
          background: rgba(255, 255, 255, 0.3);
      }

      .gallery {
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
          gap: 1.5rem;
          margin-bottom: 100px;
      }

      .gallery-item {
          position: relative;
          background: rgba(255, 255, 255, 0.95);
          border-radius: 15px;
          overflow: hidden;
          box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
          transition: transform 0.3s ease, box-shadow 0.3s ease;
      }

      .gallery-item:hover {
          transform: translateY(-5px);
          box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
      }

      .gallery-item img {
          width: 100%;
          height: 200px;
          object-fit: cover;
          cursor: pointer;
      }

      .image-controls {
          position: absolute;
          top: 10px;
          right: 10px;
          display: flex;
          gap: 0.5rem;
          opacity: 0;
          transition: opacity 0.3s ease;
      }

      .gallery-item:hover .image-controls {
          opacity: 1;
      }

      .control-btn {
          background: rgba(0, 0, 0, 0.7);
          color: white;
          border: none;
          width: 35px;
          height: 35px;
          border-radius: 50%;
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
          transition: background 0.3s ease;
      }

      .control-btn:hover {
          background: rgba(0, 0, 0, 0.9);
      }

      .checkbox-overlay {
          position: absolute;
          top: 10px;
          left: 10px;
          opacity: 0;
          transition: opacity 0.3s ease;
      }

      .checkbox-overlay.show {
          opacity: 1;
      }

      .checkbox-overlay input {
          width: 20px;
          height: 20px;
      }

      .upload-btn {
          position: fixed;
          bottom: 30px;
          right: 30px;
          width: 70px;
          height: 70px;
          background: linear-gradient(135deg, #667eea, #764ba2);
          border: none;
          border-radius: 50%;
          color: white;
          font-size: 2rem;
          cursor: pointer;
          box-shadow: 0 8px 32px rgba(102, 126, 234, 0.4);
          transition: all 0.3s ease;
          z-index: 100;
      }

      .upload-btn:hover {
          transform: scale(1.1);
          box-shadow: 0 12px 40px rgba(102, 126, 234, 0.6);
      }

      .download-selected {
          position: fixed;
          bottom: 30px;
          left: 30px;
          background: #28a745;
          color: white;
          border: none;
          padding: 1rem 2rem;
          border-radius: 25px;
          cursor: pointer;
          opacity: 0;
          transform: translateY(100px);
          transition: all 0.3s ease;
          z-index: 100;
      }

      .download-selected.show {
          opacity: 1;
          transform: translateY(0);
      }

      .modal {
          display: none;
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0, 0, 0, 0.9);
          z-index: 1000;
          backdrop-filter: blur(10px);
      }

      .modal-content {
          position: relative;
          width: 100%;
          height: 100%;
          display: flex;
          align-items: center;
          justify-content: center;
      }

      .modal img {
          max-width: 90%;
          max-height: 90%;
          object-fit: contain;
      }

      .modal-nav {
          position: absolute;
          top: 50%;
          transform: translateY(-50%);
          background: rgba(255, 255, 255, 0.2);
          border: none;
          color: white;
          width: 50px;
          height: 50px;
          border-radius: 50%;
          cursor: pointer;
          font-size: 1.5rem;
          backdrop-filter: blur(10px);
          transition: background 0.3s ease;
      }

      .modal-nav:hover {
          background: rgba(255, 255, 255, 0.3);
      }

      .modal-prev {
          left: 30px;
      }

      .modal-next {
          right: 30px;
      }

      .modal-close {
          position: absolute;
          top: 30px;
          right: 30px;
          background: rgba(255, 255, 255, 0.2);
          border: none;
          color: white;
          width: 40px;
          height: 40px;
          border-radius: 50%;
          cursor: pointer;
          font-size: 1.5rem;
          backdrop-filter: blur(10px);
      }

      .admin-modal {
          display: none;
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0, 0, 0, 0.8);
          z-index: 1000;
          backdrop-filter: blur(10px);
      }

      .admin-form {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          background: white;
          padding: 2rem;
          border-radius: 15px;
          box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      }

      .admin-form h3 {
          margin-bottom: 1rem;
          text-align: center;
      }

      .admin-form input {
          width: 100%;
          padding: 0.75rem;
          margin-bottom: 1rem;
          border: 1px solid #ddd;
          border-radius: 8px;
          font-size: 1rem;
      }

      .admin-form button {
          width: 100%;
          padding: 0.75rem;
          background: linear-gradient(135deg, #667eea, #764ba2);
          color: white;
          border: none;
          border-radius: 8px;
          cursor: pointer;
          font-size: 1rem;
      }

      .upload-modal {
          display: none;
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0, 0, 0, 0.8);
          z-index: 1000;
          backdrop-filter: blur(10px);
      }

      .upload-form {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          background: white;
          padding: 2rem;
          border-radius: 15px;
          box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
          min-width: 400px;
      }

      .upload-form h3 {
          margin-bottom: 1.5rem;
          text-align: center;
      }

      .file-input-wrapper {
          position: relative;
          margin-bottom: 1.5rem;
      }

      .file-input {
          position: absolute;
          opacity: 0;
          width: 100%;
          height: 100%;
          cursor: pointer;
      }

      .file-input-label {
          display: block;
          padding: 2rem;
          border: 2px dashed #ddd;
          border-radius: 8px;
          text-align: center;
          cursor: pointer;
          transition: all 0.3s ease;
      }

      .file-input-label:hover {
          border-color: #667eea;
          background: #f8f9ff;
      }

      .upload-form button {
          width: 100%;
          padding: 0.75rem;
          background: linear-gradient(135deg, #667eea, #764ba2);
          color: white;
          border: none;
          border-radius: 8px;
          cursor: pointer;
          font-size: 1rem;
          margin-bottom: 0.5rem;
      }

      .cancel-btn {
          background: #6c757d !important;
      }

      @media (max-width: 768px) {
          .gallery {
              grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
              gap: 1rem;
          }

          .container {
              padding: 1rem;
          }

          .header h1 {
              font-size: 2rem;
          }

          .upload-btn {
              width: 60px;
              height: 60px;
              font-size: 1.5rem;
          }
      }
  </style>
</head>
<body>
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

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
  $file = $_FILES['photo'];
  $fileName = time() . '_' . basename($file['name']);
  $uploadPath = $uploadDir . $fileName;
  $thumbnailPath = $thumbnailDir . $fileName;

  if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    // Create thumbnail
    createThumbnail($uploadPath, $thumbnailPath);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
  }
}

// Handle admin login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_password'])) {
  if ($_POST['admin_password'] === $adminPassword) {
    $_SESSION['admin'] = true;
  }
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}

// Handle admin logout
if (isset($_GET['logout'])) {
  unset($_SESSION['admin']);
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}

// Handle image deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image']) && isset($_SESSION['admin'])) {
  $imageToDelete = $_POST['delete_image'];
  $imagePath = $uploadDir . $imageToDelete;
  $thumbnailPath = $thumbnailDir . $imageToDelete;

  if (file_exists($imagePath)) {
    unlink($imagePath);
  }
  if (file_exists($thumbnailPath)) {
    unlink($thumbnailPath);
  }
  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}

// Function to create thumbnail
function createThumbnail($source, $destination, $width = 400, $height = 300) {
  $info = getimagesize($source);
  $mime = $info['mime'];

  switch ($mime) {
    case 'image/jpeg':
      $image = imagecreatefromjpeg($source);
      break;
    case 'image/png':
      $image = imagecreatefrompng($source);
      break;
    case 'image/gif':
      $image = imagecreatefromgif($source);
      break;
    default:
      return false;
  }

  $originalWidth = imagesx($image);
  $originalHeight = imagesy($image);

  $ratio = min($width / $originalWidth, $height / $originalHeight);
  $newWidth = $originalWidth * $ratio;
  $newHeight = $originalHeight * $ratio;

  $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
  imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

  switch ($mime) {
    case 'image/jpeg':
      imagejpeg($thumbnail, $destination, 85);
      break;
    case 'image/png':
      imagepng($thumbnail, $destination);
      break;
    case 'image/gif':
      imagegif($thumbnail, $destination);
      break;
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
      $images[] = [
        'name' => $file,
        'time' => filemtime($uploadDir . $file)
      ];
    }
  }

  // Sort by modification time, newest first
  usort($images, function($a, $b) {
    return $b['time'] - $a['time'];
  });
}
?>

<div class="container">
  <div class="header">
    <h1>Modern Photo Gallery</h1>
    <p>
      Welcome to our beautiful photo gallery! Here you can view stunning photos uploaded by our community members.
      Feel free to browse through the collection and don't forget to contribute your own amazing photos by clicking
      the upload button. Every photo makes our gallery more vibrant and diverse!
    </p>
  </div>

  <div class="controls">
    <div class="checkbox-toggle">
      <input type="checkbox" id="enableCheckboxes" onchange="toggleCheckboxes()">
      <label for="enableCheckboxes">Enable multi-select</label>
    </div>
    <div>
      <?php if (isset($_SESSION['admin'])): ?>
        <button class="admin-btn" onclick="location.href='<?php echo $_SERVER['PHP_SELF']; ?>?logout=1'">Admin Logout</button>
      <?php else: ?>
        <button class="admin-btn" onclick="showAdminLogin()">Admin Login</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="gallery" id="gallery">
    <?php foreach ($images as $image): ?>
      <div class="gallery-item">
        <img src="<?php echo $thumbnailDir . $image['name']; ?>"
             onclick="openModal('<?php echo $uploadDir . $image['name']; ?>', <?php echo array_search($image, $images); ?>)"
             alt="Gallery Image">

        <div class="checkbox-overlay">
          <input type="checkbox" class="image-checkbox" value="<?php echo $image['name']; ?>" onchange="updateDownloadButton()">
        </div>

        <div class="image-controls">
          <button class="control-btn" onclick="downloadImage('<?php echo $uploadDir . $image['name']; ?>', '<?php echo $image['name']; ?>')" title="Download">
            ⬇
          </button>
          <?php if (isset($_SESSION['admin'])): ?>
            <button class="control-btn" onclick="deleteImage('<?php echo $image['name']; ?>')" title="Delete" style="background: rgba(220, 53, 69, 0.8);">
              🗑
            </button>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<button class="upload-btn" onclick="showUploadModal()" title="Upload Photo">+</button>
<button class="download-selected" id="downloadSelected" onclick="downloadSelected()">Download Selected</button>

<!-- Image Modal -->
<div class="modal" id="imageModal">
  <div class="modal-content">
    <button class="modal-close" onclick="closeModal()">×</button>
    <button class="modal-nav modal-prev" onclick="prevImage()">‹</button>
    <img id="modalImage" src="" alt="Full Size Image">
    <button class="modal-nav modal-next" onclick="nextImage()">›</button>
  </div>
</div>

<!-- Admin Login Modal -->
<div class="admin-modal" id="adminModal">
  <div class="admin-form">
    <h3>Admin Login</h3>
    <form method="POST">
      <input type="password" name="admin_password" placeholder="Enter admin password" required>
      <button type="submit">Login</button>
      <button type="button" onclick="hideAdminLogin()">Cancel</button>
    </form>
  </div>
</div>

<!-- Upload Modal -->
<div class="upload-modal" id="uploadModal">
  <div class="upload-form">
    <h3>Upload Photo</h3>
    <form method="POST" enctype="multipart/form-data">
      <div class="file-input-wrapper">
        <input type="file" name="photo" class="file-input" accept="image/*" required onchange="updateFileLabel(this)">
        <label class="file-input-label" id="fileLabel">
          <div>📸</div>
          <div>Click to select photo or drag & drop</div>
        </label>
      </div>
      <button type="submit">Upload Photo</button>
      <button type="button" class="cancel-btn" onclick="hideUploadModal()">Cancel</button>
    </form>
  </div>
</div>

<script>
  let currentImageIndex = 0;
  const images = <?php echo json_encode(array_column($images, 'name')); ?>;
  const imageDir = '<?php echo $uploadDir; ?>';

  function toggleCheckboxes() {
    const checkboxes = document.querySelectorAll('.checkbox-overlay');
    const isEnabled = document.getElementById('enableCheckboxes').checked;

    checkboxes.forEach(checkbox => {
      if (isEnabled) {
        checkbox.classList.add('show');
      } else {
        checkbox.classList.remove('show');
        checkbox.querySelector('input').checked = false;
      }
    });

    updateDownloadButton();
  }

  function updateDownloadButton() {
    const selectedCheckboxes = document.querySelectorAll('.image-checkbox:checked');
    const downloadBtn = document.getElementById('downloadSelected');

    if (selectedCheckboxes.length > 0) {
      downloadBtn.classList.add('show');
      downloadBtn.textContent = `Download Selected (${selectedCheckboxes.length})`;
    } else {
      downloadBtn.classList.remove('show');
    }
  }

  function openModal(imageSrc, index) {
    document.getElementById('imageModal').style.display = 'block';
    document.getElementById('modalImage').src = imageSrc;
    currentImageIndex = index;
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    document.getElementById('imageModal').style.display = 'none';
    document.body.style.overflow = 'auto';
  }

  function nextImage() {
    currentImageIndex = (currentImageIndex + 1) % images.length;
    document.getElementById('modalImage').src = imageDir + images[currentImageIndex];
  }

  function prevImage() {
    currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
    document.getElementById('modalImage').src = imageDir + images[currentImageIndex];
  }

  function downloadImage(imageSrc, fileName) {
    const link = document.createElement('a');
    link.href = imageSrc;
    link.download = fileName;
    link.click();
  }

  function downloadSelected() {
    const selectedCheckboxes = document.querySelectorAll('.image-checkbox:checked');
    selectedCheckboxes.forEach(checkbox => {
      downloadImage(imageDir + checkbox.value, checkbox.value);
    });
  }

  function deleteImage(fileName) {
    if (confirm('Are you sure you want to delete this image?')) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.innerHTML = `<input type="hidden" name="delete_image" value="${fileName}">`;
      document.body.appendChild(form);
      form.submit();
    }
  }

  function showAdminLogin() {
    document.getElementById('adminModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
  }

  function hideAdminLogin() {
    document.getElementById('adminModal').style.display = 'none';
    document.body.style.overflow = 'auto';
  }

  function showUploadModal() {
    document.getElementById('uploadModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
  }

  function hideUploadModal() {
    document.getElementById('uploadModal').style.display = 'none';
    document.body.style.overflow = 'auto';
  }

  function updateFileLabel(input) {
    const label = document.getElementById('fileLabel');
    if (input.files.length > 0) {
      label.innerHTML = `<div>📸</div><div>${input.files[0].name}</div>`;
    }
  }

  // Keyboard navigation
  document.addEventListener('keydown', function(e) {
    if (document.getElementById('imageModal').style.display === 'block') {
      if (e.key === 'ArrowLeft') {
        prevImage();
      } else if (e.key === 'ArrowRight') {
        nextImage();
      } else if (e.key === 'Escape') {
        closeModal();
      }
    }
  });

  // Close modals when clicking outside
  window.onclick = function(event) {
    const imageModal = document.getElementById('imageModal');
    const adminModal = document.getElementById('adminModal');
    const uploadModal = document.getElementById('uploadModal');

    if (event.target === imageModal) {
      closeModal();
    } else if (event.target === adminModal) {
      hideAdminLogin();
    } else if (event.target === uploadModal) {
      hideUploadModal();
    }
  }
</script>
</body>
</html>
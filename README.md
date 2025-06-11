# Our Wedding Moments - A Shared Photo Gallery

Welcome to "Our Wedding Moments," a beautiful, self-hosted, and mobile-first photo gallery designed for sharing cherished memories from a special day. This project allows wedding guests to easily upload their captured moments and view a collective album of photos from everyone who attended. It also includes a simple, password-protected admin panel for managing the gallery content.

This project is built with **PHP** and requires no database, making it incredibly lightweight and easy to set up on any standard web server.

---

## Key Features

This gallery is packed with features designed to provide a seamless and enjoyable experience for both guests and administrators.

### 🖼️ Guest & User Experience
* **Elegant & Personal Header:** A beautifully designed header featuring the couple's names and a welcome message, fully optimized for mobile devices.
* **Dynamic Parallax Background:** Subtle, animated background elements create a delightful and immersive atmosphere.
* **Responsive Photo Grid:** The gallery displays all photos in a responsive grid that looks great on any screen size, from phones to desktops.
* **Interactive Modal Viewer:** Clicking on any photo opens it in a full-screen modal, allowing guests to view images in high detail.
    * **Easy Navigation:** Guests can navigate between photos using on-screen arrows or keyboard keys.
    * **Click-Outside-to-Close:** The modal can be closed by clicking the background or pressing the 'Escape' key.
    * **In-Modal Actions:** Users can download the currently viewed photo directly from the modal.
* **Seamless Multi-Photo Uploader:** An intuitive drag-and-drop (or click) uploader allows guests to select and upload multiple photos at once.
* **Multi-Select Download:** Guests can enable a "multi-select" mode to choose several photos from the gallery grid and download them all in a single action.
* **User-Friendly Notifications:** Non-intrusive "toast" notifications confirm successful uploads or provide feedback on actions.
* **Clean URLs:** The gallery uses `.htaccess` to provide clean, user-friendly URLs (e.g., `yourwebsite.com/` instead of `yourwebsite.com/index.php`).

### ⚙️ Admin Features
* **Password-Protected Login:** A simple modal provides access to the admin functions, secured by a single, easy-to-change password.
* **Conditional UI:** The main "Share Moments" upload button is hidden when the admin is logged in, preventing confusion.
* **Single Photo Deletion:** Admins can delete individual photos directly from the gallery grid or within the full-screen modal viewer.
* **Multi-Photo Deletion:** The "multi-select" mode allows admins to choose and delete multiple photos at once, with a confirmation prompt to prevent accidental deletions.
* **Clear Logout:** A visible "Admin Logout" button allows for securely ending the admin session.

### 🚀 Technical Features
* **Automatic Thumbnail Generation:** On upload, the application automatically creates smaller, optimized thumbnails for each photo. This ensures the main gallery page loads quickly, improving performance.
* **Lightweight & Server-Friendly:** Built with plain PHP, it runs on almost any web server with PHP support and does not require a database.
* **Secure File Handling:** Uses `basename()` to sanitize file paths during uploads and deletions to prevent path traversal vulnerabilities.

---

## 🛠️ Setup & Configuration

Setting up your own "Wedding Moments" gallery is simple.

1.  **Upload Files:** Place the `index.php` and `.htaccess` files in the root directory of your web server.
2.  **Create Directories:** Create two folders in the same directory:
    * `images/` - This is where the full-resolution uploaded photos will be stored.
    * `thumbnails/` - This is where the auto-generated thumbnails will be stored.
    * **Important:** Ensure both directories have write permissions (e.g., `chmod 755` or `chmod 777`, depending on your server configuration) so that the PHP script can save files.
3.  **Set Your Admin Password:** Open the `index.php` file and change the password on this line:
    ```php
    $adminPassword = 'admin123'; // Change this password!
    ```
4.  **Customize Content (Optional):**
    * To change the names and welcome message, edit the content within the `<div class="header">` section in `index.php`.
    * You can replace the placeholder image URL in the header with a real photo of the couple.

---

## ✉️ Contact

This project was created with love and care. If you are thinking of using this repository for your own event or have any questions, please feel free to reach out to me.

**Dipanjan Kundu**, Email: **dipsraj.kundu@gmail.com**

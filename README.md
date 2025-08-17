# Secure-Image-Uploads-in-PHP
A step-by-step example of secure file uploads in PHP — protecting against malicious files, huge image uploads, and filename collisions. This repo demonstrates best practices like CSRF protection, MIME-type validation, re-encoding images, and safe storage.

✨ Features
✅ CSRF protection for upload forms
✅ Validate MIME types with finfo
✅ Block dangerous files (.php, .exe, etc.)
✅ Resize oversized images (configurable max width/height)
✅ Strip EXIF + re-encode with GD (JPEG, PNG, WebP)
✅ Randomized file names (no overwrite)
✅ Secure permissions (0640)

📂 Structure
/config.php        # Configuration (max size, allowed types, output format)
/public/upload.php # Secure upload endpoint
/uploads_raw/      # Temporary holding area (never public)
/uploads/          # Final processed images

🚀 Getting Started
Clone repo
Configure paths + settings in config.php
Deploy public/upload.php behind a form with CSRF token
Uploaded files are validated, resized, re-encoded, and stored safely

🎯 Why?
Improper file uploads are one of the most common vulnerabilities in PHP apps.
This project shows how to do it right, and is great for teaching junior developers about secure coding.

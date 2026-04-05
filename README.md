# EduSphere - Learning Management System

EduSphere is a role-based Learning Management System (LMS) project with separate modules for admin, instructor, and student workflows.

## Static GitHub Pages Version

This repository now includes a static homepage at `index.html` so it can be published with GitHub Pages.

- GitHub Pages supports static content (HTML/CSS/JS).
- Dynamic PHP and MySQL features are not available on GitHub Pages.

## Publish on GitHub Pages

1. Push this repository to GitHub.
2. Open repository settings.
3. Go to **Pages**.
4. Under **Build and deployment**, choose:
   - **Source**: Deploy from a branch
   - **Branch**: `main`
   - **Folder**: `/ (root)`
5. Save and wait 1-2 minutes.
6. Open your Pages URL:
   - `https://<your-username>.github.io/EduSphere-A-Complete-Learning-Management-System/`

## Run Full Dynamic Version (PHP + MySQL)

For full LMS functionality (login, enrollment, quizzes, dashboard data):

1. Host on a PHP server (Apache/Nginx + PHP).
2. Create and import MySQL database.
3. Update database and site settings in `includes/config.php`.

Free PHP hosts can be used for full functionality, while GitHub Pages can be used for the static showcase.

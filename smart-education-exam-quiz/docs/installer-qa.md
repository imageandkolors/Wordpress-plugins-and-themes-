# Installer QA and Manual Testing Steps

## 1. Fresh Install and Activation
- On a fresh WordPress installation, install and activate the "Smart Education Exam & Quiz" plugin.
- Upon activation, you should be automatically redirected to the installer page (`/wp-admin/index.php?page=se-installer`).

## 2. Installer Wizard - Scratch Mode
- In the installer, click "Start Setup".
- On the "Choose Your Setup Mode" screen, select "Build from Scratch".
- On the "Configure Your Settings" screen, check the "Import Demo Data?" checkbox and click "Start Import".
- The progress screen should appear and show the import process.
- The process should finish with the "Setup Complete!" screen.
- Verify that you are redirected to the main dashboard.
- Check that sample exams and questions have been created.

## 3. Installer Wizard - Template Mode
- Deactivate and reactivate the plugin to re-run the installer (or use the "Re-run installer" link if available).
- In the installer, select "Use a Premade Template".
- On the "Select a Template" screen, you should see the three templates.
- For each template ("Modern Academic", "Creative Learning", "Professional LMS"):
    - Click "Preview". A modal should appear with template details.
    - Click "Import".
    - On the settings screen, ensure "Import Demo Data?" is checked and start the import.
    - The progress screen should appear.
    - The import will fail with a message about manual import. Verify that a download link for the template's JSON file is provided in the log.
    - Verify that the demo content (exams and questions) is still imported successfully.

## 4. Manual Template Import
- Download one of the template JSON files from the link provided in the installer log.
- In the WordPress admin, go to "Elementor > Tools > Import / Export".
- Click "Start Import" and upload the downloaded JSON file.
- Verify that the template is imported successfully.

## 5. Verify Frontend
- Create a new page and edit it with Elementor.
- Add the "Exam" widget to the page.
- Select one of the sample exams.
- View the page on the frontend and verify that the exam displays correctly and is functional.
- Test the exam-taking process, including timers, navigation, and submission.
- Verify that the styling matches the theme of the imported template (if applicable).

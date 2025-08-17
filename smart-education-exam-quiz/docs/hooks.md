# Developer Hooks

This document provides a list of the custom action and filter hooks available in the Smart Education Exam & Quiz plugin.

## Action Hooks

### `se_before_installer_start`
- **Description:** Fires just before the installer job is created and scheduled.
- **Parameters:**
    - `$args` (array): The arguments passed to the installer, including mode, template, and settings.
- **Location:** `includes/rest/class-se-installer-rest.php`

### `se_before_template_import`
- **Description:** Fires just before the template import process begins.
- **Parameters:**
    - `$template_id` (string): The ID of the template being imported.
    - `$job_id` (string): The ID of the installer job.
- **Location:** `includes/templates/importer.php`

### `se_after_template_import`
- **Description:** Fires after the template import process has completed (or in this case, after the manual download has been queued).
- **Parameters:**
    - `$template_id` (string): The ID of the template.
    - `$job_id` (string): The ID of the installer job.
    - `$result` (array): The result of the import process.
- **Location:** `includes/templates/importer.php`

### `se_template_import_failed`
- **Description:** Fires if an exception is caught during the template import process.
- **Parameters:**
    - `$template_id` (string): The ID of the template.
    - `$job_id` (string): The ID of the installer job.
    - `$error` (Exception): The exception object.
- **Location:** `includes/templates/importer.php`

### `se_after_installer_finish`
- **Description:** Fires after the entire installer job has finished, either successfully or with an error.
- **Parameters:**
    - `$job_id` (string): The ID of the installer job.
    - `$success` (bool): Whether the installer finished successfully.
- **Location:** `includes/templates/importer.php`


## Filter Hooks

### `se_installer_template_list`
- **Description:** Filters the list of templates displayed in the installation wizard.
- **Parameters:**
    - `$templates_array` (array): An array of template arrays, each with `id`, `name`, and `features`.
- **Location:** `includes/rest/class-se-installer-rest.php`

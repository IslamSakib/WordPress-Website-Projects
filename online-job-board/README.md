# Job Board Plugin

A WordPress plugin for managing job listings and applications with resume uploads.

## Installation

### Method 1: Upload via WordPress Admin

1. Download the plugin zip file
2. Go to **WordPress Admin** → **Plugins** → **Add New**
3. Click **Upload Plugin**
4. Choose the zip file and click **Install Now**
5. Click **Activate Plugin**

### Method 2: Manual Installation

1. Extract the plugin folder
2. Upload the `online-job-board` folder to `/wp-content/plugins/`
3. Go to **WordPress Admin** → **Plugins**
4. Find "Job Board by Sakib Islam" and click **Activate**

The plugin automatically creates the required database table upon activation.

## Creating Job Listings

1. Go to **Job Board** → **Add New Job Listing** in your WordPress admin
2. Enter the job title
3. Add the job description in the editor
4. Fill in the **Job Details** section:
   - Company Name (required)
   - Company Address (required)
   - Application Deadline (required)
   - Job Closing Date (required)
5. Optionally add a featured image
6. Click **Publish**

## Displaying Jobs

Use the shortcode `[job_board]` on any page or post to display the job listings.

### Basic Usage

```
[job_board]
```

This displays 6 jobs per page by default.

### Custom Jobs Per Page

```
[job_board per_page="9"]
```

### Example

Create a page called "Careers" and add:

```
[job_board per_page="6"]
```

## Managing Applications

### Viewing Applications

1. Go to **Job Board** → **Applications**
2. View all applications in a table format
3. Use the dropdown filter to view applications for specific jobs
4. Click **Filter** to apply

### Application Actions

- **View** - See full application details including cover letter
- **Resume** - Download the applicant's PDF resume
- **Delete** - Remove an application

## Features

- Custom job listing post type
- React-based frontend interface
- Search functionality for jobs
- Pagination support
- Application form with file upload
- Email notifications to applicants
- Admin dashboard for managing applications
- Responsive design for all devices
- Deadline and closing date tracking

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher

## Support

For issues or questions, contact the plugin author.

## License

GPL v2 or later
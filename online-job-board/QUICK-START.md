# Quick Start Guide - Online Job Board

**Author:** Sakib Islam | **Contact:** +880 195 002 5990

---

## 🚀 Quick Installation

1. Upload `online-job-board` folder to `/wp-content/plugins/`
2. Activate from **Plugins** menu in WordPress
3. You're done! Database table created automatically

---

## 📝 Creating Your First Job

**Step by Step:**

1. **Navigate:** Admin → **Job Board** → **Add New Job Listing**

2. **Add Job Title:**
   ```
   Example: "Senior WordPress Developer"
   ```

3. **Add Description:**
   ```
   We are looking for an experienced WordPress developer...
   (Add full job description here)
   ```

4. **Fill Job Details:**
   - **Company Name:** `Tech Solutions Inc.`
   - **Company Address:** `123 Main Street, Dhaka, Bangladesh`
   - **Application Deadline:** Select a future date

5. **Publish** the job

---

## 🎯 Using the Shortcode

### Basic Usage

On any page or post, add:

```
[job_board]
```

This displays 10 jobs per page by default.

### Custom Posts Per Page

```
[job_board per_page="15"]
```

### Example Page Setup

**Page Title:** Careers

**Page Content:**
```html
<h1>Join Our Team</h1>
<p>Explore exciting career opportunities at our company.</p>

[job_board per_page="12"]

<h2>Why Work With Us?</h2>
<ul>
  <li>Competitive salary</li>
  <li>Flexible working hours</li>
  <li>Growth opportunities</li>
</ul>
```

---

## 👥 How Candidates Apply

1. User visits job board page
2. Clicks **"Apply Now"** on any job
3. Fills application form:
   - Full Name
   - Email Address
   - Phone Number
   - Cover Letter
   - Resume (PDF only, max 5MB)
4. Clicks **"Submit Application"**
5. Receives email confirmation

---

## 📊 Managing Applications

### View All Applications

1. Go to **Job Board** → **Applications**
2. See list of all applicants

### Filter by Job

1. Use dropdown at top
2. Select specific job
3. Click **"Filter"**

### View Application Details

1. Click **"View"** button
2. See full details including cover letter
3. Click **"Resume"** to download PDF

### Delete Application

1. Click **"Delete"** button
2. Confirm deletion

---

## 🔍 Search Functionality

Users can search jobs using the search box:
- Searches job titles
- Searches company names
- Searches descriptions

---

## 📧 Email Notifications

**Automatic emails sent to candidates include:**
- Confirmation message
- Job details (title, company, location)
- Application summary
- Submission date/time

**Configure email sender:**
- WordPress admin → **Settings** → **General**
- Set correct email address

**For better email delivery, install:**
- WP Mail SMTP plugin
- Configure SMTP settings

---

## ⚙️ Configuration Tips

### Increase Upload Limit

If 5MB limit is too small, edit `php.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Customize Colors

Add to your theme's CSS:

```css
/* Change apply button color */
.ojb-apply-btn {
    background: #your-color !important;
}
```

### Email Test

Test if emails work:

```php
// Add to functions.php temporarily
wp_mail('your-email@example.com', 'Test', 'This is a test');
```

---

## 🔒 Security Features

✅ Nonce verification on all forms
✅ Input sanitization (all user inputs)
✅ Output escaping (all displayed data)
✅ SQL injection protection
✅ PDF-only resume validation
✅ File size limit enforcement
✅ Duplicate application prevention

---

## 🐛 Common Issues & Solutions

### Issue: Jobs not showing

**Solution:**
- Check jobs are published
- Verify shortcode: `[job_board]`
- Clear cache

### Issue: Application form not submitting

**Solution:**
- Check browser console for errors
- Ensure jQuery is loaded
- Test with default theme

### Issue: Emails not received

**Solution:**
- Check spam folder
- Install WP Mail SMTP plugin
- Test wp_mail() function

### Issue: PDF upload fails

**Solution:**
- Check file is actually PDF
- Verify file size under 5MB
- Check uploads folder is writable

---

## 📱 Responsive Design

The plugin is fully responsive:
- ✅ Mobile phones
- ✅ Tablets
- ✅ Desktops
- ✅ All screen sizes

---

## 🎨 Styling Examples

### Change Card Background

```css
.ojb-job-card {
    background: #f9f9f9;
    border: 2px solid #e0e0e0;
}
```

### Custom Search Button

```css
.ojb-search-btn {
    background: #ff6600;
    border-radius: 20px;
}
```

### Modal Style

```css
.ojb-modal-dialog {
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.4);
}
```

---

## 📞 Support

**Developer:** Sakib Islam
**Mobile:** +880 195 002 5990

For technical support or custom development, feel free to contact.

---

## ✅ Checklist: Before Going Live

- [ ] Created at least 3 test jobs
- [ ] Tested job board shortcode on a page
- [ ] Submitted a test application
- [ ] Verified email notification received
- [ ] Checked applications in admin panel
- [ ] Tested on mobile device
- [ ] Verified search functionality
- [ ] Tested pagination (if >10 jobs)
- [ ] Checked expired job display
- [ ] Reviewed application deletion

---

**That's it! You're ready to launch your job board! 🎉**

For detailed documentation, see [README.md](README.md)
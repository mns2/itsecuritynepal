# Admin guide (for IT Security Nepal staff)

Sign in at **https://itsecuritynepal.com/admin** with your staff account. Use "Forgot password" on the login page if needed.
New staff accounts: ask the developer to run `php artisan make:filament-user`.

## Dashboard
The first screen after login shows: **Ongoing classes**, **Students studying now**, **Students completed** (with certified count),
**New leads**, a weekly leads-vs-enrolments chart, leads by training type (Individual / Group / Corporate Batch),
ongoing and upcoming classes, and current leads (New or Contacted). Click any number to open the matching list.
The numbers come from Training → Classes and Training → Students, so keep those up to date.

## Training (Training → Classes, Students)
- **Classes:** add each real running class (course, name, trainer, schedule, start/end dates, seats). Set status to *Upcoming*, *Ongoing*, *Completed* or *Cancelled*. Open a class to add students or mark several as completed at once. Classes are internal and never shown on the website.
- **Students:** one row per person per course. Status *Studying*, *Completed* (set the completion date and tick *Passed certification exam* if they passed) or *Dropped*.
- **From a lead:** open an enquiry and press **Enrol as student**. It creates the student (optionally in a class) and marks the enquiry *Enrolled*.

## Daily: enquiries (Leads → Enquiries)
- New website enquiries appear here (the number badge shows how many are **New**) and are also emailed to the admissions inbox.
- Open an enquiry to see the course, whether it is for an **Individual** or a **Group** (with group size), preferred mode and message.
- Click the phone or email to contact the person, then set **Status** (Contacted, Enrolled, Closed) and add **Staff notes**.
- Spam: select rows → *Mark as spam*.

## Weekly: batches (Catalogue → Batches)
- Add each upcoming batch: course, start (e.g. "3 Nov 2026" or "Every Monday"), schedule, mode and status (Enrolling, Few seats left, Opening soon, Waitlist).
- Unpublish batches that have started. Batches appear on the course page, the homepage and `/batches/`.

## Courses and tracks (Catalogue)
- **Courses:** edit text in tabs (Basics, Content, Curriculum, FAQs, SEO). Drag to reorder modules. Toggle **Featured** for the homepage, **Published** to show or hide.
- Keep the **slug** unchanged for existing courses (it is the web address Google knows).
- **Tracks** group courses (Red Hat & Linux, Cybersecurity…). "Homepage tile" controls the six tiles on the homepage.
- **Learning paths:** the step-by-step course sequences shown on the homepage and track pages.

## Page content (Content → Page content)
Every heading, paragraph, button label and photo on the public pages is edited here, one tab per page:
Header & footer (menu links, footer text, enquiry pop-up), Homepage (hero, popular searches, featured courses, journey,
why-us, call to action…), About, Group training, Corporate training, Training methods, Course page blocks,
Page introductions (Courses, Blog, Batches, FAQ, Contact, Instructors) and Course Finder.
- Open a section, change the text or upload a photo, then press **Save changes**. Changes are live at once.
- Photos: JPG/PNG/WebP up to 4 MB, landscape, at least 1000 px wide. Always fill the matching *Alt* field (describes the photo for screen readers and Google).
- Lists (menu links, chips, cards) can be added, removed and dragged to reorder.
- To undo everything on a page, ask the developer to delete the `page_<name>` row in the settings table; the built-in defaults come back.

**Photos elsewhere:** Tracks have a *Track photo* (used on that track's course cards); a course can have its own *Card photo*; blog posts have a *Featured image*.
The photos shipped with the site are free Unsplash stock placeholders. Replace them with real classroom/lab photos when available.

## Content
- **Blog posts:** write in Markdown, add a featured image, choose related courses. Leave *Publish at* empty to save a draft.
- **FAQs:** grouped questions for `/faq/`; switch on *Show on homepage* for up to six.
- **Instructors / Testimonials:** publish only real, approved profiles and reviews. A testimonial shows only when *Consent recorded* **and** *Published* are both on.
- **Legal pages:** paste the approved Privacy Policy and Terms.

## Settings
- **Site settings:** offer bar (on/off, text, button), phone numbers, WhatsApp, email, address, hours, social links, key facts (switch on *Verified* to show a figure), "Training for" texts, Google Analytics ID.
- **Redirects:** when a page address changes or an old link appears in Search Console as "not found", add `/old-address/` → `/new-address/`.

# Admin guide (for IT Security Nepal staff)

Sign in at **https://itsecuritynepal.com/admin** with your staff account. Use "Forgot password" on the login page if needed.
New staff accounts: ask the developer to run `php artisan make:filament-user`.

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

## Content
- **Blog posts:** write in Markdown, add a featured image, choose related courses. Leave *Publish at* empty to save a draft.
- **FAQs:** grouped questions for `/faq/`; switch on *Show on homepage* for up to six.
- **Instructors / Testimonials:** publish only real, approved profiles and reviews. A testimonial shows only when *Consent recorded* **and** *Published* are both on.
- **Legal pages:** paste the approved Privacy Policy and Terms.

## Settings
- **Site settings:** offer bar (on/off, text, button), phone numbers, WhatsApp, email, address, hours, social links, key facts (switch on *Verified* to show a figure), "Training for" texts, Google Analytics ID.
- **Redirects:** when a page address changes or an old link appears in Search Console as "not found", add `/old-address/` → `/new-address/`.

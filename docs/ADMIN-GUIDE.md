# Admin guide (for IT Security Nepal staff)

Sign in at **https://itsecuritynepal.com/admin** with your staff account. Use "Forgot password" on the login page if needed.
New staff accounts: ask the developer to run `php artisan make:filament-user`.

## Dashboard
The first screen after login shows: **Ongoing classes**, **Students studying now**, **Students completed** (with certified count),
**New leads**, a weekly leads-vs-enrolments chart, leads by training type (Individual / Group / Corporate Batch),
ongoing and upcoming classes, and current leads (New or Contacted). Click any number to open the matching list.
The numbers come from Training → Classes and Training → Students, so keep those up to date.

## Staff accounts and roles (Settings → Staff accounts, admins only)
Each person gets their own login. What they see depends on their role:

| Role | Signs in at | Can use |
|---|---|---|
| Admin | /admin | Everything, including Site settings and Staff accounts |
| Manager | /admin | Everything except Site settings and Staff accounts |
| Front desk / counsellor | /admin | Enquiries, classes, students, attendance, fees |
| Marketing | /admin | Enquiries, campaigns, subscribers, email campaigns, content, SEO |
| Content editor | /admin | Page content, blog, FAQs, courses, tracks, career pathways, SEO |
| Instructor | /trainer | Only their own classes: students and attendance |

Switch off *Can sign in* to block someone without deleting them. People change their own password under their profile (top-right menu).

## Instructors and attendance (the full flow)
1. **Instructor account:** Settings → Staff accounts → new account with role *Instructor*.
2. **Student enrols → batch:** Enquiries → *Enrol as student* → choose the batch, or press **+** to create one: name, start date, **instructor**, **class days** and **class time**. (Batches can also be set up in Training → Classes.)
3. **During class:** the instructor signs in at **/trainer**. *Today's classes* lists their batches for today with the time ("Class in progress" during class) and whether attendance is done. **Take attendance** opens a table of the batch's students: tap **Present**, **Late**, **Absent** or **Excused** for each (or *Mark all present* and change the few who are not), add a note or the topic, **Save**. Students left unmarked are saved as absent. Instructors can only take attendance for **today**; opening it again the same day lets them correct it.
4. **Records:**
   - **Training → Attendance register:** pick a batch (and optionally a date range) to see a table of student names × class dates (P / L / A / E), totals and attendance % (below 75% in red). *Export CSV* for reports.
   - Each **class** has an *Attendance* tab (every date: present count, who took it, details, edit).
   - Each **student** has an *Attendance* tab (their full history) and the Students list shows attendance %.
   - Admins and front desk staff can take or correct attendance for any past date (Training → Take attendance → choose the date).

## Enrolling a student and fees
- From an enquiry: **Enrol as student** asks for the course, the **batch / class** (press + to create a new batch), the fee (course fee, discount and reason, amount paid now, payment method, receipt no., balance due date) and requires ticking **Fee and payment plan confirmed with the student**. The enquiry becomes *Enrolled*.
- Later payments: open the student → **Payments** → *Record payment* (amount, date, method, receipt). Paid and balance update automatically.
- **Training → Fee collection** lists every payment with totals for any period, method, course or batch (filters at the top, *Group by* for daily, per method or per batch breakdowns), plus today / this month / this year / outstanding balances. *Export CSV* for accounts.
- Students list: filter *Balance due* or *Fee confirmed*.

## Walk-in and phone enquiries
Leads → Enquiries → **Add enquiry**: choose how they came in (walk-in, phone, WhatsApp, social, event, referral), then follow up and enrol as usual.

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

## Marketing
- **Campaigns (Leads → Campaigns):** offer pop-up (poster image or text card) with button, optional countdown and offer code, plus a landing page at /offer/…/ for ads. Choose pages, devices, when it appears (after seconds, after scrolling, or when leaving) and how often. *Preview* shows it to you only. Views, clicks and leads are counted; leads show the campaign name.
- **Pop-up switches (Settings → Site settings → Pop-ups):** master switch for campaign pop-ups, the **Course Finder pop-up** (default: after 5 seconds, once a day) and the **newsletter pop-up** and footer sign-up. Only one pop-up shows per page view.
- **Subscribers (Leads → Subscribers):** people who signed up (pop-up, footer, offer page, or the optional tick box on the enquiry form). New sign-ups confirm by email first. Never add people who did not ask; *Export CSV* is available.
- **Email campaigns (Leads → Email campaigns):** write the email, *Preview*, *Send test* to yourself, then *Send* now or at a time. **Keep the Email campaigns page open while it sends**: the box at the top shows how many are left; you can close it and come back, sending continues where it stopped. Speed (emails per minute) is in Site settings → Pop-ups → Newsletter; keep it under your hosting limit. If the developer adds the optional server cron, the box says *Automatic sending on* and the page no longer needs to stay open. Every email has an unsubscribe link.
- **Google reviews (Settings → Site settings → Google reviews):** shows your real Google rating and recent reviews once the Place ID is entered and the API key is set on the server. *Test connection and refresh* checks it.

## SEO
- Every course, track, blog post, career pathway, campaign, training method and legal page has an **Advanced SEO** section at the bottom of its editor. Other pages (home, about, contact, listings) are under **Content → SEO (all pages)**.
- Fields: SEO title and description (with length counters, focus-keyword checklist and Google preview), share title/description/image, canonical URL, search-engine visibility (noindex pages also leave the sitemap), and **custom schema (JSON-LD)** with templates for FAQ, event, course offer, how-to, video and local business. Test with Google's Rich Results Test (button in the SEO list).
- Leave fields empty to keep the automatic values. Do not add review/rating schema about your own institute (Google ignores or penalises self-written reviews).

## Image sizes
Every photo field shows its recommended size next to the label (e.g. *1280 × 720 px (16:9)*). Photos are cropped to fit, so keep faces and text in the centre.

## Content
- **Blog posts:** write in Markdown, add a featured image, choose related courses. Leave *Publish at* empty to save a draft.
- **FAQs:** grouped questions for `/faq/`; switch on *Show on homepage* for up to six.
- **Instructors / Testimonials:** publish only real, approved profiles and reviews. A testimonial shows only when *Consent recorded* **and** *Published* are both on.
- **Legal pages:** paste the approved Privacy Policy and Terms.

## Settings
- **Site settings:** offer bar (on/off, text, button), phone numbers, WhatsApp, email, address, hours, social links, key facts (switch on *Verified* to show a figure), "Training for" texts, Google Analytics ID.
- **Redirects:** when a page address changes or an old link appears in Search Console as "not found", add `/old-address/` → `/new-address/`.

## Red Hat exam bookings

Public page: `/red-hat-exam-booking-nepal/` (linked from the Red Hat & Linux track page, listed in the sitemap).

**Setup (admin only): Settings → Site settings → Exam booking**
- Upload the **official payment QR code** (the original PNG/JPG from the bank or wallet app). Until a QR is uploaded, the page shows "Payment QR code coming soon" and the form stays closed.
- After saving, scan the QR on the live page with a phone and check it opens the right account.
- Fee (default NPR 35,000), optional text under the QR (e.g. account name), and the list of exams on the form.

**Daily work: Leads → Exam bookings** (admin, manager, front desk). The badge shows how many are pending.
1. Open a booking with **Review**. The payment screenshot is on the right; open it full size to read the amount, date and transaction ID.
2. Find the payment in the account statement, then click **Payment verified**. If it can't be found, click **Payment not confirmed** and give the reason.
3. Book the exam on Red Hat's side using the candidate's Red Hat login and account email, then click **Exam booked** (with the exam date if known) and contact the candidate.

Payment screenshots are stored privately (`storage/app/private/exam-payments/`), never in the public folder, and only signed-in admin, manager and front-desk staff can open them. Deleting a booking also deletes its screenshot. The website never asks for Red Hat passwords.

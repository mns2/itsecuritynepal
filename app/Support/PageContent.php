<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

/**
 * Editable page content. Every text block and photo slot on the public pages lives here with a default
 * (the approved design copy + stock photos). Staff override any value in Admin → Settings → Page content;
 * overrides are stored per page in the `settings` table as `page_{name}`.
 *
 * Conventions used by the admin editor (App\Filament\Pages\PageContentEditor) to build fields automatically:
 *  - keys ending in "image"  → image upload (public disk)      - keys "body"/"intro"/"text"/"lead"/"answer" → textarea
 *  - list of strings          → simple repeater                - list of objects → repeater with the object's fields
 *  - nested object            → collapsible section            - booleans → toggle
 */
class PageContent
{
    /** Human labels for the page tabs in the admin editor (order = tab order). */
    public const PAGES = [
        'global' => 'Header & footer',
        'home' => 'Homepage',
        'about' => 'About',
        'group' => 'Group training',
        'corporate' => 'Corporate training',
        'training_methods' => 'Training methods',
        'course_page' => 'Course page blocks',
        'listings' => 'Page introductions',
        'finder' => 'Course Finder',
    ];

    private static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        [$page, $path] = array_pad(explode('.', $key, 2), 2, null);
        $data = self::page($page);

        return $path === null ? $data : data_get($data, $path, $default);
    }

    /** Defaults merged with saved overrides. Lists are replaced wholesale, objects merged key by key. */
    public static function page(string $page): array
    {
        if (array_key_exists($page, self::$cache)) {
            return self::$cache[$page];
        }
        $defaults = self::defaults()[$page] ?? [];
        $saved = Setting::get('page_'.$page, []) ?: [];

        return self::$cache[$page] = self::merge($defaults, $saved);
    }

    public static function flush(): void
    {
        self::$cache = [];
    }

    public static function merge(array $defaults, array $saved): array
    {
        if (array_is_list($defaults) || array_is_list($saved)) {
            return $saved === [] && $defaults !== [] ? $defaults : $saved;
        }
        foreach ($saved as $k => $v) {
            $defaults[$k] = (is_array($v) && is_array($defaults[$k] ?? null)) ? self::merge($defaults[$k], $v) : $v;
        }

        return $defaults;
    }

    /** Public URL for an image value: an uploaded/stock path on the public disk, or an absolute URL. */
    public static function imageUrl(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return str_starts_with($value, 'http') ? $value : Storage::disk('public')->url($value);
    }

    public static function defaults(): array
    {
        return [
            'global' => [
                'header_nav' => [
                    ['label' => 'Upcoming Batches', 'url' => '/batches/', 'icon' => 'calendar-days', 'tag' => ''],
                    ['label' => 'Red Hat', 'url' => '/redhat/', 'icon' => 'terminal', 'tag' => ''],
                    ['label' => 'Bootcamp', 'url' => '/bootcamp/', 'icon' => 'laptop', 'tag' => 'New'],
                    ['label' => 'Career Paths', 'url' => '/career-pathways/', 'icon' => 'signpost', 'tag' => ''],
                    ['label' => 'How We Train', 'url' => '/mode-of-training/', 'icon' => 'route', 'tag' => ''],
                    ['label' => 'About', 'url' => '/about/', 'icon' => 'award', 'tag' => ''],
                    ['label' => 'Blog', 'url' => '/blog/', 'icon' => 'book-open-check', 'tag' => ''],
                    ['label' => 'Contact', 'url' => '/contact/', 'icon' => 'phone', 'tag' => ''],
                ],
                'header_button' => 'Send Inquiry',
                'mobile_quick_links' => [
                    ['label' => 'Upcoming', 'url' => '/batches/'],
                    ['label' => 'Bootcamp', 'url' => '/bootcamp/'],
                    ['label' => 'Red Hat', 'url' => '/redhat/'],
                    ['label' => 'Group training', 'url' => '/group-training/'],
                    ['label' => 'Course Finder', 'url' => '/course-finder/'],
                ],
                'footer_text' => 'Network | System | Cybersecurity | Red Hat | DevOps specialised training in Kathmandu. Red Hat Authorized Training Partner for 15+ years.',
                'footer_popular' => [
                    ['label' => 'RH124 · RHCSA path', 'url' => '/red-hat-system-administration/'],
                    ['label' => 'Ethical Hacking Expert', 'url' => '/ethical-hacking-expert/'],
                    ['label' => 'CCNA', 'url' => '/cisco-certified-network-associate/'],
                    ['label' => 'CKA', 'url' => '/certified-kubernetes-administrator/'],
                    ['label' => 'Linux Bootcamp', 'url' => '/linux-bootcamp/'],
                    ['label' => 'Job Ready (AU/NZ)', 'url' => '/job-ready/'],
                ],
                'footer_institute' => [
                    ['label' => 'Individual training', 'url' => '/course/'],
                    ['label' => 'Group training', 'url' => '/group-training/'],
                    ['label' => 'Corporate training', 'url' => '/corporate-training/'],
                    ['label' => 'Training methods', 'url' => '/mode-of-training/'],
                    ['label' => 'Career pathways', 'url' => '/career-pathways/'],
                    ['label' => 'Upcoming batches', 'url' => '/batches/'],
                    ['label' => 'About us', 'url' => '/about/'],
                    ['label' => 'Instructors', 'url' => '/instructors/'],
                    ['label' => 'Blog', 'url' => '/blog/'],
                    ['label' => 'FAQ', 'url' => '/faq/'],
                ],
                'copyright_note' => 'Red Hat® and other certification names are trademarks of their respective owners.',
                'enquiry_dialog' => [
                    'eyebrow' => 'Free course advice',
                    'title' => 'Enquire about a course',
                    'intro' => "Tell us what you're interested in. An advisor will call you back, usually the same day.",
                ],
                'enquiry_success' => 'An advisor will contact you within one working day.',
            ],

            'home' => [
                'seo' => [
                    'title' => 'IT Training Institute in Kathmandu | Red Hat, Cybersecurity, CCNA & DevOps | IT Security Nepal',
                    'description' => 'Hands-on IT training in Kathmandu from a Red Hat Authorized Training Partner. RHCSA, RHCE, ethical hacking, CCNA, Kubernetes and Azure courses for individuals, groups and corporate batches: classroom or live online.',
                ],
                'hero' => [
                    'badge' => 'Red Hat Authorized Training Partner · 15+ years',
                    'title' => 'Build your future in IT with',
                    'title_highlight' => 'industry-driven training',
                    'lead' => 'Develop practical skills in cybersecurity, Linux, networking, cloud and DevOps through hands-on, certification-focused training, in Kathmandu or live online.',
                    'search_placeholder' => 'What do you want to learn?',
                    'search_button' => 'Explore Courses',
                    'popular' => [
                        ['label' => 'RHCSA', 'url' => '/red-hat-system-administration-ii/'],
                        ['label' => 'Ethical Hacking', 'url' => '/ethical-hacking-expert/'],
                        ['label' => 'CCNA', 'url' => '/cisco-certified-network-associate/'],
                        ['label' => 'CKA', 'url' => '/certified-kubernetes-administrator/'],
                        ['label' => 'AZ-104', 'url' => '/microsoft-azure-administrator/'],
                    ],
                    'advisor_button' => 'Talk to a Training Advisor',
                    'finder_link' => 'Not sure? Try the Course Finder',
                    'main_image' => 'stock/home-hero-main.webp',
                    'main_image_alt' => 'Students working at computers in a computer lab',
                    'second_image' => 'stock/home-hero-lab.webp',
                    'second_image_alt' => 'Students practising in a computer lab',
                    'third_image' => 'stock/home-hero-cert.webp',
                    'third_image_alt' => 'Graduates celebrating',
                    'callout_title' => 'Linux Bootcamp',
                    'callout_text' => 'Mon–Fri, 1–5 PM · New batch every Monday',
                    'callout_url' => '/linux-bootcamp/',
                ],
                'extra_fact' => ['value' => '3 ways', 'label' => 'to train: individually, as a group or as a corporate batch', 'icon' => 'users'],
                'tracks' => [
                    'eyebrow' => 'Course categories',
                    'title' => 'Explore courses by track',
                    'intro' => 'Pick a track to filter the catalogue, or browse everything.',
                    'courses_shown' => 8,
                ],
                'audiences' => [
                    'eyebrow' => 'Training for',
                    'title' => 'Learn on your own, as a group or as a company',
                    'intro' => 'Every course is available to individual learners, to groups of friends or classmates, and as a private corporate batch for your organisation.',
                    'individual_image' => 'stock/audience-individual.webp',
                    'group_image' => 'stock/audience-group.webp',
                    'corporate_image' => 'stock/corporate-hero.webp',
                ],
                'featured' => [
                    'eyebrow' => 'Featured courses',
                    'title' => 'Popular right now',
                    'main_course' => 'red-hat-system-administration',
                    'main_badge' => 'Official Red Hat training',
                    'main_image' => 'stock/home-featured-main.webp',
                    'cards' => [
                        ['course' => 'junior-security-analyst-track', 'badge' => 'Cybersecurity', 'extra' => '80% practical', 'image' => 'stock/home-featured-soc.webp'],
                        ['course' => 'linux-bootcamp', 'badge' => 'Bootcamp', 'extra' => 'Mon–Fri 1–5 PM', 'image' => 'stock/home-featured-code.webp'],
                    ],
                ],
                'journey' => [
                    'eyebrow' => 'How we train',
                    'title' => 'From first enquiry to certified professional',
                    'image' => 'stock/home-journey.webp',
                    'image_alt' => 'Instructor guiding students in the lab',
                    'pillars' => [
                        ['icon' => 'flask-conical', 'label' => 'Practical learning'],
                        ['icon' => 'graduation-cap', 'label' => 'Expert guidance'],
                        ['icon' => 'target', 'label' => 'Career-focused skills'],
                    ],
                    'steps' => [
                        ['title' => 'Free career counselling', 'text' => 'Talk to an advisor about your background and goals to find the right track, alone or as a group.'],
                        ['title' => 'Hands-on training', 'text' => 'Instructor-led classes with practical labs: classroom, live virtual, one-on-one or flip class.'],
                        ['title' => 'Certification-focused preparation', 'text' => 'Official courseware and exam-style practice for Red Hat and other certifications.'],
                        ['title' => 'Job-ready skills', 'text' => 'Leave ready to apply your skills in real IT roles, with lifetime support from our mentors.'],
                    ],
                ],
                'paths' => [
                    'eyebrow' => 'Learning paths',
                    'title' => 'Know exactly what to learn next',
                    'intro' => 'Pick a career goal to see a proven sequence of courses from our catalogue.',
                ],
                'batches' => [
                    'eyebrow' => 'Upcoming batches',
                    'title' => 'Join the next batch',
                    'intro' => 'Seats are limited so every learner gets lab time.',
                ],
                'trust' => [
                    'eyebrow' => 'Trust & credibility',
                    'title' => 'Certification tracks we teach',
                    'vendors' => ['Red Hat', 'Microsoft', 'Cisco', 'CompTIA', 'ISC2', 'Kubernetes'],
                    'partner_title' => 'Red Hat Authorized Training Partner for 15+ years',
                    'partner_text' => 'Official courseware, labs and exam-focused content, including RHCSA and RHCE programmes.',
                    'reviews_text' => 'Read independent reviews from our students on Google.',
                ],
                'why' => [
                    'eyebrow' => 'Why choose us',
                    'title' => 'Practical training, recognised certifications',
                    'items' => [
                        ['icon' => 'award', 'title' => 'Red Hat Authorized Partner', 'text' => 'Up-to-date, official Red Hat training materials and labs.'],
                        ['icon' => 'flask-conical', 'title' => 'Hands-on training', 'text' => 'Around 80% of class time is practical work on real systems.'],
                        ['icon' => 'briefcase-business', 'title' => 'Job-ready skills', 'text' => 'Resume, interview and career guidance alongside the technical skills.'],
                        ['icon' => 'laptop', 'title' => 'Flexible learning options', 'text' => 'Classroom, live virtual, one-on-one and flip-class modes.'],
                        ['icon' => 'graduation-cap', 'title' => 'Certified industry experts', 'text' => 'Learn from certified professionals with real-world experience.'],
                        ['icon' => 'users', 'title' => 'Individuals and groups', 'text' => 'Join a public batch, or train privately with your group or team.'],
                    ],
                ],
                'faq' => [
                    'eyebrow' => 'FAQ',
                    'title' => 'Questions, answered',
                    'intro' => 'Everything you need to know before you enrol.',
                ],
                'cta' => [
                    'title' => 'Not sure which course is right for you?',
                    'text' => 'Tell us your career goals and current experience. A training advisor will call you back to recommend a track and batch, for you or your group.',
                    'checklist' => ['Free career counselling', 'Sun – Fri · 6:30 AM – 8:00 PM', 'New Baneshwor, Kathmandu, Nepal'],
                    'form_title' => 'Send Inquiry',
                    'form_intro' => 'An advisor replies the same working day.',
                ],
            ],

            'about' => [
                'seo' => [
                    'title' => 'About IT Security Nepal | Red Hat Authorized Training Partner in Kathmandu',
                    'description' => 'I.T. Security College of Computer Studies: a Kathmandu IT training institute and Red Hat Authorized Training Partner for 15+ years, specialising in cybersecurity, networking, cloud and Linux.',
                ],
                'eyebrow' => 'About IT Security Nepal',
                'title' => "Nepal's specialist institute for cybersecurity, network and system training",
                'lead' => 'Welcome to I.T. Security College of Computer Studies, a leader in IT training and certification based in New Baneshwor, Kathmandu, and a Red Hat Authorized Training Partner for more than 15 years.',
                'mission' => [
                    'eyebrow' => 'Our mission',
                    'title' => 'Empowering people with the skills, knowledge and certifications to excel in IT',
                    'paragraphs' => [
                        'Whether you are a beginner exploring the IT field or an experienced professional pursuing advanced certification, we offer the expertise and resources to help you reach your goals, on your own or as part of a group.',
                        'Over the past decade and a half we have trained and certified thousands of students who now work in IT in Nepal and abroad.',
                    ],
                    'cards' => [
                        ['icon' => 'terminal', 'title' => 'Training philosophy', 'text' => 'Learn by doing. Every concept is practised on a real system, with an instructor beside you.'],
                        ['icon' => 'award', 'title' => 'Authorized partner', 'text' => 'Red Hat Authorized Training Partner for over 15 years, using official courseware and labs.'],
                        ['icon' => 'users', 'title' => 'Individuals and groups', 'text' => 'Join a public batch, train one-on-one, or bring your group for a private batch.'],
                        ['icon' => 'graduation-cap', 'title' => 'Certification-focused', 'text' => 'Courses map to recognised exams such as RHCSA, RHCE, CCNA, CKA and AZ-104.'],
                    ],
                ],
                'stats_title' => 'At a glance',
                'certifications' => [
                    'eyebrow' => 'Certifications we prepare you for',
                    'title' => 'Globally recognised credentials',
                    'intro' => 'Our courses prepare learners for certifications from these technology vendors and bodies. Red Hat is our formal training partnership; the others are exam-preparation courses.',
                    'vendors' => [
                        ['name' => 'Red Hat', 'text' => 'RHCSA · RHCE · OpenShift'],
                        ['name' => 'Cisco', 'text' => 'CCNA · CCNP'],
                        ['name' => 'Microsoft', 'text' => 'Azure · M365 · Security'],
                        ['name' => 'CompTIA', 'text' => 'A+ · Security+ · CySA+'],
                        ['name' => 'ISC2', 'text' => 'CISSP · CCSP'],
                        ['name' => 'CNCF', 'text' => 'CKA · CKS'],
                    ],
                ],
                'environment' => [
                    'eyebrow' => 'Learning environment',
                    'title' => 'A practical lab in the heart of Kathmandu',
                    'intro' => "Our New Baneshwor centre is set up for hands-on work: lab machines, networking equipment and instructors who help you troubleshoot in real time. Can't come in? Join the same classes live online.",
                    'image' => 'stock/about-environment.webp',
                    'image_alt' => 'Class in a training room',
                ],
                'cta_title' => 'Visit us or talk to an advisor',
            ],

            'group' => [
                'seo' => [
                    'title' => 'Group IT Training in Kathmandu | Private Batches | IT Security Nepal',
                    'description' => 'Train together in a private batch: Red Hat, cybersecurity, CCNA, DevOps and cloud courses for groups of friends, classmates or teams, in Kathmandu or live online.',
                ],
                'badge' => 'Training for groups',
                'title' => 'Group training: learn together in a private batch',
                'lead' => 'Bring a group of friends, classmates or colleagues, or a whole team from your organisation, and train together in a private batch with a schedule that suits everyone.',
                'image' => 'stock/audience-group.webp',
                'image_alt' => 'Group of students learning together',
                'features' => [
                    ['icon' => 'users', 'title' => 'Any group', 'text' => 'Friends, classmates, colleagues or a whole team from your organisation.'],
                    ['icon' => 'calendar-days', 'title' => 'Your schedule', 'text' => 'Batch days and times agreed with the group.'],
                    ['icon' => 'map-pin', 'title' => 'Your location', 'text' => 'Our New Baneshwor centre, on-site at your office, or live online.'],
                    ['icon' => 'badge-check', 'title' => 'Same quality', 'text' => 'The same certified instructors, labs and certification preparation.'],
                ],
                'compare_title' => 'Choose how you want to train',
                'compare_rows' => [
                    ['label' => 'Batch', 'individual' => 'Join a public batch (or one-on-one)', 'group' => 'Private batch for your group'],
                    ['label' => 'Schedule', 'individual' => 'Fixed batch timings', 'group' => 'Agreed with your group'],
                    ['label' => 'Location', 'individual' => 'Centre or live online', 'group' => 'Centre, on-site or live online'],
                    ['label' => 'Curriculum', 'individual' => 'Standard course outline', 'group' => 'Standard outline; focus areas can be adjusted'],
                    ['label' => 'Best for', 'individual' => 'Individual learners and job seekers', 'group' => 'Friends, classmates, colleagues and teams'],
                ],
                'popular_title' => 'Courses groups often take together',
                'popular_courses' => ['linux-bootcamp', 'red-hat-system-administration', 'cisco-certified-network-associate', 'devops-mastery'],
                'plan_title' => 'Tell us about your group',
                'plan_intro' => 'Share the course, how many people and your preferred timing. We will suggest a schedule and send the details.',
                'faqs' => [
                    ['question' => 'How many people make a group?', 'answer' => 'Group size and any group terms are confirmed by our advisors when you enquire.'],
                    ['question' => 'Can a company book group training?', 'answer' => 'Yes. Teams from organisations can train as a private group at our centre, on-site or online. See also corporate training for customised programmes.'],
                    ['question' => 'Can we choose the timing?', 'answer' => 'Yes. Group batches are scheduled around the group, subject to instructor availability.'],
                ],
            ],

            'corporate' => [
                'seo' => [
                    'title' => 'Corporate IT Training in Nepal | Cybersecurity, DevOps & Cloud | IT Security Nepal',
                    'description' => 'Customised corporate IT training for organisations in Nepal: cybersecurity, DevOps, cloud and infrastructure programmes delivered on-site, at our Kathmandu centre or online.',
                ],
                'eyebrow' => 'For organisations',
                'title' => 'Corporate IT training that strengthens your whole team',
                'lead' => "Customised programmes in cybersecurity, DevOps, cloud and infrastructure, designed around your systems, your risks and your team's current skills.",
                'image' => 'stock/corporate-hero.webp',
                'image_alt' => 'Trainer presenting to a team',
                'highlights' => [
                    ['icon' => 'users', 'title' => 'Training for teams of any size', 'text' => 'From small IT teams to organisation-wide security awareness.'],
                    ['icon' => 'sliders-horizontal', 'title' => 'Customised content', 'text' => 'Built around your technologies, workflows and goals.'],
                    ['icon' => 'map-pin', 'title' => 'On-site, at our centre or online', 'text' => 'Flexible scheduling for busy professionals.'],
                ],
                'benefits_title' => 'Close skill gaps before they become incidents',
                'benefits' => [
                    ['icon' => 'shield-check', 'title' => 'Reduce security risk', 'text' => 'Train staff to recognise and respond to threats, and harden the systems they run.'],
                    ['icon' => 'trending-up', 'title' => 'Improve productivity', 'text' => 'Teams that automate and troubleshoot confidently ship faster with fewer outages.'],
                    ['icon' => 'badge-check', 'title' => 'Certify your people', 'text' => 'Prepare staff for recognised certifications that clients and regulators trust.'],
                    ['icon' => 'refresh-ccw', 'title' => 'Keep pace with change', 'text' => 'Adopt containers, cloud and DevOps practices with structured, hands-on learning.'],
                ],
                'sector_title' => 'Training for banking & financial institutions',
                'sector_intro' => 'Banks are prime targets for cyber threats. Our specialised programmes help banking teams safeguard customer data, maintain trust and meet regulatory requirements.',
                'sector_points' => ['Fraud prevention and detection', 'Compliance and regulatory standards', 'Secure software development', 'Incident response and security operations'],
                'approach_title' => 'From skills assessment to certified team',
                'approach_steps' => [
                    ['title' => 'Assess', 'text' => "We review your team's current skills, systems and goals."],
                    ['title' => 'Design', 'text' => 'We build a programme around your technologies and timeline.'],
                    ['title' => 'Deliver', 'text' => 'Hands-on sessions on-site, at our centre or live online.'],
                    ['title' => 'Measure', 'text' => 'Lab assessments and certification readiness reports.'],
                ],
                'proposal_title' => 'Tell us about your team',
                'proposal_intro' => 'We usually reply within one working day with a suggested programme outline, format and next steps.',
            ],

            'training_methods' => [
                'title' => 'Flexible ways to learn, with the same expert instructors',
                'lead' => 'Choose the format that fits your schedule. Every option uses the same curriculum, labs and certified instructors, whether you train on your own or as a group.',
                'cta_title' => 'Not sure which format suits you?',
                'cta_text' => 'An advisor can suggest the best option for your schedule and goals.',
            ],

            'course_page' => [
                'learn_title' => "How you'll learn",
                'learn_items' => [
                    ['icon' => 'terminal', 'title' => 'Hands-on labs', 'text' => 'Around 80% of class time is practical work on real systems.'],
                    ['icon' => 'flask-conical', 'title' => 'Real-world scenarios', 'text' => 'Case studies and troubleshooting tasks modelled on real jobs.'],
                    ['icon' => 'badge-check', 'title' => 'Exam preparation', 'text' => 'Exam-style tasks and guidance for the certification.'],
                ],
                'panel_note' => 'Free counselling · no obligation',
                'fees_text' => 'Ask an advisor',
                'no_batch_title' => 'No batch scheduled yet',
                'no_batch_text' => "Leave your details and we'll tell you when the next batch opens, or ask about a private group batch.",
            ],

            'listings' => [
                'courses_title' => 'IT courses & certification training',
                'courses_lead' => 'Instructor-led courses across Linux, cybersecurity, networking, cloud and DevOps, with classroom batches in Kathmandu and live online options, for individuals, groups and corporate batches.',
                'blog_title' => 'Learning resources & career guides',
                'blog_lead' => 'Practical advice on certifications, career paths and getting started in IT, from the team at IT Security Nepal.',
                'batches_title' => 'Upcoming batches',
                'batches_lead' => 'Seats are limited so everyone gets real lab time. Reserve a seat, ask us to notify you when a new batch opens, or request a private batch for your group.',
                'faq_title' => 'Frequently asked questions',
                'faq_lead' => "Can't find an answer? Contact us and an advisor will help.",
                'contact_title' => 'Contact us',
                'contact_lead' => 'Questions about a course, batch dates or fees, for yourself or a group? Call, message or visit us. Advisors usually reply the same working day.',
                'instructors_title' => 'Learn from certified practitioners',
                'instructors_lead' => 'Our instructors hold the certifications they teach and bring real industry experience into every lab session.',
            ],

            'finder' => [
                'title' => 'Find the right course in 4 questions',
                'lead' => 'Get a personalised recommendation from our catalogue. It takes under a minute.',
                'result_title' => 'Your recommended path',
                'advisor_title' => 'Talk it through with an advisor',
                'advisor_text' => 'Confirm the right start, the next batch and fees.',
            ],
        ];
    }

    /** Dot-keys of every image slot, e.g. "home.hero.main_image" (used by tests and the admin editor). */
    public static function imageKeys(): array
    {
        return collect(Arr::dot(self::defaults()))->keys()->filter(fn ($k) => preg_match('/image$/', $k))->values()->all();
    }
}

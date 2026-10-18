{{-- Website pop-ups (Admin → Settings → Site settings → Pop-ups). The script shows at most one per page view:
     campaign first, then Course Finder, then newsletter. --}}
@php
    $popups = \App\Support\Popups::settings();
    // No pop-ups over admin screens or the exam booking/payment flow.
    $isAdminPath = request()->is('admin', 'admin/*', 'red-hat-exam-booking-nepal', 'red-hat-exam-booking-nepal/*');
    $isPreview = request()->filled('campaign_preview') && auth()->check();
@endphp
@unless ($isAdminPath)
    @if ($popups['campaigns_enabled'] || $isPreview)
        @include('partials.campaign-popup')
    @endif
    @unless ($isPreview)
        @if ($popups['course_finder']['enabled'] && ! request()->is('course-finder', 'course-finder/*', 'offer/*')
            && ($popups['course_finder']['show_on'] !== 'home' || request()->is('/')))
            @include('partials.course-finder-popup', ['p' => $popups['course_finder']])
        @endif
        @if ($popups['newsletter']['enabled'] && ! request()->is('offer/*', 'newsletter/*'))
            @include('partials.newsletter-popup', ['p' => $popups['newsletter']])
        @endif
    @endunless
@endunless

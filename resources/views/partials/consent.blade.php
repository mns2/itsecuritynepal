{{-- Google Analytics 4. Admin → Site settings → Analytics decides how it loads:
     - "Ask visitors" ON: banner; GA loads only after Accept (choice remembered in this browser).
     - "Ask visitors" OFF (default): GA loads for everyone, no banner. Google Consent Mode keeps analytics cookies off for
       EEA / UK / Swiss visitors (cookieless pings only), ads storage/personalisation is denied everywhere, and the footer
       "Analytics opt-out" link (itsn-consent = no) is always respected.
     No personal data is sent in events. --}}
@php
    $askFirst = (bool) ($site['analytics']['require_consent'] ?? false);
    // EEA + UK + Switzerland: analytics cookies need consent there, so they get cookieless measurement instead.
    $consentRegions = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'IS', 'LI', 'NO', 'GB', 'CH'];
@endphp
<div x-data="{
        askFirst: {{ $askFirst ? 'true' : 'false' }},
        choice: null,
        init() {
            try { this.choice = localStorage.getItem('itsn-consent'); } catch (e) {}
            window.addEventListener('itsn:track', (e) => window.gtag && gtag('event', e.detail.event, Object.fromEntries(Object.entries(e.detail).filter(([k]) => k !== 'event'))));
            window.addEventListener('itsn:analytics-opt-out', () => this.set('no'));
            if (this.choice === 'no') return;
            if (this.choice === 'yes' || ! this.askFirst) this.load();
        },
        set(v) {
            this.choice = v;
            try { localStorage.setItem('itsn-consent', v); } catch (e) {}
            if (v === 'yes') this.load();
            if (v === 'no' && window.gtag) gtag('consent', 'update', { analytics_storage: 'denied' });
        },
        load() {
            if (window.gtag) return;
            window.dataLayer = window.dataLayer || []; window.gtag = function () { dataLayer.push(arguments); };
            gtag('consent', 'default', { ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied', analytics_storage: 'granted' });
            if (! this.askFirst) gtag('consent', 'default', { analytics_storage: 'denied', region: {{ \Illuminate\Support\Js::from($consentRegions) }} });
            gtag('js', new Date()); gtag('config', '{{ $ga }}', { anonymize_ip: true });
            const s = document.createElement('script'); s.async = true; s.src = 'https://www.googletagmanager.com/gtag/js?id={{ $ga }}'; document.head.appendChild(s);
        }
     }" x-show="askFirst && choice === null" x-cloak class="fixed inset-x-3 bottom-20 z-[45] mx-auto max-w-xl rounded-lg border border-line bg-white p-4 shadow-panel sm:bottom-4" role="region" aria-label="Cookie consent">
    <p class="text-sm text-ink">We use analytics cookies to understand which pages help visitors. See our <a href="/privacy-policy/">Privacy Policy</a>.</p>
    <div class="mt-3 flex gap-2">
        <button type="button" class="btn btn-primary btn-sm" @click="set('yes')">Accept analytics</button>
        <button type="button" class="btn btn-outline btn-sm" @click="set('no')">Decline</button>
    </div>
</div>

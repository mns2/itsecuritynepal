{{-- Cookie consent: Google Analytics 4 loads only after the visitor accepts. No PII is sent in events. --}}
<div x-data="{
        choice: null,
        init() {
            try { this.choice = localStorage.getItem('itsn-consent'); } catch (e) {}
            if (this.choice === 'yes') this.load();
            window.addEventListener('itsn:track', (e) => window.gtag && gtag('event', e.detail.event, Object.fromEntries(Object.entries(e.detail).filter(([k]) => k !== 'event'))));
        },
        set(v) { this.choice = v; try { localStorage.setItem('itsn-consent', v); } catch (e) {} if (v === 'yes') this.load(); },
        load() {
            if (window.gtag) return;
            const s = document.createElement('script'); s.async = true; s.src = 'https://www.googletagmanager.com/gtag/js?id={{ $ga }}'; document.head.appendChild(s);
            window.dataLayer = window.dataLayer || []; window.gtag = function () { dataLayer.push(arguments); };
            gtag('js', new Date()); gtag('config', '{{ $ga }}', { anonymize_ip: true });
        }
     }" x-show="choice === null" x-cloak class="fixed inset-x-3 bottom-20 z-[45] mx-auto max-w-xl rounded-lg border border-line bg-white p-4 shadow-panel sm:bottom-4" role="region" aria-label="Cookie consent">
    <p class="text-sm text-ink">We use analytics cookies to understand which pages help visitors. See our <a href="/privacy-policy/">Privacy Policy</a>.</p>
    <div class="mt-3 flex gap-2">
        <button type="button" class="btn btn-primary btn-sm" @click="set('yes')">Accept analytics</button>
        <button type="button" class="btn btn-outline btn-sm" @click="set('no')">Decline</button>
    </div>
</div>

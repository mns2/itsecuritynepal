{{-- Enquiry form. Works without JS (normal POST + redirect back) and with JS (Alpine: inline validation + fetch).
     Spam protection: CSRF, honeypot field, minimum fill time (signed timestamp), rate limiting (see AppServiceProvider). --}}
@props(['id' => 'enquiry', 'compact' => false, 'defaultCourse' => '', 'title' => null, 'corporate' => false, 'audience' => 'Individual', 'form' => null])
@php
    $formType = $form ?? ($corporate ? 'corporate' : ($audience === 'Group' ? 'group' : 'course'));
    $span = $compact ? '' : 'sm:col-span-2';
    $issued = encrypt(now()->timestamp);
    $sessionStatus = session('enquiry_status');
    $sessionForm = session('enquiry_form');
    $errors = $errors ?? new \Illuminate\Support\ViewErrorBag; // e.g. on error pages rendered outside the web middleware
@endphp
<form id="{{ $id }}" action="{{ route('enquiries.store') }}" method="post" class="grid gap-4" novalidate
      x-data="enquiryForm('{{ old('course', $defaultCourse) }}', {{ $corporate ? 'true' : 'false' }}, '{{ $audience }}')" @submit.prevent="submit"
      @open-enquiry.window="if ($event.detail.course) form.course = $event.detail.course; if ($event.detail.audience) form.audience = $event.detail.audience">
    @csrf
    <input type="hidden" name="form" value="{{ $formType }}">
    <input type="hidden" name="source_url" value="{{ url()->current() }}">
    <input type="hidden" name="issued" value="{{ $issued }}">
    <div class="absolute -left-[9999px]" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

    @if ($sessionStatus === 'sent' && $sessionForm === $id)
        <div class="rounded-lg border border-success-600/30 bg-success-50 p-5" role="status">
            <p class="flex items-center gap-2 font-semibold text-success-600"><x-icon name="circle-check" class="size-5" /> Thank you! Your enquiry is in.</p>
            <p class="mt-1 text-sm text-ink-muted">An advisor will contact you within one working day.</p>
        </div>
    @endif

    <template x-if="state === 'success'">
        <div class="rounded-lg border border-success-600/30 bg-success-50 p-5" role="status" tabindex="-1" x-init="$el.focus()">
            <p class="flex items-center gap-2 font-semibold text-success-600"><x-icon name="circle-check" class="size-5" /><span x-text="'Thank you, ' + form.name.split(' ')[0] + '! Your enquiry is in.'">Thank you! Your enquiry is in.</span></p>
            <p class="mt-1 text-sm text-ink-muted">An advisor will contact you within one working day.</p>
            <button type="button" class="btn btn-ghost btn-sm mt-3 -ml-3" @click="reset()">Send another enquiry</button>
        </div>
    </template>
    <div x-show="state === 'error'" x-cloak class="rounded-lg border border-error-600/30 bg-error-50 p-4 text-sm text-error-600" role="alert">
        <p class="flex items-center gap-2 font-semibold"><x-icon name="circle-alert" class="size-5" /> We couldn't send your enquiry.</p>
        <p class="mt-1">Please try again, or call <a href="tel:{{ $site['contact']['phonePrimary']['tel'] ?? '' }}">{{ $site['contact']['phonePrimary']['label'] ?? '' }}</a>.</p>
    </div>
    @if ($errors->any() && old('form_id') === $id)
        <div class="rounded-md bg-error-50 px-4 py-3 text-sm font-medium text-error-600" role="alert">Please fix {{ $errors->count() }} field{{ $errors->count() > 1 ? 's' : '' }} below.</div>
    @endif
    <input type="hidden" name="form_id" value="{{ $id }}">
    <div x-show="Object.keys(errors).length && submitted" x-cloak class="rounded-md bg-error-50 px-4 py-3 text-sm font-medium text-error-600" role="alert" x-text="'Please fix ' + Object.keys(errors).length + ' field' + (Object.keys(errors).length > 1 ? 's' : '') + ' below.'"></div>

    <fieldset class="contents" x-show="state !== 'success'" :disabled="state === 'loading'">
        <legend class="sr-only">{{ $title ?? 'Course enquiry' }}</legend>
        <div @class(['grid gap-4', 'sm:grid-cols-2' => ! $compact])>
            @foreach ([['name', 'Full name', 'text', 'name', ''], ['phone', 'Phone', 'tel', 'tel', '98XXXXXXXX']] as [$f, $label, $type, $ac, $ph])
                <div>
                    <label for="{{ $id }}-{{ $f }}" class="field-label">{{ $label }}</label>
                    <input id="{{ $id }}-{{ $f }}" name="{{ $f }}" class="input" type="{{ $type }}" autocomplete="{{ $ac }}" @if ($ph) placeholder="{{ $ph }}" @endif value="{{ old($f) }}"
                           x-model.trim="form.{{ $f }}" @blur="validate('{{ $f }}')" :aria-invalid="!!errors.{{ $f }}" aria-describedby="{{ $id }}-{{ $f }}-err" required maxlength="{{ $f === 'phone' ? 30 : 120 }}">
                    <p id="{{ $id }}-{{ $f }}-err" class="field-error" x-show="errors.{{ $f }}" x-cloak><x-icon name="circle-alert" class="size-4" /><span x-text="errors.{{ $f }}"></span></p>
                    @error($f)<p class="field-error" x-show="false"><x-icon name="circle-alert" class="size-4" />{{ $message }}</p>@enderror
                </div>
            @endforeach
            <div class="{{ $span }}">
                <label for="{{ $id }}-email" class="field-label">Email</label>
                <input id="{{ $id }}-email" name="email" class="input" type="email" autocomplete="email" placeholder="you@example.com" value="{{ old('email') }}" maxlength="160"
                       x-model.trim="form.email" @blur="validate('email')" :aria-invalid="!!errors.email" aria-describedby="{{ $id }}-email-err" required>
                <p id="{{ $id }}-email-err" class="field-error" x-show="errors.email" x-cloak><x-icon name="circle-alert" class="size-4" /><span x-text="errors.email"></span></p>
                @error('email')<p class="field-error" x-show="false"><x-icon name="circle-alert" class="size-4" />{{ $message }}</p>@enderror
            </div>

            @unless ($corporate)
                <fieldset class="{{ $span }}">
                    <legend class="field-label">Training for</legend>
                    <div class="flex flex-wrap gap-2">
                        @foreach ([['Individual', 'user-round'], ['Group', 'users']] as [$a, $ic])
                            <label class="chip"><input type="radio" class="sr-only" name="audience" value="{{ $a }}" x-model="form.audience" @checked($audience === $a)><x-icon :name="$ic" class="size-4" />{{ $a }}</label>
                        @endforeach
                    </div>
                </fieldset>
                <div class="{{ $span }}" x-show="form.audience === 'Group'" @if ($audience !== 'Group') x-cloak @endif>
                    <label for="{{ $id }}-gsize" class="field-label">Group size</label>
                    <select id="{{ $id }}-gsize" name="size" class="input" x-model="form.size">
                        @foreach (['2–5 people', '6–10 people', '11–20 people', '20+ people'] as $s)<option>{{ $s }}</option>@endforeach
                    </select>
                </div>
            @else
                <div>
                    <label for="{{ $id }}-org" class="field-label">Organisation</label>
                    <input id="{{ $id }}-org" name="org" class="input" type="text" autocomplete="organization" value="{{ old('org') }}" maxlength="160"
                           x-model.trim="form.org" @blur="validate('org')" :aria-invalid="!!errors.org" aria-describedby="{{ $id }}-org-err" required>
                    <p id="{{ $id }}-org-err" class="field-error" x-show="errors.org" x-cloak><x-icon name="circle-alert" class="size-4" /><span x-text="errors.org"></span></p>
                </div>
                <div>
                    <label for="{{ $id }}-size" class="field-label">Team size</label>
                    <select id="{{ $id }}-size" name="size" class="input" x-model="form.size"><option>1–5</option><option>6–15</option><option>16–40</option><option>40+</option></select>
                </div>
            @endunless

            <div class="{{ $span }}">
                <label for="{{ $id }}-course" class="field-label">{{ $corporate ? 'Training area' : 'Interested course' }}</label>
                <select id="{{ $id }}-course" name="course" class="input" x-model="form.course" @change="validate('course')" :aria-invalid="!!errors.course" aria-describedby="{{ $id }}-course-err">
                    <option value="">{{ $corporate ? 'Select a training area' : 'Select a course' }}</option>
                    @if ($corporate)
                        @foreach ($navCategories as $cat)<option value="area:{{ $cat->key }}">{{ $cat->name }}</option>@endforeach
                        <option value="area:custom">Custom programme</option>
                    @else
                        @foreach ($navCategories as $cat)
                            <optgroup label="{{ $cat->name }}">
                                @foreach ($cat->publishedCourses as $c)<option value="{{ $c->slug }}" @selected(old('course', $defaultCourse) === $c->slug)>{{ $c->code }} · {{ $c->name }}</option>@endforeach
                            </optgroup>
                        @endforeach
                        <option value="not-sure">Not sure yet, help me choose</option>
                    @endif
                </select>
                <p id="{{ $id }}-course-err" class="field-error" x-show="errors.course" x-cloak><x-icon name="circle-alert" class="size-4" /><span x-text="errors.course"></span></p>
            </div>

            @unless ($corporate)
                <fieldset class="{{ $span }}">
                    <legend class="field-label">Preferred mode</legend>
                    <div class="flex flex-wrap gap-2">
                        @foreach (\App\Models\Course::MODES as $m)
                            <label class="chip"><input type="radio" class="sr-only" name="mode" value="{{ $m }}" x-model="form.mode" @checked($loop->first)>{{ $m }}</label>
                        @endforeach
                    </div>
                </fieldset>
            @endunless

            <div class="{{ $span }}">
                <label for="{{ $id }}-msg" class="field-label">Message <span class="font-normal text-ink-muted">(optional)</span></label>
                <textarea id="{{ $id }}-msg" name="message" class="input" rows="{{ $compact ? 3 : 4 }}" maxlength="2000" x-model="form.message" placeholder="{{ $corporate ? 'Tell us about your team and goals' : 'Your background, goals or questions' }}">{{ old('message') }}</textarea>
            </div>
            <div class="{{ $span }}">
                <label class="flex items-start gap-3 text-sm text-ink-muted">
                    <input type="checkbox" name="consent" value="1" class="check mt-0.5" x-model="form.consent" @change="validate('consent')" :aria-invalid="!!errors.consent" aria-describedby="{{ $id }}-consent-err" @checked(old('consent'))>
                    <span>I agree that IT Security Nepal may contact me about this enquiry, as described in the <a href="/privacy-policy/">Privacy Policy</a>.</span>
                </label>
                <p id="{{ $id }}-consent-err" class="field-error" x-show="errors.consent" x-cloak><x-icon name="circle-alert" class="size-4" /><span x-text="errors.consent"></span></p>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block" :aria-busy="state === 'loading'">
            <span x-show="state !== 'loading'">{{ $corporate ? 'Request a proposal' : 'Send enquiry' }}</span>
            <span x-show="state === 'loading'" x-cloak class="inline-flex items-center gap-2"><x-icon name="loader-circle" class="size-5 animate-spin" /> Sending…</span>
        </button>
        <p class="text-center text-xs text-ink-subtle">Protected against spam. We never share your details.</p>
    </fieldset>
</form>

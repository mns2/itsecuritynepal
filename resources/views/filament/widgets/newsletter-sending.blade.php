<x-filament-widgets::widget>
    {{-- Checks every 15 s; when nothing is waiting this is one quick database query. --}}
    <div wire:poll.15s="tick">
        <x-filament::section compact>
            <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:center;justify-content:space-between">
                <div style="display:flex;gap:.75rem;align-items:flex-start">
                    @if ($hasWork)
                        <x-filament::loading-indicator class="h-6 w-6" style="color:#003275" />
                    @else
                        <x-filament::icon icon="heroicon-o-paper-airplane" class="h-6 w-6" style="color:#003275" />
                    @endif
                    <div>
                        @if ($hasWork)
                            <p style="font-weight:600">Sending: {{ number_format($queued) }} emails left. Keep this page open.</p>
                            <p style="font-size:.875rem;opacity:.75">
                                About {{ $perMinute }} per minute{{ $queued ? ', roughly '.max(1, (int) ceil($queued / $perMinute)).' min to go' : '' }}.
                                @if ($lastBatch) Last batch: {{ $lastBatch }} sent. @endif
                                You can close the page any time; sending continues when you open it again.
                            </p>
                        @elseif ($active->isNotEmpty())
                            <p style="font-weight:600">Scheduled: {{ $active->first()->subject }}</p>
                            <p style="font-size:.875rem;opacity:.75">
                                Starts {{ $active->first()->scheduled_at?->format('j M, g:i A') }}.
                                {{ $cron ? 'It will send automatically.' : 'Have this page open at that time (or later) and it will send.' }}
                            </p>
                        @else
                            <p style="font-weight:600">Nothing waiting to send.</p>
                            <p style="font-size:.875rem;opacity:.75">Press <strong>Send</strong> on an email below. It is delivered from this page while it is open, about {{ $perMinute }} per minute.</p>
                        @endif
                        @if ($lastError)<p style="font-size:.875rem;color:#b91c1c">Problem: {{ $lastError }}</p>@endif
                    </div>
                </div>
                <x-filament::badge :color="$cron ? 'success' : 'gray'" :icon="$cron ? 'heroicon-m-check-circle' : 'heroicon-m-computer-desktop'">
                    {{ $cron ? 'Automatic sending on (server cron detected)' : 'Sends from this page' }}
                </x-filament::badge>
            </div>
        </x-filament::section>
    </div>
</x-filament-widgets::widget>

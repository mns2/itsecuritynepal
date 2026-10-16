<x-filament-panels::page>
    <style>
        .att-pill input { position: absolute; opacity: 0; width: 1px; height: 1px; pointer-events: none; }
        .att-pill span { display: inline-flex; align-items: center; min-height: 2.25rem; padding: .4rem .85rem; border-radius: 999px; border: 1px solid #C4CEDC; font-size: .8125rem; font-weight: 600; background: #fff; color: #1C2B3F; transition: background .12s, color .12s; }
        .att-pill:hover span { border-color: #003275; }
        .att-pill input:focus-visible + span { outline: 2px solid #003275; outline-offset: 2px; }
        .att-row:hover { background: #F7F9FC; }
    </style>
    @php
        $tone = [
            'present' => ['on' => 'background:#16a34a;color:#fff;border-color:#16a34a', 'label' => 'Present', 'key' => 'P'],
            'late' => ['on' => 'background:#F9B233;color:#002152;border-color:#F9B233', 'label' => 'Late', 'key' => 'L'],
            'absent' => ['on' => 'background:#dc2626;color:#fff;border-color:#dc2626', 'label' => 'Absent', 'key' => 'A'],
            'excused' => ['on' => 'background:#5B6B82;color:#fff;border-color:#5B6B82', 'label' => 'Excused', 'key' => 'E'],
        ];
    @endphp
    <form wire:submit="save" class="grid gap-6">
        {{ $this->form }}

        @if ($class)
            <x-filament::section>
                <x-slot name="heading">
                    {{ $class->name }} · {{ \Illuminate\Support\Carbon::parse($this->data['held_on'] ?? today())->format('l j M Y') }}
                </x-slot>
                <x-slot name="description">
                    {{ $class->course?->name }}@if ($class->schedule) · {{ $class->schedule }}@endif @if ($class->trainer) · {{ $class->trainer }}@endif
                    @if ($existing) · <strong>Already saved{{ $takenBy ? ' by '.$takenBy : '' }}</strong>; changes update it.@endif
                </x-slot>
                <x-slot name="afterHeader">
                    @if ($students->isNotEmpty())
                        <x-filament::button size="sm" color="success" icon="heroicon-m-check" wire:click="markAll('present')" type="button">Mark all present</x-filament::button>
                    @endif
                </x-slot>

                @if ($notClassDay)
                    <p style="margin-bottom:1rem;padding:.6rem .8rem;border-radius:8px;background:#FEF3D7;color:#002152;font-size:.875rem">
                        This batch does not normally meet on this day. You can still take attendance (for example, an extra class).
                    </p>
                @endif

                @if ($students->isEmpty())
                    <p style="padding:1rem 0;color:#5B6B82">No students in this batch yet. Students are added when they are enrolled (Enquiries → Enrol as student) or under Training → Classes → this batch.</p>
                @else
                    <div style="overflow-x:auto">
                        <table style="width:100%;border-collapse:collapse;font-size:.9375rem">
                            <thead>
                                <tr style="text-align:left;border-bottom:1px solid #DCE4F0;color:#5B6B82;font-size:.8125rem">
                                    <th style="padding:.5rem .5rem;width:2.5rem">#</th>
                                    <th style="padding:.5rem .5rem">Student</th>
                                    <th style="padding:.5rem .5rem">Attendance</th>
                                    <th style="padding:.5rem .5rem;min-width:12rem">Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $s)
                                    <tr wire:key="row-{{ $s->id }}" class="att-row" style="border-bottom:1px solid #EDF2F9">
                                        <td style="padding:.6rem .5rem;color:#5B6B82">{{ $loop->iteration }}</td>
                                        <td style="padding:.6rem .5rem">
                                            <div style="font-weight:600;color:#003275">{{ $s->name }}</div>
                                            @if ($s->phone)<div style="font-size:.8125rem;color:#5B6B82">{{ $s->phone }}</div>@endif
                                        </td>
                                        <td style="padding:.6rem .5rem">
                                            <div role="radiogroup" aria-label="Attendance for {{ $s->name }}" style="display:flex;flex-wrap:wrap;gap:.35rem">
                                                @foreach ($tone as $value => $t)
                                                    <label class="att-pill" style="position:relative;cursor:pointer">
                                                        <input type="radio" wire:model.live="marks.{{ $s->id }}" value="{{ $value }}" name="mark-{{ $s->id }}">
                                                        <span @if (($marks[$s->id] ?? '') === $value) style="{{ $t['on'] }}" @endif>{{ $t['label'] }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td style="padding:.6rem .5rem">
                                            <input type="text" wire:model="notes.{{ $s->id }}" maxlength="200" placeholder="Optional"
                                                   style="width:100%;padding:.4rem .6rem;border:1px solid #DCE4F0;border-radius:8px;font-size:.875rem">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:center;justify-content:space-between;margin-top:1.25rem">
                        <p style="font-size:.875rem;color:#1C2B3F">
                            <strong style="color:#16a34a">{{ $counts['present'] ?? 0 }} present</strong> ·
                            <strong style="color:#b45309">{{ $counts['late'] ?? 0 }} late</strong> ·
                            <strong style="color:#dc2626">{{ $counts['absent'] ?? 0 }} absent</strong> ·
                            {{ $counts['excused'] ?? 0 }} excused
                            @if (($counts['unmarked'] ?? 0) > 0)
                                · <span style="color:#5B6B82">{{ $counts['unmarked'] }} not marked (saved as absent)</span>
                            @endif
                        </p>
                        <div style="display:flex;gap:.5rem">
                            @if ($registerUrl)<x-filament::button tag="a" :href="$registerUrl" color="gray" icon="heroicon-o-table-cells">Attendance register</x-filament::button>@endif
                            <x-filament::button type="submit" icon="heroicon-o-check">{{ $existing ? 'Update attendance' : 'Save attendance' }}</x-filament::button>
                        </div>
                    </div>
                @endif
            </x-filament::section>
        @endif
    </form>
</x-filament-panels::page>

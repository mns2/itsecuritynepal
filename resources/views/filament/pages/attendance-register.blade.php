<x-filament-panels::page>
    @php
        $cell = [
            'present' => ['P', '#dcfce7', '#15803d', 'Present'],
            'late' => ['L', '#FEF3D7', '#92400e', 'Late'],
            'absent' => ['A', '#fee2e2', '#b91c1c', 'Absent'],
            'excused' => ['E', '#EDF2F9', '#5B6B82', 'Excused'],
        ];
    @endphp
    {{ $this->form }}

    @if ($class)
        <x-filament::section>
            <x-slot name="heading">{{ $class->name }}</x-slot>
            <x-slot name="description">
                {{ $class->course?->name }}@if ($class->schedule) · {{ $class->schedule }}@endif @if ($class->trainer) · {{ $class->trainer }}@endif
                · {{ $sessions->count() }} {{ \Illuminate\Support\Str::plural('class day', $sessions->count()) }} recorded
            </x-slot>

            @if ($rows->isEmpty())
                <p style="color:#5B6B82">No students in this batch yet.</p>
            @elseif ($sessions->isEmpty())
                <p style="color:#5B6B82">No attendance taken for this batch{{ ($this->data['from'] ?? null) || ($this->data['until'] ?? null) ? ' in this period' : ' yet' }}.</p>
            @else
                <div style="overflow-x:auto;max-width:100%">
                    <table style="border-collapse:separate;border-spacing:0;font-size:.875rem;min-width:100%">
                        <thead>
                            <tr style="color:#5B6B82;font-size:.75rem">
                                <th style="position:sticky;left:0;background:#fff;text-align:left;padding:.5rem .75rem .5rem 0;border-bottom:1px solid #DCE4F0;min-width:12rem;z-index:1">Student</th>
                                @foreach ($sessions as $sess)
                                    <th style="padding:.4rem .2rem;border-bottom:1px solid #DCE4F0;text-align:center;white-space:nowrap" title="{{ $sess->topic }}">
                                        <div>{{ $sess->held_on->format('D') }}</div><div style="font-weight:700;color:#003275">{{ $sess->held_on->format('j M') }}</div>
                                    </th>
                                @endforeach
                                <th style="padding:.4rem .6rem;border-bottom:1px solid #DCE4F0;text-align:center">P</th>
                                <th style="padding:.4rem .6rem;border-bottom:1px solid #DCE4F0;text-align:center">L</th>
                                <th style="padding:.4rem .6rem;border-bottom:1px solid #DCE4F0;text-align:center">A</th>
                                <th style="padding:.4rem .6rem;border-bottom:1px solid #DCE4F0;text-align:center">E</th>
                                <th style="padding:.4rem .6rem;border-bottom:1px solid #DCE4F0;text-align:center">Attendance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $r)
                                <tr>
                                    <td style="position:sticky;left:0;background:#fff;padding:.45rem .75rem .45rem 0;border-bottom:1px solid #EDF2F9;z-index:1">
                                        <div style="font-weight:600;color:#003275">{{ $r['student']->name }}</div>
                                        @if ($r['student']->status !== 'enrolled')<div style="font-size:.75rem;color:#5B6B82">{{ \App\Models\Student::STATUSES[$r['student']->status] ?? $r['student']->status }}</div>@endif
                                    </td>
                                    @foreach ($sessions as $sess)
                                        @php($m = $r['marks'][$sess->id] ?? null)
                                        <td style="padding:.25rem .2rem;border-bottom:1px solid #EDF2F9;text-align:center">
                                            @if ($m)
                                                <span title="{{ $cell[$m][3] }} · {{ $sess->held_on->format('j M') }}" style="display:inline-grid;place-items:center;width:1.75rem;height:1.75rem;border-radius:6px;font-weight:700;font-size:.75rem;background:{{ $cell[$m][1] }};color:{{ $cell[$m][2] }}">{{ $cell[$m][0] }}</span>
                                            @else
                                                <span style="color:#C4CEDC">·</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td style="text-align:center;border-bottom:1px solid #EDF2F9">{{ $r['present'] }}</td>
                                    <td style="text-align:center;border-bottom:1px solid #EDF2F9">{{ $r['late'] }}</td>
                                    <td style="text-align:center;border-bottom:1px solid #EDF2F9">{{ $r['absent'] }}</td>
                                    <td style="text-align:center;border-bottom:1px solid #EDF2F9">{{ $r['excused'] }}</td>
                                    <td style="text-align:center;border-bottom:1px solid #EDF2F9;font-weight:700;color:{{ $r['rate'] === null ? '#5B6B82' : ($r['rate'] >= 75 ? '#15803d' : '#b91c1c') }}">
                                        {{ $r['rate'] === null ? '—' : $r['rate'].'%' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p style="margin-top:.9rem;font-size:.8125rem;color:#5B6B82">P present · L late · A absent · E excused. Attendance % counts present and late. Below 75% is shown in red. Hover a date to see the topic.</p>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>

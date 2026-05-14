@extends('layouts.app')

@section('title', 'Calendar – Bira')

@section('content')
@php
    $today     = \Carbon\Carbon::today();
    $firstDay  = \Carbon\Carbon::create($year, $month, 1);
    $lastDay   = $firstDay->copy()->endOfMonth();
    $startDow  = $firstDay->dayOfWeek; // 0=Sun
    $daysInMonth = $firstDay->daysInMonth;
    $prevMonth = $firstDay->copy()->subMonth();
    $nextMonth = $firstDay->copy()->addMonth();
    $monthLabel = $firstDay->format('F Y');
    $activeDayMap = $activeDays; // now an associative array
    $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
@endphp

<style>
    .cal-day.active-tile {
        border-color: #8b5cf6 !important;
        box-shadow: 0 0 0 2px #13131f, 0 0 0 4px #8b5cf6 !important;
    }
    .text-truncate-multi {
        display: -webkit-box;
        -webkit-line-clamp: 6;
        -webkit-box-orient: vertical;
        overflow: hidden;
        word-break: break-all;
        white-space: normal;
    }
    .day-panel-enter { animation: slideInRight .25s ease; }
    @keyframes slideInRight {
        from { transform: translateX(40px); opacity: 0; }
        to   { transform: translateX(0);   opacity: 1; }
    }
    #log-list:empty::after {
        content: 'No time logged for this day yet.';
        display: block;
        text-align: center;
        color: rgba(255,255,255,0.3);
        font-size: 0.75rem;
        padding: 1.5rem 0;
    }
</style>

<div class="flex h-full" id="calendar-root">

    {{-- ══════════════ MAIN CALENDAR AREA ══════════════ --}}
    <div class="flex-1 px-8 py-8 min-w-0" id="cal-main">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-primary/20 flex items-center justify-center text-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                Calendar
            </h1>

            {{-- Month navigation --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('calendar.index', ['date' => $prevMonth->format('Y-m-d')]) }}"
                   class="p-2 rounded-lg hover:bg-white/10 text-muted-foreground hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <span id="cal-month-label" class="text-lg font-bold text-white w-44 text-center">{{ $monthLabel }}</span>
                <a href="{{ route('calendar.index', ['date' => $nextMonth->format('Y-m-d')]) }}"
                   class="p-2 rounded-lg hover:bg-white/10 text-muted-foreground hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('calendar.index', ['date' => $today->format('Y-m-d')]) }}"
                   class="ml-2 px-3 py-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors text-sm font-semibold">
                    Today
                </a>
            </div>
        </div>

        {{-- Day-of-week headers --}}
        <div class="grid mb-2" style="grid-template-columns: repeat(7, minmax(0, 1fr));">
            @foreach($dayNames as $d)
                <div class="text-center text-[11px] font-bold uppercase tracking-widest text-muted-foreground pb-2">{{ $d }}</div>
            @endforeach
        </div>

        {{-- Calendar grid --}}
        <div class="grid gap-1.5" style="grid-template-columns: repeat(7, minmax(0, 1fr));" id="cal-grid">
            {{-- Leading blank cells --}}
            @for($b = 0; $b < $startDow; $b++)
                <div class="cal-day rounded-xl"></div>
            @endfor

            {{-- Day cells --}}
            @for($d = 1; $d <= $daysInMonth; $d++)
                @php
                    $cellDate = \Carbon\Carbon::create($year, $month, $d);
                    $dateStr  = $cellDate->toDateString();
                    $isToday  = $cellDate->isSameDay($today);
                    $dayData  = $activeDayMap[$d] ?? null;
                    $hasEntry = $dayData !== null;
                    $isFuture = $cellDate->isFuture();
                @endphp
                <button
                    data-date="{{ $dateStr }}"
                    data-day="{{ $d }}"
                    style="height: 160px;"
                    class="cal-day overflow-hidden rounded-xl border border-white/5 bg-white/[0.02] hover:bg-white/[0.06] hover:border-white/15 text-left p-3 cursor-pointer transition-all group relative flex flex-col"
                    onclick="openDayPanel('{{ $dateStr }}')"
                >
                    <div class="flex justify-between items-start w-full shrink-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold {{ $isToday ? 'text-white' : 'text-white/70 group-hover:text-white' }} transition-colors">{{ $d }}</span>
                        </div>
                        <div class="day-duration text-xs font-bold text-primary bg-primary/10 px-1.5 py-0.5 rounded inline-block">
                            {{ $dayData['duration'] ?? '0m' }}
                        </div>
                    </div>
                    
                    <div class="w-full text-left mt-2 overflow-hidden flex-1">
                        <p class="day-note text-[13px] text-muted-foreground leading-snug text-truncate-multi break-all {{ empty($dayData['note']) ? 'hidden' : '' }}">
                            {{ $dayData['note'] ?? '' }}
                        </p>
                    </div>
                </button>
            @endfor
        </div>


    </div>

    {{-- ══════════════ DAY PANEL (right side) ══════════════ --}}
    @include('calendar.partials.sidebar', ['isOverlay' => false])
</div>
@endsection

@push('scripts')
@include('calendar.partials.scripts')
<script>
    // Include the updateTile specifically needed for calendar grid
    function updateTile(date, note, duration) {
        const tile = document.querySelector(`.cal-day[data-date="${date}"]`);
        if (!tile) return;
        
        const durEl = tile.querySelector('.day-duration');
        const noteEl = tile.querySelector('.day-note');
        
        durEl.textContent = duration || '0m';
        
        if (note) {
            noteEl.textContent = note;
            noteEl.classList.remove('hidden');
        } else {
            noteEl.classList.add('hidden');
        }
    }
</script>
@endpush

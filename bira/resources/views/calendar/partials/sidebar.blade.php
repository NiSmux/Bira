<div id="day-panel" class="hidden w-[400px] max-w-[400px] bg-[#13131f] flex-col h-full overflow-hidden {{ isset($isOverlay) && $isOverlay ? 'fixed inset-y-0 right-0 z-50 shadow-2xl border-l border-white/10' : 'shrink-0 border-l border-white/10' }}">
    {{-- Panel Header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-white/10 shrink-0">
        <div>
            <p class="text-[10px] uppercase tracking-widest text-muted-foreground mb-0.5">Day View</p>
            <h2 id="panel-day-label" class="text-base font-bold text-white"></h2>
        </div>
        <button onclick="closeDayPanel()" class="p-1.5 rounded-lg hover:bg-white/10 text-muted-foreground hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto">
        {{-- Daily Note --}}
        <div class="px-6 py-4 border-b border-white/5">
            <label class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Daily Note
            </label>
            <textarea id="day-note" rows="3"
                class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/40 resize-none transition-all"
                placeholder="Write a note for this day…"></textarea>
            <div class="flex items-center justify-between mt-1.5">
                <span id="note-status" class="text-[10px] text-muted-foreground"></span>
                <button id="save-note-btn" onclick="saveNote()"
                    class="text-[11px] font-bold text-primary hover:text-primary/80 transition-colors">Save</button>
            </div>
        </div>

        {{-- Time Logs list --}}
        <div class="px-6 py-4 border-b border-white/5">
            <div class="flex items-center justify-between mb-3">
                <label class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-muted-foreground">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Time Logged
                </label>
                <span id="total-duration" class="text-xs font-bold text-primary"></span>
            </div>
            <div id="log-list" class="space-y-2"></div>
        </div>

        {{-- Log Time Form --}}
        <div class="px-6 py-4">
            <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-3 flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Log Time
            </p>

            {{-- Board selector --}}
            <div class="mb-3">
                <label class="text-[10px] text-muted-foreground mb-1 block">Board</label>
                <select id="log-board" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary/40" onchange="fetchTasksForBoard(this.value)">
                    <option value="" class="bg-[#13131f] text-white">— Select Board —</option>
                    @if(isset($myBoards))
                        @foreach($myBoards as $b)
                            <option value="{{ $b->id }}" class="bg-[#13131f] text-white">{{ $b->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            {{-- Task selector --}}
            <div class="mb-3">
                <label class="text-[10px] text-muted-foreground mb-1 block">Task (optional)</label>
                <select id="log-task" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary/40">
                    <option value="" class="bg-[#13131f] text-white">General</option>
                </select>
            </div>

            {{-- Time inputs --}}
            <div class="grid grid-cols-2 gap-2 mb-3">
                <div>
                    <label class="text-[10px] text-muted-foreground mb-1 block">Hours</label>
                    <input id="log-hours" type="number" min="0" max="23" placeholder="0"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary/40">
                </div>
                <div>
                    <label class="text-[10px] text-muted-foreground mb-1 block">Minutes</label>
                    <input id="log-minutes" type="number" min="0" max="59" placeholder="0"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary/40">
                </div>
            </div>

            {{-- Note --}}
            <div class="mb-4">
                <label class="text-[10px] text-muted-foreground mb-1 block">Note (optional)</label>
                <input id="log-note" type="text" maxlength="200" placeholder="What did you work on?"
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary/40">
            </div>

            <button onclick="submitTimeLog()" id="log-submit-btn"
                class="w-full bg-primary hover:bg-primary/90 text-white text-sm font-bold py-2.5 rounded-xl transition-all shadow-lg shadow-primary/20 active:scale-[0.98]">
                Log Time
            </button>
            <p id="log-error" class="text-red-400 text-xs mt-2 hidden"></p>
        </div>
    </div>
</div>

<script>
const CSRF  = document.querySelector('meta[name="csrf-token"]').content;
let activeDate = null;

// ── Open / close panel ─────────────────────────────────────────────────────
function openDayPanel(date) {
    activeDate = date;
    const panel = document.getElementById('day-panel');
    panel.classList.remove('hidden');
    panel.classList.add('flex');
    
    // Highlight active day tile (only on full calendar view)
    document.querySelectorAll('.cal-day').forEach(el => {
        el.classList.remove('active-tile');
        if (el.dataset.date === date) {
            el.classList.add('active-tile');
        }
    });

    loadDayData(date);
}

function closeDayPanel() {
    document.getElementById('day-panel').classList.add('hidden');
    document.getElementById('day-panel').classList.remove('flex');
    activeDate = null;
    
    document.querySelectorAll('.cal-day').forEach(el => {
        el.classList.remove('active-tile');
    });
}

// ── Load day data via AJAX ─────────────────────────────────────────────────
function loadDayData(date) {
    document.getElementById('panel-day-label').textContent = '…';
    document.getElementById('day-note').value = '';
    document.getElementById('log-list').innerHTML = '';
    document.getElementById('total-duration').textContent = '';

    fetch(`/calendar/day/${date}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            document.getElementById('panel-day-label').textContent = data.day_label;
            document.getElementById('day-note').value = data.note;
            document.getElementById('total-duration').textContent = data.total_minutes > 0 ? data.total_duration + ' total' : '';
            renderLogs(data.logs);
            if (typeof updateTile === 'function') {
                updateTile(date, data.note, data.total_duration);
            }
        });
}

// ── Load tasks for a selected board ─────────────────────────────────────────
function fetchTasksForBoard(boardId) {
    const taskSelect = document.getElementById('log-task');
    taskSelect.innerHTML = '<option value="" class="bg-[#13131f] text-white">General</option>';
    
    if (!boardId) return;

    fetch(`/calendar/board-tasks/${boardId}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(tasks => {
            tasks.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.className = 'bg-[#13131f] text-white';
                opt.textContent = t.title.length > 50 ? t.title.substring(0, 50) + '...' : t.title;
                taskSelect.appendChild(opt);
            });
        });
}

// ── Render time log list ───────────────────────────────────────────────────
function renderLogs(logs) {
    const list = document.getElementById('log-list');
    if (!logs.length) { list.innerHTML = ''; return; }

    list.innerHTML = logs.map(l => `
        <div class="flex items-start gap-3 p-3 rounded-xl bg-white/5 border border-white/5 group" data-log-id="${l.id}">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-white truncate">${l.task_title === 'Free entry' ? 'General' : l.task_title}</p>
                ${l.note ? `<p class="text-[11px] text-muted-foreground mt-0.5 break-all">${escHtml(l.note)}</p>` : ''}
                <p class="text-[10px] text-muted-foreground/60 mt-0.5">${l.created_at}</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-sm font-bold text-primary">${l.duration}</span>
                <button onclick="deleteLog(${l.id})" class="opacity-0 group-hover:opacity-100 transition-opacity p-1 rounded hover:bg-red-500/20 text-muted-foreground hover:text-red-400">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>`).join('');
}

// ── Save note ──────────────────────────────────────────────────────────────
let noteTimer = null;
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('day-note')?.addEventListener('input', () => {
        clearTimeout(noteTimer);
        document.getElementById('note-status').textContent = 'Unsaved…';
        noteTimer = setTimeout(saveNote, 1200);
    });
});

function saveNote() {
    if (!activeDate) return;
    const content = document.getElementById('day-note').value;
    fetch('/calendar/notes', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ note_date: activeDate, content }),
    }).then(r => r.json()).then(data => {
        if (data.success) {
            document.getElementById('note-status').textContent = 'Saved ✓';
            const currentDur = document.getElementById('total-duration').textContent.replace(' total', '');
            if (typeof updateTile === 'function') {
                updateTile(activeDate, content, currentDur);
            } else {
                updateMiniTile(activeDate, content, currentDur);
            }
            setTimeout(() => { document.getElementById('note-status').textContent = ''; }, 2000);
        }
    });
}

// ── Submit time log ────────────────────────────────────────────────────────
function submitTimeLog() {
    const hours   = parseInt(document.getElementById('log-hours').value) || 0;
    const minutes = parseInt(document.getElementById('log-minutes').value) || 0;
    const note    = document.getElementById('log-note').value;
    const taskId  = document.getElementById('log-task').value;
    const errEl   = document.getElementById('log-error');

    if (hours === 0 && minutes === 0) {
        errEl.textContent = 'Please enter a time greater than 0.';
        errEl.classList.remove('hidden');
        return;
    }
    errEl.classList.add('hidden');

    const btn = document.getElementById('log-submit-btn');
    btn.disabled = true;
    btn.textContent = 'Logging…';

    fetch('/calendar/time-logs', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ logged_date: activeDate, hours, minutes, note, work_item_id: taskId || null }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('log-hours').value   = '';
            document.getElementById('log-minutes').value = '';
            document.getElementById('log-note').value    = '';
            loadDayData(activeDate); // This fetches and updates tile instantly
            
            // Let button show logged temporarily
            btn.textContent = 'Logged ✓';
            btn.classList.add('bg-green-500', 'hover:bg-green-600');
            btn.classList.remove('bg-primary', 'hover:bg-primary/90');
            
            // For dashboard mini tile
            if (typeof updateMiniTile === 'function') {
                updateMiniTile(activeDate, true, true);
            }

            setTimeout(() => {
                btn.disabled = false;
                btn.textContent = 'Log Time';
                btn.classList.remove('bg-green-500', 'hover:bg-green-600');
                btn.classList.add('bg-primary', 'hover:bg-primary/90');
            }, 1500);
        } else {
            btn.disabled = false;
            btn.textContent = 'Log Time';
            errEl.textContent = data.error || 'An error occurred.';
            errEl.classList.remove('hidden');
        }
    }).catch(() => {
        btn.disabled = false;
        btn.textContent = 'Log Time';
        errEl.textContent = 'Network error. Try again.';
        errEl.classList.remove('hidden');
    });
}

// ── Delete time log ────────────────────────────────────────────────────────
function deleteLog(logId) {
    if (!confirm('Remove this time log entry?')) return;
    fetch(`/calendar/time-logs/${logId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    }).then(r => r.json()).then(data => {
        if (data.success) {
            loadDayData(activeDate); // Instantly updates tile and panel
            
            // For dashboard mini tile (might need to check if still has logs/notes)
            // But since this is a complex UI, a page refresh or soft-refresh would be ideal.
            // But since they just deleted it, we just loadDayData.
        }
    });
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function updateMiniTile(date, note, duration) {
    const miniTile = document.querySelector(`a[title="${date}"]`);
    if (miniTile) {
        let dot = miniTile.querySelector('.mini-day-dot');
        const isToday = miniTile.classList.contains('text-primary');
        if (note || duration) {
            if (!dot && !isToday) {
                dot = document.createElement('span');
                dot.className = 'absolute bottom-0.5 w-1 h-1 rounded-full bg-primary/70 inline-block mini-day-dot';
                miniTile.appendChild(dot);
            }
        }
    }
}
</script>

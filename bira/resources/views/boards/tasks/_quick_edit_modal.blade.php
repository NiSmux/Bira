@once('quick-edit-modal')
{{-- ═══════════════════════════════════════════════════════════
     QUICK EDIT MODAL  –  redesigned to match Backlog Create Task modal
     ═══════════════════════════════════════════════════════════ --}}

{{-- ── Modal backdrop + panel ──────────────────────────────── --}}
<div id="quick-edit-modal"
     class="fixed inset-0 z-[1000] flex items-center justify-center p-4"
     style="display:none;"
     aria-modal="true" role="dialog" aria-labelledby="qem-heading">

    {{-- Backdrop --}}
    <div id="qem-overlay"
         class="absolute inset-0 bg-background/80 backdrop-blur-md cursor-pointer"></div>

    {{-- Panel --}}
    <div id="qem-panel"
         class="relative flex flex-col w-full max-w-3xl bg-sidebar border border-white/10 rounded-[2.5rem] mx-4 shadow-3xl animate-in zoom-in-95 duration-200 overflow-hidden"
         style="max-height:90vh;">

        {{-- ── Header ── --}}
        <div class="qem-header flex items-center gap-4 shrink-0">
            <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary border border-primary/20 shrink-0">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 id="qem-heading" class="text-white font-black text-2xl tracking-tight">Quick Edit</h3>
                <p id="qem-subtitle" class="text-muted-foreground text-sm font-medium mt-0.5">–</p>
            </div>
            <button type="button" id="qem-close-btn"
                    class="ml-2 p-2 rounded-xl hover:bg-white/5 text-muted-foreground hover:text-white transition-all shrink-0 cursor-pointer"
                    aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- ── Loading spinner ── --}}
        <div id="qem-spinner" class="flex flex-col items-center justify-center gap-3 py-20">
            <div class="w-8 h-8 rounded-full border-[3px] border-white/10 border-t-primary animate-spin"></div>
            <p class="text-muted-foreground text-sm font-medium">Loading task details…</p>
        </div>

        {{-- ── Form ── --}}
        <form id="qem-form" onsubmit="return qemSave(event)"
              class="flex-col overflow-hidden" style="display:none;">

            {{-- Scrollable body --}}
            <div class="qem-body flex-1 overflow-y-auto space-y-8 custom-scrollbar">

                {{-- Error banner --}}
                <div id="qem-err"
                     class="hidden rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 text-sm leading-relaxed">
                </div>

                {{-- Title --}}
                <div class="flex flex-col gap-2">
                    <label class="qem-lbl">Task Title <span class="text-primary">*</span></label>
                    <input type="text" id="qem-t" required placeholder="e.g.: Create login page" class="qem-inp">
                </div>

                {{-- Description --}}
                <div class="flex flex-col gap-2">
                    <label class="qem-lbl">Description</label>
                    <textarea id="qem-desc" rows="4" placeholder="Describe the task in detail..."
                              class="qem-inp resize-none"></textarea>
                </div>

                {{-- Status + Type --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex flex-col gap-2">
                        <label class="qem-lbl">Status</label>
                        <div class="relative group/select">
                            <select id="qem-status" class="qem-sel cursor-pointer"></select>
                            <div class="absolute inset-y-0 right-6 flex items-center pointer-events-none text-muted-foreground group-hover/select:text-primary transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="qem-lbl">Type</label>
                        <div class="relative group/select">
                            <select id="qem-itype" class="qem-sel cursor-pointer"></select>
                            <div class="absolute inset-y-0 right-6 flex items-center pointer-events-none text-muted-foreground group-hover/select:text-primary transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Priority + Estimation --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex flex-col gap-2">
                        <label class="qem-lbl">Priority</label>
                        <div class="relative group/select">
                            <select id="qem-pri" class="qem-sel cursor-pointer">
                                <option value="" class="bg-card">— No priority —</option>
                            </select>
                            <div class="absolute inset-y-0 right-6 flex items-center pointer-events-none text-muted-foreground group-hover/select:text-primary transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label id="qem-est-lbl" class="qem-lbl">Story Points</label>
                        <input type="number" id="qem-est" min="0" placeholder="0" class="qem-inp">
                    </div>
                </div>

                {{-- Tags --}}
                <div class="pt-6 border-t border-white/5 flex flex-col gap-2">
                    <label class="qem-lbl">Tags</label>
                    <div id="qem-tags" class="flex flex-wrap gap-2 min-h-[36px] items-center">
                        <span class="text-muted-foreground text-xs">Loading…</span>
                    </div>
                </div>

                {{-- Assignee --}}
                <div class="pt-6 border-t border-white/5 flex flex-col gap-4">
                    <label class="qem-lbl">Assign to</label>
                    <div id="qem-at-btns" class="flex gap-2 mb-2">
                        <button type="button" data-at="none" class="qem-at">None</button>
                        <button type="button" data-at="user" class="qem-at flex items-center gap-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            User
                        </button>
                        <button type="button" data-at="sub_team" class="qem-at hidden flex items-center gap-2" id="qem-st-btn">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.768-.231-1.48-.628-2.143M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.768.231-1.48.628-2.143M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Sub-Team
                        </button>
                    </div>
                    <div class="hidden" id="qem-user-wrap">
                        <div class="relative group/select">
                            <select id="qem-user-sel" class="qem-sel cursor-pointer">
                                <option value="" class="bg-card">— Select user —</option>
                            </select>
                            <div class="absolute inset-y-0 right-6 flex items-center pointer-events-none text-muted-foreground group-hover/select:text-primary transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                    <div class="hidden" id="qem-st-wrap">
                        <div class="relative group/select">
                            <select id="qem-st-sel" class="qem-sel qem-sel-violet cursor-pointer">
                                <option value="" class="bg-card">— Select sub-team —</option>
                            </select>
                            <div class="absolute inset-y-0 right-6 flex items-center pointer-events-none text-muted-foreground group-hover/select:text-violet-400 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>{{-- /scrollable body --}}

            {{-- ── Footer ── --}}
            <div class="qem-footer flex items-center justify-between gap-4 border-t border-white/5 shrink-0 bg-sidebar/20">
                <a id="qem-full-link" href="#"
                   class="inline-flex items-center gap-2 text-xs text-muted-foreground hover:text-white font-black uppercase tracking-widest transition-all group"
                   title="Open the dedicated edit page">
                    <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Open Full Edit Page
                </a>
                <div class="flex items-center gap-4 flex-1 justify-end">
                    <button type="button" id="qem-cancel-btn"
                            class="px-8 py-3 rounded-2xl text-muted-foreground hover:text-white font-black uppercase tracking-widest hover:bg-white/5 transition-all cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" id="qem-save-btn"
                            class="bg-primary hover:bg-primary/90 text-white px-10 py-4 rounded-2xl font-black text-lg transition-all shadow-xl shadow-primary/25 active:scale-95 leading-none flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </div>
        </form>{{-- /qem-form --}}
    </div>{{-- /qem-panel --}}
</div>{{-- /quick-edit-modal --}}

{{-- ── Success toast ── --}}
<div id="qem-toast"
     class="fixed bottom-6 right-6 z-[510] transition-all duration-300 pointer-events-none"
     style="transform:translateY(120%);opacity:0;">
    <div class="flex items-center gap-3 bg-[#0e0e1c] border border-green-500/30 text-green-400 px-5 py-3.5 rounded-2xl shadow-2xl shadow-black/50">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="font-semibold text-sm">Task updated successfully!</span>
    </div>
</div>

{{-- ── Styles ── --}}
<style>
/* Custom padding overrides to guarantee elements do not touch the edges, mimicking Tailwind p-12 */
.qem-body {
    padding: 2.5rem 3rem !important; /* py-10 px-12 - matching 3rem padding */
}
.qem-header {
    padding: 2.5rem 3rem 1.5rem 3rem !important; /* pt-10 px-12 pb-6 */
}
.qem-footer {
    padding: 1.5rem 3rem 2.5rem 3rem !important; /* pt-6 px-12 pb-10 */
}

/* Show/hide helpers */
#quick-edit-modal           { display: none; }
#quick-edit-modal.qem-open { display: flex !important; }
#qem-form.qem-open          { display: flex !important; flex-direction: column; overflow: hidden; }

/* Custom scrollbar for scrollable body */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }

/* Label style from Create Task */
.qem-lbl {
    display: block;
    font-size: 10px;
    font-weight: 900;
    color: rgba(255, 255, 255, 0.3) !important;
    text-transform: uppercase;
    letter-spacing: 0.2em;
    margin-left: 0.25rem;
    margin-bottom: 0.625rem;
}

/* Inputs from Create Task */
.qem-inp, .qem-sel {
    display: block;
    width: 100%;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 1rem !important; /* rounded-2xl */
    padding: 0.85rem 1.25rem !important; /* py-3 px-5 - optimized spacing */
    color: #fff !important;
    font-size: 0.95rem !important; /* text-base scaled slightly to allow breathing room */
    font-weight: 500 !important;
    outline: none !important;
    transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s !important;
}
.qem-sel {
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
}
.qem-inp::placeholder { color: rgba(255, 255, 255, 0.2) !important; }
.qem-inp:focus, .qem-sel:focus {
    border-color: #7c3aed !important; /* primary */
    box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1) !important; /* focus:ring-primary/10 */
    background-color: rgba(255, 255, 255, 0.08) !important;
}

.qem-sel option { background: #1a1a2e; color: #fff; }

/* Assignee type buttons */
.qem-at {
    padding: 0.5rem 1rem !important; /* px-4 py-2 */
    border-radius: 0.75rem !important; /* rounded-xl */
    font-size: 0.75rem !important; /* text-xs */
    font-weight: 900 !important; /* font-black */
    text-transform: uppercase !important;
    letter-spacing: 0.1em !important; /* tracking-widest */
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    background: rgba(255, 255, 255, 0.05) !important;
    color: var(--muted-foreground, #888) !important;
    cursor: pointer !important;
    transition: all 0.2s !important;
}
.qem-at:hover {
    color: #fff !important;
    background: rgba(255, 255, 255, 0.08) !important;
}
.qem-at.qem-at-active {
    background: rgba(255, 255, 255, 0.1) !important;
    border-color: rgba(255, 255, 255, 0.2) !important;
    color: #fff !important;
}
.qem-at[data-at="sub_team"].qem-at-active {
    background: rgba(124, 58, 237, 0.15) !important;
    border-color: rgba(124, 58, 237, 0.3) !important;
    color: #a78bfa !important;
}

/* Panel entrance animation */
@keyframes qem-in { from { opacity:0; transform:scale(.96) translateY(10px); } to { opacity:1; transform:scale(1) translateY(0); } }
#qem-panel { animation: qem-in .18s ease-out both; }

/* Spin keyframe for save button spinner */
@keyframes qem-spin { to { transform:rotate(360deg); } }
.qem-spin-icon { animation: qem-spin .65s linear infinite; display:inline-block; }
</style>

{{-- ── Script ── --}}
<script>
(function () {
    'use strict';
    if (window.__qemReady) return; // guard – only initialise once
    window.__qemReady = true;

    const CSRF = '{{ csrf_token() }}';
    let _d       = null;   // current task data from server
    let _selTags = new Set();
    let _atype   = 'none';

    const el = id => document.getElementById(id);

    /* ── PUBLIC API ─────────────────────────────────────────── */
    window.openQuickEdit = function (boardId, taskId) {
        _reset();
        el('quick-edit-modal').classList.add('qem-open');
        document.body.style.overflow = 'hidden';

        fetch(`/boards/${boardId}/tasks/${taskId}/quick-edit-data`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
        .then(data => {
            _d = data;
            _fill(data);
            el('qem-spinner').style.display = 'none';
            el('qem-form').classList.add('qem-open');
        })
        .catch(() => { alert('Could not load task data. Please try again.'); _close(); });
    };

    window.closeQuickEdit = _close;

    window.qemSave = function (e) {
        e.preventDefault();
        if (!_d) return false;

        const btn     = el('qem-save-btn');
        const errDiv  = el('qem-err');
        const origHTML = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<svg class="qem-spin-icon" style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Saving…';
        errDiv.classList.add('hidden');

        /* Build form body */
        const body = new URLSearchParams();
        body.append('_method',      'PUT');
        body.append('_token',       CSRF);
        body.append('title',        el('qem-t').value.trim());
        body.append('description',  el('qem-desc').value);
        body.append('status_id',    el('qem-status').value);
        body.append('item_type_id', el('qem-itype').value);
        body.append('priority_id',  el('qem-pri').value);
        body.append('assignee_type', _atype === 'none' ? '' : _atype);

        const estVal = el('qem-est').value;
        if (_d.estimationMode === 'hours') {
            body.append('estimated_hours', estVal);
        } else {
            body.append('story_points', estVal);
        }

        if (_atype === 'user')     body.append('assignee_id',  el('qem-user-sel').value);
        if (_atype === 'sub_team') body.append('sub_team_id',  el('qem-st-sel').value);

        _selTags.forEach(id => body.append('tags[]', id));

        fetch(_d.updateUrl, {
            method: 'POST',
            headers: {
                'Content-Type':     'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN':     CSRF,
                'Accept':           'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: body.toString(),
        })
        .then(async r => {
            if (r.ok) {
                _close();
                _showToast();
                setTimeout(() => location.reload(), 1100);
            } else {
                try {
                    const j = await r.json();
                    if (j.errors) {
                        errDiv.innerHTML = Object.values(j.errors).flat().join('<br>');
                    } else {
                        errDiv.textContent = j.message || 'Something went wrong.';
                    }
                } catch { errDiv.textContent = 'Something went wrong. Please try again.'; }
                errDiv.classList.remove('hidden');
                btn.disabled  = false;
                btn.innerHTML = origHTML;
            }
        })
        .catch(() => {
            errDiv.textContent = 'Network error. Please check your connection.';
            errDiv.classList.remove('hidden');
            btn.disabled  = false;
            btn.innerHTML = origHTML;
        });

        return false;
    };

    /* ── PRIVATE ─────────────────────────────────────────────── */
    function _close() {
        el('quick-edit-modal').classList.remove('qem-open');
        document.body.style.overflow = '';
        _d = null; _selTags = new Set(); _atype = 'none';
    }

    function _reset() {
        el('qem-spinner').style.display = '';
        el('qem-form').classList.remove('qem-open');
        el('qem-err').classList.add('hidden');
        el('qem-subtitle').textContent = '…';
        const btn = el('qem-save-btn');
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Save changes';
    }

    function _fill(d) {
        const t = d.task;
        el('qem-subtitle').textContent = t.title || '–';
        el('qem-t').value    = t.title       || '';
        el('qem-desc').value = t.description || '';
        el('qem-full-link').href = d.editUrl;

        /* Statuses */
        const ss = el('qem-status'); ss.innerHTML = '';
        (d.statuses || []).forEach(s =>
            ss.appendChild(new Option(s.name, s.id, false, s.id == t.status_id)));

        /* Types */
        const ts = el('qem-itype'); ts.innerHTML = '';
        (d.itemTypes || []).forEach(tp => {
            const lbl = (tp.icon ? tp.icon + ' ' : '') + tp.name;
            ts.appendChild(new Option(lbl, tp.id, false, tp.id == t.item_type_id));
        });

        /* Priorities */
        const ps = el('qem-pri'); ps.innerHTML = '<option value="">— No priority —</option>';
        (d.priorities || []).forEach(p =>
            ps.appendChild(new Option(p.name, p.id, false, p.id == t.priority_id)));

        /* Estimation */
        const estI = el('qem-est'), estL = el('qem-est-lbl');
        if (d.estimationMode === 'hours') {
            estL.textContent = 'Estimated Hours';
            estI.max = 1000; estI.step = 0.5;
            estI.value = t.estimated_hours ?? '';
        } else {
            estL.textContent = 'Story Points';
            estI.max = 100; estI.step = 1;
            estI.value = t.story_points ?? '';
        }

        /* Tags */
        _selTags = new Set((t.tags || []).map(String));
        const tc = el('qem-tags'); tc.innerHTML = '';
        if (d.boardTags && d.boardTags.length) {
            d.boardTags.forEach(tag => {
                const sel = _selTags.has(String(tag.id));
                const b   = document.createElement('button');
                b.type         = 'button';
                b.dataset.tid  = tag.id;
                b.style.cssText = `display:inline-flex;align-items:center;gap:5px;padding:.3rem .65rem;border-radius:.5rem;font-size:.7rem;font-weight:700;cursor:pointer;transition:all .15s;color:${tag.color};border:1px solid;`;
                b.innerHTML = `<span style="width:8px;height:8px;border-radius:50%;background:${tag.color};flex-shrink:0;display:inline-block;"></span>${tag.name}`;
                _tagStyle(b, tag.color, sel);
                b.addEventListener('click', () => {
                    const on = _selTags.has(String(tag.id));
                    on ? (_selTags.delete(String(tag.id)), _tagStyle(b, tag.color, false))
                       : (_selTags.add(String(tag.id)),   _tagStyle(b, tag.color, true));
                });
                tc.appendChild(b);
            });
        } else {
            tc.innerHTML = '<span style="font-size:.75rem;color:#666;">No tags configured on this board.</span>';
        }

        /* Board members */
        const us = el('qem-user-sel'); us.innerHTML = '<option value="">— Select user —</option>';
        (d.boardMembers || []).forEach(m =>
            us.appendChild(new Option(m.name, m.id, false, m.id == t.assignee_id)));

        /* Sub-teams */
        const sts = el('qem-st-sel'), stb = el('qem-st-btn');
        sts.innerHTML = '<option value="">— Select sub-team —</option>';
        if (d.subTeams && d.subTeams.length) {
            stb.classList.remove('hidden');
            d.subTeams.forEach(s =>
                sts.appendChild(new Option(`${s.name} (${s.memberCount} members)`, s.id, false, s.id == t.sub_team_id)));
        } else {
            stb.classList.add('hidden');
        }

        /* Initial assignee type */
        _setAtype(t.assignee_id ? 'user' : (t.sub_team_id ? 'sub_team' : 'none'));
    }

    function _tagStyle(btn, color, selected) {
        btn.style.backgroundColor = selected ? color + '22' : 'transparent';
        btn.style.borderColor     = selected ? color + '88' : 'rgba(255,255,255,.12)';
        btn.style.opacity         = selected ? '1' : '0.42';
    }

    function _setAtype(type) {
        _atype = type;
        el('qem-user-wrap').classList.toggle('hidden', type !== 'user');
        el('qem-st-wrap').classList.toggle('hidden', type !== 'sub_team');
        document.querySelectorAll('.qem-at').forEach(b => {
            b.classList.toggle('qem-at-active', b.dataset.at === type);
        });
    }

    function _showToast() {
        const t = el('qem-toast');
        t.style.transform = 'translateY(0)';
        t.style.opacity   = '1';
        setTimeout(() => { t.style.transform = 'translateY(120%)'; t.style.opacity = '0'; }, 2800);
    }

    function _init() {
        el('qem-overlay')?.addEventListener('click', _close);
        el('qem-close-btn')?.addEventListener('click', _close);
        el('qem-cancel-btn')?.addEventListener('click', _close);

        document.querySelectorAll('.qem-at').forEach(b =>
            b.addEventListener('click', () => _setAtype(b.dataset.at)));

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && el('quick-edit-modal')?.classList.contains('qem-open'))
                _close();
        });
    }

    /* ── Events (Robust Initialization) ─────────────────────── */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', _init);
    } else {
        _init();
    }
})();
</script>
@endonce

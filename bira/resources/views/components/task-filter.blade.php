<style>
    #task-filter-container select option {
        background-color: #1a1a2e;
        color: #ffffff;
    }
</style>
@php
   $filterBoardMode = isset($board) ? ($board->estimation_mode ?? 'points') : 'points';
   $estimatorLabel = $filterBoardMode === 'hours' ? 'Time Estimate' : 'Story Points';
@endphp
<div id="task-filter-container" class="hidden mb-6 p-5 rounded-3xl bg-white/[0.02] border border-white/5 flex flex-wrap items-center gap-5 animate-in fade-in slide-in-from-top-4 duration-300 shadow-xl backdrop-blur-sm">
    <div class="flex flex-col gap-1.5 flex-1 min-w-[160px]">
        <label class="text-[10px] font-black text-muted-foreground uppercase tracking-widest pl-1">{{ $estimatorLabel }}</label>
        <select id="filter-sp" class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-xs font-bold text-white focus:outline-none focus:ring-2 focus:ring-primary/40 appearance-none shadow-sm cursor-pointer hover:bg-white/[0.02] transition-colors">
            <option value="all">Any Size</option>
        </select>
    </div>
    
    <div class="flex flex-col gap-1.5 flex-1 min-w-[160px]">
        <label class="text-[10px] font-black text-muted-foreground uppercase tracking-widest pl-1">Task Type</label>
        <select id="filter-type" class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-xs font-bold text-white focus:outline-none focus:ring-2 focus:ring-primary/40 appearance-none shadow-sm cursor-pointer hover:bg-white/[0.02] transition-colors">
            <option value="all">Any Type</option>
        </select>
    </div>

    <div class="flex flex-col gap-1.5 flex-1 min-w-[160px]">
        <label class="text-[10px] font-black text-muted-foreground uppercase tracking-widest pl-1">Assignee</label>
        <select id="filter-assignee" class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-xs font-bold text-white focus:outline-none focus:ring-2 focus:ring-primary/40 appearance-none shadow-sm cursor-pointer hover:bg-white/[0.02] transition-colors">
            <option value="all">Anyone</option>
        </select>
    </div>

    <div class="flex flex-col gap-1.5 flex-1 min-w-[160px]">
        <label class="text-[10px] font-black text-muted-foreground uppercase tracking-widest pl-1">Priority</label>
        <select id="filter-priority" class="w-full bg-background border border-border-subtle rounded-xl px-4 py-2.5 text-xs font-bold text-white focus:outline-none focus:ring-2 focus:ring-primary/40 appearance-none shadow-sm cursor-pointer hover:bg-white/[0.02] transition-colors">
            <option value="all">Any Priority</option>
        </select>
    </div>

    <div class="flex items-end h-full pt-[22px]">
        <button id="clear-filters-btn" class="px-5 py-2.5 rounded-xl bg-white/5 hover:bg-red-500/10 text-muted-foreground hover:text-red-400 text-xs font-black transition-all border border-transparent hover:border-red-500/20 uppercase tracking-widest h-[40px] flex items-center justify-center">
            Clear
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterContainer = document.getElementById('task-filter-container');
        const toggleBtn = document.getElementById('toggle-filters-btn');
        const selects = {
            sp: document.getElementById('filter-sp'),
            type: document.getElementById('filter-type'),
            assignee: document.getElementById('filter-assignee'),
            priority: document.getElementById('filter-priority')
        };
        const clearBtn = document.getElementById('clear-filters-btn');

        if(toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const isHidden = filterContainer.classList.contains('hidden');
                if (isHidden) {
                    populateFilters();
                    filterContainer.classList.remove('hidden');
                    filterContainer.classList.add('flex');
                    toggleBtn.classList.add('bg-white/10', 'border-white/20');
                } else {
                    filterContainer.classList.add('hidden');
                    filterContainer.classList.remove('flex');
                    toggleBtn.classList.remove('bg-white/10', 'border-white/20');
                    clearFilters();
                }
            });
        }

        function getTasks() {
            return Array.from(document.querySelectorAll('.filterable-task'));
        }

        function populateFilters() {
            const tasks = getTasks();
            const sets = {
                sp: new Set(),
                type: new Set(),
                assignee: new Set(),
                priority: new Set()
            };

            tasks.forEach(task => {
                sets.sp.add(task.dataset.filterSp || '0');
                sets.type.add(task.dataset.filterType || 'none');
                sets.assignee.add(task.dataset.filterAssignee || 'Unassigned');
                sets.priority.add(task.dataset.filterPriority || 'none');
            });

            const currentVals = {
                sp: selects.sp.value,
                type: selects.type.value,
                assignee: selects.assignee.value,
                priority: selects.priority.value
            };

            const populate = (key, defaultText) => {
                const sel = selects[key];
                sel.innerHTML = `<option value="all">${defaultText}</option>`;
                const sorted = Array.from(sets[key]).sort((a,b) => {
                    if (key === 'sp') {
                        const numA = parseFloat(a) || 0;
                        const numB = parseFloat(b) || 0;
                        return numA - numB;
                    }
                    return a.localeCompare(b);
                });
                sorted.forEach(val => {
                    const opt = document.createElement('option');
                    opt.value = val;
                    opt.textContent = (val === '0' && key === 'sp') ? 'Unestimated (0)' : val;
                    sel.appendChild(opt);
                });
                
                if (Array.from(sel.options).some(o => o.value === currentVals[key])) {
                    sel.value = currentVals[key];
                }
            };

            populate('sp', 'Any Size');
            populate('type', 'Any Type');
            populate('assignee', 'Anyone');
            populate('priority', 'Any Priority');
        }

        function applyFilters() {
            const tasks = getTasks();
            const activeVals = {
                sp: selects.sp.value,
                type: selects.type.value,
                assignee: selects.assignee.value,
                priority: selects.priority.value
            };

            tasks.forEach(task => {
                const spOk = activeVals.sp === 'all' || task.dataset.filterSp === activeVals.sp;
                const typeOk = activeVals.type === 'all' || task.dataset.filterType === activeVals.type;
                const assigneeOk = activeVals.assignee === 'all' || task.dataset.filterAssignee === activeVals.assignee;
                const priorityOk = activeVals.priority === 'all' || task.dataset.filterPriority === activeVals.priority;

                const isMatch = spOk && typeOk && assigneeOk && priorityOk;
                task.classList.toggle('hidden', !isMatch);
            });

            // Update column counters for Kanban if on board view
            document.querySelectorAll('.kanban-column').forEach(col => {
                const visibleTasks = col.querySelectorAll('.filterable-task:not(.hidden)').length;
                const counter = col.querySelector('.rounded-full.bg-white\\/5');
                if (counter) {
                    counter.textContent = visibleTasks;
                }
            });

            const isFiltered = Object.values(activeVals).some(v => v !== 'all');
            if (isFiltered && toggleBtn) {
                toggleBtn.classList.add('text-primary', 'border-primary/40', 'bg-primary/5');
            } else if (toggleBtn) {
                toggleBtn.classList.remove('text-primary', 'border-primary/40', 'bg-primary/5');
            }
        }

        ["sp", "type", "assignee", "priority"].forEach(key => {
            selects[key].addEventListener('change', applyFilters);
        });

        function clearFilters() {
            Object.values(selects).forEach(s => s.value = 'all');
            applyFilters();
        }

        if(clearBtn) {
            clearBtn.addEventListener('click', clearFilters);
        }
    });
</script>

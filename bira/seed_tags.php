<?php
$boards = App\Models\Board::all();
$defaultTags = [
    ['name' => 'Front-end', 'color' => '#3b82f6'],
    ['name' => 'Back-End', 'color' => '#ef4444'],
    ['name' => 'Proof-Of-Concept', 'color' => '#a855f7'],
    ['name' => 'Documentation', 'color' => '#10b981'],
    ['name' => 'Blocked', 'color' => '#f97316'],
    ['name' => 'Support Case', 'color' => '#6366f1']
];
foreach ($boards as $b) {
    if ($b->tags()->count() === 0) {
        foreach ($defaultTags as $dt) {
            $b->tags()->create($dt);
        }
    }
}
echo "Tags seeded for boards!\n";

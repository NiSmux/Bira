<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    /**
     * Send a notification to one or more users.
     *
     * @param  array       $userIds  Array of user IDs to notify
     * @param  string      $type     Notification type (e.g. poker_started, sprint_started)
     * @param  string      $title    Short title shown in the notification list
     * @param  string      $message  Longer description / detail text
     * @param  string|null $link     URL to redirect when clicked
     */
    public static function notify(array $userIds, string $type, string $title, string $message, ?string $link = null): void
    {
        if (empty($userIds)) {
            return;
        }

        $records = [];
        $now = now();

        foreach (array_unique($userIds) as $userId) {
            $records[] = [
                'user_id'    => $userId,
                'type'       => $type,
                'title'      => $title,
                'message'    => $message,
                'link'       => $link,
                'is_read'    => 0,
                'created_at' => $now,
            ];
        }

        Notification::insert($records);
    }
}

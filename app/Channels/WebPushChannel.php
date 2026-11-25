<?php

namespace App\Channels;

use App\Models\PushSubscription;
use Illuminate\Notifications\Notification;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $publicKey = (string) config('services.vapid.public_key');
        $privateKey = (string) config('services.vapid.private_key');

        if ($publicKey === '' || $privateKey === '') {
            return;
        }

        $data = method_exists($notification, 'toDatabase')
            ? $notification->toDatabase($notifiable)
            : [];

        $payload = json_encode([
            'title' => $data['type'] === 'ticket_assigned_to_teknisi'
                ? 'Penugasan Teknisi Baru'
                : 'Penugasan Laporan Baru',
            'body' => $data['message'] ?? 'Anda menerima penugasan laporan baru.',
            'url' => $data['url'] ?? '/',
        ], JSON_UNESCAPED_UNICODE);

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => config('services.vapid.subject', config('app.url')),
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ]);

        foreach (PushSubscription::where('user_id', $notifiable->id)->get() as $subscription) {
            if (!$subscription->public_key || !$subscription->auth_token) {
                continue;
            }

            $pushSubscription = Subscription::create([
                'endpoint' => $subscription->endpoint,
                'contentEncoding' => $subscription->content_encoding ?: 'aes128gcm',
                'keys' => [
                    'p256dh' => $subscription->public_key,
                    'auth' => $subscription->auth_token,
                ],
            ]);

            $report = $webPush->sendOneNotification($pushSubscription, $payload);
            if ($report->isSubscriptionExpired()) {
                $subscription->delete();
            }
        }
    }
}

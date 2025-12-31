<?php

namespace App\Services;

use App\Models\NotificationChannel;
use App\Models\User;
use App\Models\UserNotificationSubscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    /**
     * Send a unified email notification
     *
     * @param User $user The user to send the notification to
     * @param string $subject Email subject
     * @param string $content Email content/body
     * @param string|null $ctaText Optional call-to-action button text
     * @param string|null $ctaUrl Optional call-to-action button URL
     * @return bool Whether the email was sent successfully
     */
    public function sendEmail(
        User $user,
        string $subject,
        string $content,
        ?string $ctaText = null,
        ?string $ctaUrl = null
    ): bool {
        // Check if user is subscribed to email notifications
        if (!$this->isSubscribedToChannel($user, 'email')) {
            Log::info("User {$user->id} is not subscribed to email notifications");
            return false;
        }

        try {
            $appName = $this->settingService->get('app_name');
            $mailFromName = $this->settingService->get('mail_from_name');
            $mailFromAddress = $this->settingService->get('mail_from_address');

            Mail::send('emails.notification', [
                'user' => $user,
                'subject' => $subject,
                'content' => $content,
                'ctaText' => $ctaText,
                'ctaUrl' => $ctaUrl,
                'app_name' => $appName,
            ], function ($message) use ($user, $subject, $mailFromName, $mailFromAddress) {
                $message
                    ->to($user->email, $user->name)
                    ->subject($subject)
                    ->from($mailFromAddress, $mailFromName);
            });

            Log::info("Email notification sent to user {$user->id}: {$subject}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send email notification to user {$user->id}", [
                'error' => $e->getMessage(),
                'subject' => $subject,
            ]);
            return false;
        }
    }

    /**
     * Send notification to multiple users
     *
     * @param iterable $users Collection of users
     * @param string $subject Email subject
     * @param string $content Email content
     * @param string|null $ctaText Optional CTA text
     * @param string|null $ctaUrl Optional CTA URL
     * @return int Number of successfully sent notifications
     */
    public function sendBulkEmail(
        iterable $users,
        string $subject,
        string $content,
        ?string $ctaText = null,
        ?string $ctaUrl = null
    ): int {
        $sentCount = 0;

        foreach ($users as $user) {
            if ($this->sendEmail($user, $subject, $content, $ctaText, $ctaUrl)) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    /**
     * Check if a user is subscribed to a specific notification channel
     *
     * @param User $user
     * @param string $channelName The channel identifier (e.g., 'email', 'sms')
     * @return bool
     */
    public function isSubscribedToChannel(User $user, string $channelName): bool
    {
        $channel = NotificationChannel::where('channel', $channelName)
            ->where('is_active', true)
            ->first();

        if (!$channel) {
            return false;
        }

        $subscription = UserNotificationSubscription::where('user_id', $user->id)
            ->where('notification_channel_id', $channel->id)
            ->first();

        return $subscription && $subscription->is_subscribed;
    }

    /**
     * Subscribe a user to a notification channel
     *
     * @param User $user
     * @param string $channelName
     * @return bool
     */
    public function subscribe(User $user, string $channelName): bool
    {
        $channel = NotificationChannel::where('channel', $channelName)->first();

        if (!$channel) {
            return false;
        }

        UserNotificationSubscription::updateOrCreate(
            [
                'user_id' => $user->id,
                'notification_channel_id' => $channel->id,
            ],
            [
                'is_subscribed' => true,
            ]
        );

        return true;
    }

    /**
     * Unsubscribe a user from a notification channel
     *
     * @param User $user
     * @param string $channelName
     * @return bool
     */
    public function unsubscribe(User $user, string $channelName): bool
    {
        $channel = NotificationChannel::where('channel', $channelName)->first();

        if (!$channel) {
            return false;
        }

        UserNotificationSubscription::updateOrCreate(
            [
                'user_id' => $user->id,
                'notification_channel_id' => $channel->id,
            ],
            [
                'is_subscribed' => false,
            ]
        );

        return true;
    }

    /**
     * Subscribe a user to all active notification channels
     *
     * @param User $user
     * @return int Number of channels subscribed to
     */
    public function subscribeToAllChannels(User $user): int
    {
        $channels = NotificationChannel::where('is_active', true)->get();
        $count = 0;

        foreach ($channels as $channel) {
            UserNotificationSubscription::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'notification_channel_id' => $channel->id,
                ],
                [
                    'is_subscribed' => true,
                ]
            );
            $count++;
        }

        return $count;
    }

    /**
     * Get all notification channels with user's subscription status
     *
     * @param User $user
     * @return \Illuminate\Support\Collection
     */
    public function getUserChannelPreferences(User $user)
    {
        $channels = NotificationChannel::where('is_active', true)->get();

        return $channels->map(function ($channel) use ($user) {
            $subscription = UserNotificationSubscription::where('user_id', $user->id)
                ->where('notification_channel_id', $channel->id)
                ->first();

            return [
                'id' => $channel->id,
                'channel' => $channel->channel,
                'name' => $channel->name,
                'description' => $channel->description,
                'is_subscribed' => $subscription ? $subscription->is_subscribed : false,
            ];
        });
    }

    /**
     * Update user's notification preferences
     *
     * @param User $user
     * @param array $preferences Array of channel_id => is_subscribed
     * @return bool
     */
    public function updateUserPreferences(User $user, array $preferences): bool
    {
        try {
            foreach ($preferences as $channelId => $isSubscribed) {
                UserNotificationSubscription::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'notification_channel_id' => $channelId,
                    ],
                    [
                        'is_subscribed' => (bool) $isSubscribed,
                    ]
                );
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to update notification preferences for user {$user->id}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}

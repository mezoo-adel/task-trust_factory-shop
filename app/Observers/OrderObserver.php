<?php

namespace App\Observers;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Handle the Order "created" event.
     * Send notification when order is created with 'paid' status
     */
    public function created(Order $order): void
    {
        // Only send notification if order is created with 'paid' status
        if ($order->status === OrderStatusEnum::PAID) {
            $this->sendOrderReceivedNotification($order);
        }
    }

    /**
     * Handle the Order "updated" event.
     * Send notification when order status changes
     */
    public function updated(Order $order): void
    {
        // Check if status was changed
        if ($order->isDirty('status')) {
            $oldStatus = $order->getOriginal('status');
            $newStatus = $order->status;

            Log::info("Order {$order->id} status changed from {$oldStatus} to {$newStatus->value}");

            // Send appropriate notification based on new status
            match ($newStatus) {
                OrderStatusEnum::PAID => $this->sendOrderReceivedNotification($order),
                OrderStatusEnum::PROCESSING => $this->sendOrderProcessingNotification($order),
                OrderStatusEnum::SHIPPED => $this->sendOrderShippedNotification($order),
                OrderStatusEnum::DELIVERED => $this->sendOrderDeliveredNotification($order),
                OrderStatusEnum::CANCELLED => $this->sendOrderCancelledNotification($order),
                default => null,
            };
        }
    }

    /**
     * Send order received notification to customer and admins
     */
    private function sendOrderReceivedNotification(Order $order): void
    {
        $order->load('user', 'items.product');

        // Notify customer
        $this->notificationService->sendEmail(
            $order->user,
            'Order Received - #' . $order->uuid,
            "Thank you for your order! We have received your order and will begin processing it shortly.\n\nOrder Number: {$order->uuid}\nTotal: $" . number_format($order->total, 2),
            'View Order',
            route('orders.show', $order->uuid)
        );

        // Notify all admins
        $admins = User::where('is_admin', true)->get();
        $this->notificationService->sendBulkEmail(
            $admins,
            'New Order Received - #' . $order->uuid,
            "A new order has been placed by {$order->user->name}.\n\nOrder Number: {$order->uuid}\nTotal: $" . number_format($order->total, 2) . "\nItems: " . $order->items->count(),
            'View Order Details',
            url('/admin/orders/' . $order->id)
        );
    }

    /**
     * Send order processing notification to customer
     */
    private function sendOrderProcessingNotification(Order $order): void
    {
        $order->load('user');

        $this->notificationService->sendEmail(
            $order->user,
            'Order Processing - #' . $order->uuid,
            "Good news! Your order is now being processed and prepared for shipment.\n\nOrder Number: {$order->uuid}\nWe'll notify you once it ships.",
            'Track Order',
            route('orders.show', $order->uuid)
        );
    }

    /**
     * Send order shipped notification to customer
     */
    private function sendOrderShippedNotification(Order $order): void
    {
        $order->load('user');

        $this->notificationService->sendEmail(
            $order->user,
            'Order Shipped - #' . $order->uuid,
            "Your order has been shipped and is on its way to you!\n\nOrder Number: {$order->uuid}\nYou should receive it within 3-5 business days.",
            'Track Shipment',
            route('orders.show', $order->uuid)
        );
    }

    /**
     * Send order delivered notification to customer
     */
    private function sendOrderDeliveredNotification(Order $order): void
    {
        $order->load('user');

        $this->notificationService->sendEmail(
            $order->user,
            'Order Delivered - #' . $order->uuid,
            "Your order has been delivered! We hope you enjoy your purchase.\n\nOrder Number: {$order->uuid}\n\nThank you for shopping with us!",
            'View Order',
            route('orders.show', $order->uuid)
        );
    }

    /**
     * Send order cancelled notification to customer
     */
    private function sendOrderCancelledNotification(Order $order): void
    {
        $order->load('user');

        $this->notificationService->sendEmail(
            $order->user,
            'Order Cancelled - #' . $order->uuid,
            "Your order has been cancelled.\n\nOrder Number: {$order->uuid}\n\nIf you have any questions, please contact our support team.",
            'Contact Support',
            url('/contact')
        );
    }
}

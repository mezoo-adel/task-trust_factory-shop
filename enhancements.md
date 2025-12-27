# Backend Updates
## Order Address
    - on creating new order for auth user, it always creates new address and we need to change that into choosing address (as cards with radio input that send address_id) or `add new address` button that shows the address form to create new address
## Notifications
    - create `UserNotificationSubscription` and `NotificationChannel` models, NotificationChannel will have `channel` attribute with value "email" and  `UserNotificationSubscription` will have `is_subscribed` is "true", `user_id` and `notification_channel_id`
    - create `UserObserver` to add `user_id` `is_subscribed` "true" to all available channels onCreate.
    - create NotificationService and for now add unified email sending function inside it, for example when order's status changes the email takes subject, object, nullable (cta-text and cta-url) to be send for specific user.
    - send the email to the user if the current channel "email" `is_subscribed` = "true"
## Order
    - create OrderObserver to check order's `status` attribute if changed, getting the help of OrderStatusEnum we'll keep tracking for the order's status. for example we create order with status paid on stripe success payment only! if no payment all products still at the cart, so on paid we send email to all admins and the order's user that the order is recieved. also on `processing` status we send another email to the user and so on.
    - add `is_cancallable` accessor attribute in order, it can be cancelled if status not in `cancelled` or `delivered` and add the cancell function at the OrderController and the cancell button at Orders/Show page.
## Admin dashboard
    - `/dashboard` url is for admins only, they'll have a login page that checks users with `is_admin` attribute.
    - create small dashboard with simple statistics like: today's order, no of users [not admins], orders for some statuses, total revenue from delivered order's total, etc... (no need to add all, just giving you ideas).
    - page for product's CRUD and uploading images [store image seperatly at uploadable model then respons with id to be back with product's data] and hint the order's threshold [show products order by threshold to notice what needs] without quantity controll.
    - page to handle stock transactions, choose order and add/remove stock.
    - page to track orders and change status, or add note, etc..
    - creating admin throw dashboard, add page to create admin then we'll send email with the login url and random 10 chars password for login.
## User [Customer, Not Admin]
    - add `profile` page with many tabs, one for change password and another to enable/disable notifications.
    - another tab to edit/create addresses, can't delete any address at backend if it's linked to an order


### if you've any questions or need any help before getting started, just ask me 
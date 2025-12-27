<!-- ## Frontend changes
- create unified components for Products, Address From, Orders, Order Details that recieve props and use them instead of creating a new card code everywhere. 
- create a unified useApiFetch() composable for fetch calls (takes url, params,) that makes calls and handle response & errors from backend as well.
- refactor api-calls to use useApiFetch() in all places instead of fetch().
- move all interface into "types" folder for example, to use it as types instead of adding them everywhere (in case we've written them as types).
- some buttons as "Add to Cart" cursor isn't pointer, and this not a good UX 
- finnaly, the site footer is black/dark indigo and most of app is pink, viollet and white! and this not fit
- the login/register pages from laravel are completly different and not uses the same layout of our home page -->

# Backend Changes
## Order Adress
    - on creating new order for auth user, it always creates new address and we need to change that into choosing address (as cards with radio input that send address_id) or `add new address` button that shows the address form to create new address
## Notifications
    - create NotificationService and for now add unified email sending function inside it, for example when order's status changes the email takes subject, object, nullable (cta-text and cta-url) to be send for specific user
## Order
    - create OrderObserver to check order's `status` attribute if changed, getting the help of OrderStatusEnum we'll keep tracking for the order's status. for example we create order with status paid on stripe success payment only! if no payment all products still at the cart, so on paid we send email to all admins and the order's user that the order is recieved. also on `processing` status we send another email to the user and so on.
    - add `is_cancallable` accessor attribute in order, it can be cancelled if status not in `cancelled` or `delivered` and add the cancell function at the OrderController and the cancell button at Orders/Show page.
## Admin dashboard


### if you've any questions or need any help before getting started, just ask me 
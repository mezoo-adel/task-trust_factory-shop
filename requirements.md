# Simple E-commerce Shopping Cart

## Project Overview
Build a Laravel-based e-commerce shopping cart system with user authentication and automated notifications.

## Core Features
- **Product Browsing**: Display products with name, price, and stock quantity
- **Cart Management**: Add, update quantities, and remove items
- **User Authentication**: Cart persistence tied to authenticated users
- **Stock Monitoring**: Automated low stock notifications
- **Sales Reporting**: Daily sales summary emails

## Technical Requirements

### Backend
- **Framework**: Laravel (latest stable)
- **Authentication**: Laravel starter kit built-in auth
- **Database**: User-based cart storage (no session/localStorage)

### Frontend
Choose one Laravel starter kit:
- Laravel Breeze with Vue

### Styling
- **CSS Framework**: Tailwind CSS

### Version Control
- **Repository**: Git/GitHub

## Data Models

### Product
- `name` (string)
- `price` (decimal)
- `stock_quantity` (integer)

### Cart
- Associated with User model
- Persistent across sessions

## Automated Features

### Low Stock Notification
- **Trigger**: Product stock falls below threshold
- **Implementation**: Laravel Job/Queue
- **Recipient**: Admin user email
- **Content**: Product details and current stock level

### Daily Sales Report
- **Schedule**: Every evening (cron job)
- **Implementation**: Laravel Task Scheduling
- **Recipient**: Admin user email
- **Content**: All products sold in the last 24 hours

## Best Practices
- Follow Laravel conventions and guidelines
- Implement proper error handling
- Use Laravel's built-in features (validation, middleware, etc.)
- Keep code simple and maintainable
- Proper database relationships and migrations
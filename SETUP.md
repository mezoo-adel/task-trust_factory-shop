# Trust Factory Shop - Setup Guide

## Phase 1 Complete ✅

### What's Been Implemented

#### 1. **Dependencies Installed**
- ✅ Laravel Cashier (Stripe integration)
- ✅ Laravel Breeze with Vue 3 + Inertia
- ✅ Tailwind CSS v4
- ✅ Laravel Fortify (authentication)

#### 2. **Database Structure Created**
- ✅ 10 migrations created and ready
- ✅ OrderStatusEnum created
- ✅ 9 Eloquent models with full relationships
- ✅ Seeders created (AdminUserSeeder, ProductSeeder)

#### 3. **Models Implemented**
All models include proper relationships, casts, and helper methods:
- `User` (with is_admin, fingerprint)
- `Visitor` (guest tracking)
- `Product` (with soft deletes, stock helpers)
- `Upload` (polymorphic for images)
- `Cart` (with computed totals)
- `CartItem` (with item total accessor)
- `Address` (user addresses)
- `Order` (with OrderStatusEnum, auto order number)
- `OrderItem` (product snapshots)
- `StockTransaction` (inventory tracking)

## Next Steps - Run Migrations

### Option 1: Use SQLite (Recommended for Development)

The project already has `database/database.sqlite` file. Update your `.env`:

```bash
DB_CONNECTION=sqlite
# Comment out or remove these lines:
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=task_trust_factory
# DB_USERNAME=root
# DB_PASSWORD=
```

Then run:
```bash
php artisan migrate:fresh --seed
```

### Option 2: Use MySQL

Create the MySQL database first:
```bash
mysql -u root -p
CREATE DATABASE task_trust_factory;
exit;
```

Then run:
```bash
php artisan migrate:fresh --seed
```

## After Migration

You'll have:
- **Admin User**: admin@trustfactory.com / password
- **10 Sample Products** (cosmetics)
- **1 Low-Stock Product** (Anti-Aging Serum with 2 units)

## Start Development Server

```bash
composer dev
```

This will start:
- Laravel server (http://localhost:8000)
- Queue worker
- Vite dev server
- Pail logs

## Environment Variables to Configure

Add these to your `.env` file:

```env
# Stripe (get from https://dashboard.stripe.com/test/apikeys)
STRIPE_KEY=pk_test_xxxxx
STRIPE_SECRET=sk_test_xxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxx

# Mail (use Mailtrap for testing)
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@trustfactory.com
MAIL_FROM_NAME="Trust Factory Shop"

# Admin Email (for notifications)
ADMIN_EMAIL=admin@trustfactory.com
```

## Project Structure

```
app/
├── Enums/
│   └── OrderStatusEnum.php ✅
├── Models/
│   ├── User.php ✅
│   ├── Visitor.php ✅
│   ├── Product.php ✅
│   ├── Upload.php ✅
│   ├── Cart.php ✅
│   ├── CartItem.php ✅
│   ├── Address.php ✅
│   ├── Order.php ✅
│   ├── OrderItem.php ✅
│   └── StockTransaction.php ✅
database/
├── migrations/ (10 migrations) ✅
└── seeders/
    ├── AdminUserSeeder.php ✅
    ├── ProductSeeder.php ✅
    └── DatabaseSeeder.php ✅
```

## Next Implementation Phases

### Phase 2: Core Backend Development (Next)
- [ ] Visitor & Fingerprint System
- [ ] Product Management (Admin CRUD)
- [ ] Cart System with Services
- [ ] Order Processing

### Phase 3: Payment Integration
- [ ] Stripe Checkout Flow
- [ ] Webhook Handler
- [ ] Stock Management on Payment

### Phase 4: Automated Features
- [ ] Low Stock Notifications (Job)
- [ ] Daily Sales Report (Scheduled)
- [ ] Order Observer

### Phase 5: Frontend Development
- [ ] Public Pages (Vue Components)
- [ ] Admin Dashboard
- [ ] Cart & Checkout UI

### Phase 6: Testing & QA
### Phase 7: Deployment

## Troubleshooting

### Database Connection Error
If you see "Unknown database" error:
1. Check `.env` file DB_CONNECTION setting
2. For SQLite: ensure `database/database.sqlite` exists
3. For MySQL: create the database first

### Migration Errors
```bash
# Reset and re-run migrations
php artisan migrate:fresh --seed
```

### Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## Testing Database Setup

After running migrations, verify:

```bash
# Check tables were created
php artisan db:show

# Check seeded data
php artisan tinker
>>> \App\Models\User::count()
>>> \App\Models\Product::count()
```

## Development Workflow

1. **Start dev server**: `composer dev`
2. **Make changes** to code
3. **Hot reload** works automatically (Vite)
4. **Queue jobs** process automatically
5. **View logs** in terminal (Pail)

## Important Files

- `plan.md` - Complete project plan with all phases
- `requirements.md` - Original requirements
- `.env.example` - Environment template
- `composer.json` - PHP dependencies
- `package.json` - JavaScript dependencies

## Ready to Continue?

Once migrations are run successfully, we can proceed with Phase 2: Backend Development!

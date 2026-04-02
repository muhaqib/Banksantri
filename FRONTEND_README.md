# Bank Pesantren - Frontend Implementation

A digital wallet system for Islamic boarding school (pesantren) students.

## Tech Stack

- **Backend:** Laravel 13
- **Frontend:** Blade Templates + TailwindCSS 4 + Alpine.js
- **Database:** MySQL

## Features

### Admin Dashboard
- **Dashboard Analytics:** View daily income, expenses, transaction count, and main cash balance
- **Kas Management:** Add/withdraw cash from the main fund
- **Petugas Performance:** Monitor transaction performance of all petugas
- **Settlement:** Approve/reject withdrawal requests from petugas

### Petugas Dashboard
- **Dashboard:** View personal balance, daily earnings, and transaction statistics
- **Transaction Processing:** Scan RFID cards, input amount, verify with student PIN
- **Transaction History:** Filter and view all processed transactions
- **Cash Withdrawal:** Request cash withdrawal from admin

### Santri Mobile UI
- **Home Screen:** Balance display with low balance alert (≤ Rp 10,000)
- **Transaction History:** Color-coded transactions (Red for outgoing, Green for incoming)
- **Profile:** View account info and change PIN
- **Mobile-first Design:** Optimized for mobile devices (byond by BSI style)

## Installation

### Prerequisites
- PHP 8.3+
- Composer
- Node.js & npm
- MySQL

### Steps

1. **Install PHP dependencies**
   ```bash
   composer install
   ```

2. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   Update `.env` with your database credentials:
   ```
   DB_CONNECTION=mysql
   DB_DATABASE=bank_pesantren
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start development server**
   ```bash
   php artisan serve
   ```

## Default Login Credentials

After running the seeder, you can login with:

### Admin
- Email: `admin@pesantren.id`
- Password: `password`

### Petugas
- Email: `petugas1@pesantren.id`
- Password: `password`

### Santri
- Email: `ahmad@pesantren.id` (or budi@pesantren.id, candra@pesantren.id)
- Password: `password`
- PIN: `123456`

## Routes

### Admin Routes
- `/admin/dashboard` - Main dashboard
- `/admin/kas` - Cash management
- `/admin/petugas` - Petugas performance
- `/admin/settlement` - Settlement approval

### Petugas Routes
- `/petugas/dashboard` - Main dashboard
- `/petugas/transaksi` - Process transactions
- `/petugas/riwayat` - Transaction history
- `/petugas/tarik-tunai` - Cash withdrawal

### Santri Routes
- `/santri/home` - Mobile home screen
- `/santri/riwayat` - Transaction history
- `/santri/profile` - Profile settings

## Security Features

- **PIN Verification:** All transactions require 6-digit PIN verification
- **Role-based Access:** Middleware ensures users can only access their designated areas
- **Session Management:** Secure session handling with CSRF protection

## File Structure

```
resources/views/
├── layouts/
│   ├── app.blade.php       # Admin & Petugas layout
│   ├── guest.blade.php     # Login layout
│   └── santri.blade.php    # Santri mobile layout
├── components/
│   ├── sidebar.blade.php   # Navigation sidebar
│   ├── pin-modal.blade.php # PIN verification modal
│   └── transaction-card.blade.php
└── pages/
    ├── auth/
    │   └── login.blade.php
    ├── admin/
    │   ├── dashboard.blade.php
    │   ├── kas.blade.php
    │   ├── petugas.blade.php
    │   └── settlement.blade.php
    ├── petugas/
    │   ├── dashboard.blade.php
    │   ├── transaksi.blade.php
    │   ├── riwayat.blade.php
    │   └── tarik-tunai.blade.php
    └── santri/
        ├── home.blade.php
        ├── riwayat.blade.php
        └── profile.blade.php
```

## Development

### Hot Module Replacement
For development with hot reload:
```bash
npm run dev
```

### Running Tests
```bash
php artisan test
```

## Notes

- This is a **closed system** - no external payment gateway integration
- All monetary values are in Indonesian Rupiah (IDR)
- RFID scanner integration is simulated via keyboard input
- The system is designed for internal pesantren use only

## License

This project is proprietary software for pesantren use.

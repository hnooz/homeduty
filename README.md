# HomeDuty

A household duty management application that helps home groups organize and rotate chores among members. Built with Laravel 12, Inertia.js v2, Vue 3, and Tailwind CSS v4.

## Features

- **Authentication** — Registration, login, password reset, email verification, and two-factor authentication (TOTP) via Laravel Fortify
- **Home Group Creation** — Eligible admins can create a home group and become its owner
- **Member Management** — Invite members by email, accept invitations via token link, manage roles (admin/member), and remove members
- **Duty Planning** — Create rotating duties (cooking, cleaning) with automatic slot generation, member assignment, and drag-based ordering
- **Duty Reminders** — Automated email notifications for upcoming duty slots (day-before and same-day)
- **Role-Based Authorization** — Owner, Admin, and Member roles with granular permissions via Spatie Laravel Permission

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.3, Laravel 13 |
| Frontend | Vue 3, Inertia.js v2, TypeScript |
| Styling | Tailwind CSS v4, Reka UI, Lucide icons |
| Auth | Laravel Fortify (headless) |
| Authorization | Spatie Laravel Permission |
| Routing (FE) | Laravel Wayfinder |
| Testing | Pest 4 |
| Code Quality | Laravel Pint, Rector |

## Requirements

- PHP 8.3+
- Composer
- Node.js & npm
- MySQL

## Installation

```bash
# Clone the repository
git clone git@github.com:hnooz/homeduty.git
cd homeduty

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure your database in .env, then run migrations
php artisan migrate

# Build frontend assets
npm run build
```

## Development

```bash
# Start the development server (Vite + Laravel)
composer run dev

# Or run separately
php artisan serve
npm run dev
```

## Testing

```bash
# Run all tests
php artisan test --compact

# Run a specific test file
php artisan test --compact --filter=DutyTest

# Run with coverage
php artisan test --coverage
```

## Code Quality

```bash
# Format PHP code
vendor/bin/pint

# Run Rector
vendor/bin/rector process

# Lint & format frontend
npm run lint
npm run format

# Type-check Vue/TypeScript
npm run types:check
```

## Console Commands

| Command | Description |
|---------|-------------|
| `php artisan duties:send-reminders` | Send duty reminder notifications for upcoming slots |

## Project Structure

```
app/
├── Actions/Fortify/       # Fortify action classes
├── Console/Commands/      # Artisan commands (duty reminders)
├── Enums/                 # DutyType, GroupMemberRole, HomeDutyRole, HomeDutyPermission
├── Http/
│   ├── Controllers/       # Dashboard, Group, Duty, Member, Invitation, Settings
│   ├── Middleware/         # Custom middleware
│   └── Requests/          # Form request validation
├── Models/                # Eloquent models (User, Group, Duty, DutySlot, etc.)
├── Notifications/         # Email notifications (duty assigned, reminders, invitations)
├── Policies/              # Authorization policies (Group, User)
├── Providers/             # Service providers
└── Services/              # Domain services (Auth, Groups, Roles)
resources/js/
├── components/            # Reusable Vue components
├── layouts/               # App and auth layouts
└── pages/                 # Inertia pages (Dashboard, Groups, Settings, Auth)
tests/
├── Feature/               # Feature tests
└── Unit/                  # Unit tests
```

## License

MIT

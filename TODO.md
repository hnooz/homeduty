# TODO

## Superadmin Dashboard (Filament)

Move email settings out of the user settings area into a dedicated Filament-powered superadmin panel, and add group/member oversight.

### Setup

- [ ] Install Filament (`composer require filament/filament`) and run `php artisan filament:install --panels`
- [ ] Configure the Filament AdminPanel provider at `app/Providers/Filament/AdminPanelProvider.php`
- [ ] Set panel path to `/admin` and restrict access to superadmin users via `canAccessPanel()` on the User model
- [ ] Customize panel branding (app name, logo, colors) to match HomeDuty

### Role & Access

- [ ] Add `SuperAdmin` case to `HomeDutyRole` enum
- [ ] Update `SyncHomeDutyAuthorization` to handle the new role
- [ ] Add an artisan command to promote a user to superadmin (`php artisan homeduty:promote-admin`)
- [ ] Implement `FilamentUser` interface on User model with `canAccessPanel()` checking for SuperAdmin role

### Filament Resources

- [ ] `GroupResource` — table with owner name, member count, duty count, created date; sortable/filterable columns; show page with members, duty schedule, invitation history
- [ ] `UserResource` (Members) — table with role, group name, joined date; sortable/filterable columns
- [ ] `DutyResource` — table with group, assigned user, schedule, status
- [ ] `InvitationResource` — table with invitee, group, status, sent date

### Email Settings (Custom Filament Page)

- [ ] Create a custom Filament page `App\Filament\Pages\EmailSettings` for mailer configuration
- [ ] Move email settings logic from `EmailSettingsController` into this page
- [ ] Remove `/settings/email` routes and the "Email" nav item from the user settings layout
- [ ] Add a "Send Test Email" action button — dispatches a test mail to the superadmin's address to verify config

### Dashboard Widgets

- [ ] Stats overview widget: total groups, total members, pending invitations
- [ ] Mailer status widget: current mailer + whether last mail succeeded
- [ ] Recent activity widget: latest group creations, duty assignments, invitation activity

### Improvements

- [ ] Soft-delete groups instead of hard delete, with a restore action in the GroupResource table
- [ ] Impersonate users directly from UserResource using a Filament action (e.g. `lab404/laravel-impersonate`)
- [ ] Audit log using Spatie Activity Log — viewable as a Filament resource or relation manager
- [ ] Export members/groups as CSV using Filament's built-in export actions
- [ ] Filament notifications for important events (new group created, failed mail delivery)

## Mobile Application Preparation

Prepare the platform with a versioned REST API and token-based auth so a mobile client can consume all existing functionality.

### Authentication (API)

- [ ] Install Laravel Sanctum (`composer require laravel/sanctum`) and publish its config/migrations
- [ ] Create `routes/api.php` and register it in `bootstrap/app.php`
- [ ] `POST /api/v1/login` — issue a Sanctum token (email + password + device_name)
- [ ] `POST /api/v1/register` — register and return a token
- [ ] `POST /api/v1/logout` — revoke the current token
- [ ] `POST /api/v1/forgot-password` — send password-reset link
- [ ] `POST /api/v1/reset-password` — reset password with token

### API Resources & Endpoints

- [ ] Create Eloquent API Resources for all core models (Group, Duty, User, Invitation, etc.)
- [ ] `GET /api/v1/user` — authenticated user profile
- [ ] `PATCH /api/v1/user` — update profile
- [ ] `GET /api/v1/groups` — list user's groups
- [ ] `POST /api/v1/groups` — create a group
- [ ] `GET /api/v1/groups/{group}` — group detail
- [ ] `PATCH /api/v1/groups/{group}` — update group
- [ ] `DELETE /api/v1/groups/{group}` — delete group
- [ ] `GET /api/v1/groups/{group}/duties` — list duties in a group
- [ ] `POST /api/v1/groups/{group}/duties` — create a duty
- [ ] `PATCH /api/v1/duties/{duty}` — update a duty
- [ ] `DELETE /api/v1/duties/{duty}` — delete a duty
- [ ] `POST /api/v1/groups/{group}/invitations` — invite a member
- [ ] `GET /api/v1/invitations` — list pending invitations for the authenticated user
- [ ] `POST /api/v1/invitations/{invitation}/accept` — accept invitation
- [ ] `POST /api/v1/invitations/{invitation}/decline` — decline invitation

### Push Notifications

- [ ] Add `device_token` (nullable) column to `users` table via migration
- [ ] Create a notification channel for FCM (Firebase Cloud Messaging) or APNs
- [ ] Send push notification when a duty is assigned or updated
- [ ] Send push notification for new group invitations

### Infrastructure & Testing

- [ ] Add API versioning prefix (`/api/v1`) and apply `auth:sanctum` middleware to protected routes
- [ ] Add rate limiting to API routes (`throttle:api` or custom limiter)
- [ ] Write Pest feature tests for all API endpoints (auth, groups, duties, invitations)
- [ ] Ensure all API responses follow a consistent JSON structure (data, message, errors)

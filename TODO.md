# TODO

## Superadmin Dashboard (Filament)

Move email settings out of the user settings area into a dedicated Filament-powered superadmin panel, and add group/member oversight.

### Setup

- [x] Install Filament (`composer require filament/filament`) and run `php artisan filament:install --panels`
- [x] Configure the Filament AdminPanel provider at `app/Providers/Filament/AdminPanelProvider.php`
- [x] Set panel path to `/admin` and restrict access to superadmin users via `canAccessPanel()` on the User model
- [x] Customize panel branding (app name, logo, colors) to match HomeDuty

### Role & Access

- [x] Add `SuperAdmin` case to `HomeDutyRole` enum
- [x] Update `SyncHomeDutyAuthorization` to handle the new role
- [x] Add an artisan command to promote a user to superadmin (`php artisan homeduty:promote-admin`)
- [x] Implement `FilamentUser` interface on User model with `canAccessPanel()` checking for SuperAdmin role

### Filament Resources

- [x] `GroupResource` — table with owner name, member count, duty count, created date; sortable/filterable columns; show page with members, duty schedule, invitation history
- [x] `UserResource` (Members) — table with role, group name, joined date; sortable/filterable columns
- [x] `DutyResource` — table with group, assigned user, schedule, status
- [x] `InvitationResource` — table with invitee, group, status, sent date

### Email Settings (Custom Filament Page)

- [x] Create a custom Filament page `App\Filament\Pages\EmailSettings` for mailer configuration
- [x] Move email settings logic from `EmailSettingsController` into this page
- [x] Remove `/settings/email` routes and the "Email" nav item from the user settings layout
- [x] Add a "Send Test Email" action button — dispatches a test mail to the superadmin's address to verify config

### Dashboard Widgets

- [x] Stats overview widget: total groups, total members, pending invitations
- [x] Mailer status widget: current mailer + whether last mail succeeded
- [x] Recent activity widget: latest group creations, duty assignments, invitation activity

### Improvements

- [x] Soft-delete groups instead of hard delete, with a restore action in the GroupResource table
- [x] Impersonate users directly from UserResource using a Filament action (e.g. `lab404/laravel-impersonate`)
- [x] Audit log using Spatie Activity Log — viewable as a Filament resource or relation manager
- [x] Export members/groups as CSV using Filament's built-in export actions
- [x] Filament notifications for important events (new group created, failed mail delivery)

## Mobile Application Preparation

Prepare the platform with a versioned REST API and token-based auth so a mobile client can consume all existing functionality.

### Authentication (API)

- [x] Install Laravel Sanctum (`composer require laravel/sanctum`) and publish its config/migrations
- [x] Create `routes/api.php` and register it in `bootstrap/app.php`
- [x] `POST /api/v1/auth/login` — issue a Sanctum token (email + password + device_name)
- [x] `POST /api/v1/auth/register` — register and return a token
- [x] `POST /api/v1/auth/logout` — revoke the current token
- [x] `POST /api/v1/auth/forgot-password` — send password-reset link
- [x] `POST /api/v1/auth/reset-password` — reset password with token

### API Resources & Endpoints

- [x] Create Eloquent API Resources for all core models: User, Group, GroupMember, GroupInvitation, Duty, DutySlot
- [x] `GET /api/v1/user` — authenticated user profile
- [x] `PATCH /api/v1/user` — update profile
- [x] `GET /api/v1/dashboard` — aggregated dashboard payload (mirrors `DashboardController`)
- [x] `GET /api/v1/groups` — list user's groups
- [x] `POST /api/v1/groups` — create a group (mirrors `GroupController@store`, including initial cleaning period)
- [x] `GET /api/v1/groups/{group}` — group detail
- [x] `PATCH /api/v1/groups/{group}` — update group (name)
- [x] `DELETE /api/v1/groups/{group}` — delete group
- [x] `GET /api/v1/groups/{group}/duties` — list duties + slots
- [x] `POST /api/v1/groups/{group}/duties` — create a duty (accept `cleaning_period_days` 1–3 when type=cleaning)
- [x] `PATCH /api/v1/groups/{group}/duties/{duty}` — update a duty (accept `cleaning_period_days` 1–3 when type=cleaning)
- [x] `DELETE /api/v1/groups/{group}/duties/{duty}` — delete a duty
- [x] `GET /api/v1/groups/{group}/members` — list members
- [x] `PATCH /api/v1/groups/{group}/members/{member}` — update member (role, etc.)
- [x] `DELETE /api/v1/groups/{group}/members/{member}` — remove member
- [x] `POST /api/v1/groups/{group}/invitations` — invite a member
- [x] `POST /api/v1/groups/{group}/invitations/{invitation}/accept-direct` — direct-accept flow for existing users
- [x] `DELETE /api/v1/groups/{group}/invitations/{invitation}` — revoke invitation
- [x] `GET /api/v1/invitations` — list pending invitations for the authenticated user
- [x] `GET /api/v1/invitations/{invitation}` — invitation detail (mirrors `group-invitations.show`)
- [x] `POST /api/v1/invitations/{invitation}/accept` — accept invitation
- [x] `POST /api/v1/invitations/{invitation}/decline` — decline invitation

### Parity with web features

- [ ] Email verification endpoints (Fortify) for self-registered admins
- [ ] Expose superadmin-only endpoints if mobile needs admin tooling (groups, users, duties, invitations, audit log) — otherwise keep admin in Filament only
- [ ] Return brand assets / app metadata endpoint (logo URL, favicon) for mobile splash/branding

### Push Notifications

- [ ] Add `device_token` (nullable) column to `users` table via migration
- [ ] Create a notification channel for FCM (Firebase Cloud Messaging) or APNs
- [ ] Send push notification when a duty is assigned or updated
- [ ] Send push notification for new group invitations

### Infrastructure & Testing

- [x] Add API versioning prefix (`/api/v1`) and apply `auth:sanctum` middleware to protected routes
- [x] Add rate limiting to API routes (`throttle:api` or custom limiter)
- [x] Write Pest feature tests for all API endpoints (auth, groups, duties, invitations)
- [x] Ensure all API responses follow a consistent JSON structure (data, message, errors)

## General

- [ ] Create group section on superadmin panel
- [ ] Make cleaning period manually editable (allow entering the period manually)
- [ ] Make the app responsive
- [ ] Add SEO (meta tags, titles, descriptions, OG tags, sitemap)

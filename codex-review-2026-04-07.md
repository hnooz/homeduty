# Codex Review — 2026-04-07

## Issues

### 🔴 Blocker — Unrestricted impersonation
- **Files:** `app/Models/User.php:24`, `routes/web.php:33`
- **Problem:** Lab404 impersonation defaults (`canImpersonate` / `canBeImpersonated` return `true`) are not overridden, and `Route::impersonate()` is only behind `auth`. Any authenticated user can impersonate any other account, including super admins — full privilege escalation.

### 🟠 Major — SMTP/Resend secrets stored & logged in plain text
- **Files:** `app/Models/Setting.php:13-19`, `app/Filament/Pages/EmailSettings.php:24-99`
- **Problem:** Mail credentials are stored unencrypted in the `settings` table and recorded verbatim in Spatie activity logs. Anyone with DB or activity-log read access sees the secrets.

### 🟠 Major — `syncRoles()` strips elevated roles
- **Files:** `app/Services/Groups/AcceptGroupInvitation.php:50`, `app/Services/Groups/UpdateGroupMemberRole.php:24`
- **Problem:** Both services call `$user->syncRoles($role->toHomeDutyRole())`, which removes all other Spatie roles. Accepting an invitation or having a group role changed silently drops `SuperAdmin` / `AccessAdminPanel`.

### 🟡 Minor — Missing test coverage for the above
- **Files:** `tests/Feature/**`
- **Problem:** No tests assert impersonation is restricted or that elevated roles survive group-role changes, so regressions will go unnoticed.

## Todo

- [ ] Override `canImpersonate()` on `User` to allow only `SuperAdmin`.
- [ ] Override `canBeImpersonated()` on `User` to forbid impersonating `SuperAdmin`.
- [ ] Wrap `Route::impersonate()` (or guard it) with an admin-only middleware as defense-in-depth.
- [ ] Add `encrypted` cast (or equivalent) to `Setting::$value` for secret keys.
- [ ] Stop logging the `value` attribute on `Setting` via Spatie activity log (exclude or redact).
- [ ] Prefer env/secret storage for mail credentials where possible.
- [ ] Replace `syncRoles()` in `AcceptGroupInvitation` with logic that assigns the new group role without removing unrelated roles (e.g. remove prior `GroupMemberRole`-derived roles, then `assignRole`).
- [ ] Apply the same fix in `UpdateGroupMemberRole`.
- [ ] Add a feature test: non-admin user cannot hit the impersonate take/leave routes.
- [ ] Add a feature test: a `SuperAdmin` who accepts a group invitation retains the `SuperAdmin` role.
- [ ] Add a feature test: updating a group member's role does not remove their `SuperAdmin` / `AccessAdminPanel` roles.

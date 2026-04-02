# TODO

## Superadmin Dashboard

Move email settings out of the user settings area into a dedicated superadmin panel, and add group/member oversight.

### Role & Access

- [ ] Add `SuperAdmin` case to `HomeDutyRole` enum
- [ ] Update `SyncHomeDutyAuthorization` to handle the new role
- [ ] Add an artisan command to promote a user to superadmin
- [ ] Create `EnsureSuperAdmin` middleware and register it as `superadmin` alias in `bootstrap/app.php`

### Routes (`routes/admin.php`)

- [ ] `GET /admin` → redirect to `/admin/groups`
- [ ] `GET /admin/groups` → `Admin\GroupController@index`
- [ ] `GET /admin/groups/{group}` → `Admin\GroupController@show`
- [ ] `GET /admin/members` → `Admin\MemberController@index`
- [ ] `GET /admin/settings/email` → move `EmailSettingsController` to `App\Http\Controllers\Admin\`
- [ ] `PATCH /admin/settings/email` → same
- [ ] Remove `/settings/email` routes and the "Email" nav item from the user settings layout

### Backend

- [ ] `Admin\GroupController@index` — paginated list of all groups with owner name, member count, duty count, created date
- [ ] `Admin\GroupController@show` — group detail: members, duty schedule, invitation history, delete action
- [ ] `Admin\MemberController@index` — paginated list of all users with role, group name, joined date
- [ ] Move `EmailSettingsController` to `App\Http\Controllers\Admin\`

### Frontend

- [ ] `resources/js/layouts/AdminLayout.vue` — sidebar with Groups, Members, Email Settings links
- [ ] `resources/js/pages/admin/Groups.vue` — sortable/filterable table with stats
- [ ] `resources/js/pages/admin/Members.vue` — sortable/filterable table
- [ ] `resources/js/pages/admin/settings/Email.vue` — move from `settings/Email.vue`

### Improvements

- [ ] Stats cards on the admin home: total groups, total members, pending invitations, mailer status badge (shows current mailer + whether last mail succeeded)
- [ ] Send test email button on the email settings page — dispatches a test mail to the superadmin's address to verify config before saving
- [ ] Soft-delete groups instead of hard delete, with a restore option in the admin panel
- [ ] Impersonate any user from the admin panel for debugging (e.g. `404labfr/laravel-impersonate`)
- [ ] Audit log for settings changes — track who changed the mailer config and when (Spatie Activity Log or a custom `activity_log` table)
- [ ] Export members/groups as CSV from the admin tables

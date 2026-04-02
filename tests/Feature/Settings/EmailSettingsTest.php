<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var User $user */
    $user = User::factory()->createOne();
    $this->actingAs($user);
});

it('shows the email settings page', function () {
    $this->get(route('email-settings.edit'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('settings/Email'));
});

it('redirects guests away from email settings', function () {
    auth()->logout();

    $this->get(route('email-settings.edit'))
        ->assertRedirect(route('login'));
});

it('saves email settings to the database', function () {
    $this->patch(route('email-settings.update'), [
        'mail_mailer' => 'smtp',
        'mail_host' => 'smtp.example.com',
        'mail_port' => 587,
        'mail_username' => 'user@example.com',
        'mail_password' => 'secret',
        'mail_encryption' => 'tls',
        'mail_from_address' => 'noreply@example.com',
        'mail_from_name' => 'HomeDuty',
    ])->assertRedirect(route('email-settings.edit'));

    expect(Setting::get('mail_mailer'))->toBe('smtp')
        ->and(Setting::get('mail_host'))->toBe('smtp.example.com')
        ->and(Setting::get('mail_port'))->toBe('587')
        ->and(Setting::get('mail_from_address'))->toBe('noreply@example.com')
        ->and(Setting::get('mail_from_name'))->toBe('HomeDuty');
});

it('overrides existing settings on update', function () {
    Setting::set('mail_mailer', 'log');

    $this->patch(route('email-settings.update'), [
        'mail_mailer' => 'smtp',
        'mail_host' => 'smtp.example.com',
        'mail_port' => 465,
        'mail_encryption' => 'ssl',
        'mail_from_address' => 'hello@example.com',
        'mail_from_name' => 'HomeDuty',
    ])->assertRedirect(route('email-settings.edit'));

    expect(Setting::get('mail_mailer'))->toBe('smtp');
    expect(Setting::query()->where('key', 'mail_mailer')->count())->toBe(1);
});

it('saves resend settings to the database', function () {
    $this->patch(route('email-settings.update'), [
        'mail_mailer' => 'resend',
        'resend_api_key' => 're_test_abc123',
        'mail_from_address' => 'noreply@example.com',
        'mail_from_name' => 'HomeDuty',
    ])->assertRedirect(route('email-settings.edit'));

    expect(Setting::get('mail_mailer'))->toBe('resend')
        ->and(Setting::get('resend_api_key'))->toBe('re_test_abc123');
});

it('requires resend api key when mailer is resend', function () {
    $this->patch(route('email-settings.update'), [
        'mail_mailer' => 'resend',
        'resend_api_key' => '',
        'mail_from_address' => 'noreply@example.com',
        'mail_from_name' => 'HomeDuty',
    ])->assertSessionHasErrors(['resend_api_key']);
});

it('validates required fields', function () {
    $this->patch(route('email-settings.update'), [
        'mail_mailer' => '',
        'mail_from_address' => '',
        'mail_from_name' => '',
    ])->assertSessionHasErrors(['mail_mailer', 'mail_from_address', 'mail_from_name']);
});

it('validates from address is a valid email', function () {
    $this->patch(route('email-settings.update'), [
        'mail_mailer' => 'smtp',
        'mail_from_address' => 'not-an-email',
        'mail_from_name' => 'HomeDuty',
    ])->assertSessionHasErrors(['mail_from_address']);
});

it('passes existing settings to the view', function () {
    Setting::set('mail_mailer', 'smtp');
    Setting::set('mail_from_address', 'test@example.com');

    $this->get(route('email-settings.edit'))
        ->assertInertia(fn ($page) => $page
            ->component('settings/Email')
            ->where('emailSettings.mail_mailer', 'smtp')
            ->where('emailSettings.mail_from_address', 'test@example.com')
        );
});

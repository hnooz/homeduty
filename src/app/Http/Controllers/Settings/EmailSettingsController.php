<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\EmailSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EmailSettingsController extends Controller
{
    /** @var list<string> */
    private const array EMAIL_KEYS = [
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'resend_api_key',
    ];

    /**
     * Show the email settings page.
     */
    public function edit(): Response
    {
        $settings = Setting::query()
            ->whereIn('key', self::EMAIL_KEYS)
            ->pluck('value', 'key')
            ->toArray();

        return Inertia::render('settings/Email', [
            'emailSettings' => $settings,
        ]);
    }

    /**
     * Update the email settings.
     */
    public function update(EmailSettingsRequest $request): RedirectResponse
    {
        foreach ($request->validated() as $key => $value) {
            Setting::set($key, $value);
        }

        return to_route('email-settings.edit');
    }
}

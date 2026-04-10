<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;

class EmailSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    protected string $view = 'filament.pages.email-settings';

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 100;

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

    /** @var array<string, mixed> */
    public array $data = [];

    public function mount(): void
    {
        $settings = Setting::query()
            ->whereIn('key', self::EMAIL_KEYS)
            ->pluck('value', 'key')
            ->toArray();

        $this->form->fill($settings);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('mail_mailer')
                    ->label('Mailer')
                    ->options([
                        'smtp' => 'SMTP',
                        'sendmail' => 'Sendmail',
                        'resend' => 'Resend',
                        'log' => 'Log',
                        'array' => 'Array',
                    ])
                    ->required()
                    ->reactive(),
                TextInput::make('resend_api_key')
                    ->label('Resend API Key')
                    ->password()
                    ->revealable()
                    ->maxLength(255)
                    ->visible(fn ($get): bool => $get('mail_mailer') === 'resend')
                    ->requiredIf('mail_mailer', 'resend'),
                TextInput::make('mail_host')
                    ->label('Host')
                    ->maxLength(255),
                TextInput::make('mail_port')
                    ->label('Port')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(65535),
                TextInput::make('mail_username')
                    ->label('Username')
                    ->maxLength(255),
                TextInput::make('mail_password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->maxLength(255),
                Select::make('mail_encryption')
                    ->label('Encryption')
                    ->options([
                        '' => 'None',
                        'tls' => 'TLS',
                        'ssl' => 'SSL',
                    ]),
                TextInput::make('mail_from_address')
                    ->label('From Address')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('mail_from_name')
                    ->label('From Name')
                    ->required()
                    ->maxLength(255),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->title('Email settings saved')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendTestEmail')
                ->label('Send Test Email')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->action(function (): void {
                    try {
                        Mail::raw('This is a test email from HomeDuty.', function ($message): void {
                            $message->to(auth()->user()->email)
                                ->subject('HomeDuty - Test Email');
                        });

                        Notification::make()
                            ->title('Test email sent')
                            ->body('Check your inbox at '.auth()->user()->email)
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Failed to send test email')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}

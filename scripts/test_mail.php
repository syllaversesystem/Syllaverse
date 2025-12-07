<?php
require __DIR__ . '/../vendor/autoload.php';
/** Bootstrap Laravel **/
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Illuminate\Support\Facades\Log::info('Sending test email via MAIL_MAILER', [
    'mailer' => config('mail.default'),
]);

try {
    Illuminate\Support\Facades\Mail::raw('Test email from test_mail.php', function ($m) {
        $m->to('syllaverse.system@gmail.com')->subject('Mail test');
    });
    echo "OK\n";
} catch (Throwable $e) {
    fwrite(STDERR, "MAIL ERROR: " . $e->getMessage() . "\n");
    Illuminate\Support\Facades\Log::error('Mail send failed', [
        'error' => $e->getMessage(),
    ]);
    exit(1);
}
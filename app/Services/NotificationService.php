<?php

namespace App\Services;

use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\User;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

/**
 * Drives the "Customer Communication" feature from the project brief (section 6).
 *
 * Email is a real, working channel (sent through whatever MAIL_MAILER is configured —
 * defaults to the `log` driver locally, so nothing breaks without SMTP credentials).
 * WhatsApp is logged as a ready-to-wire stub: swap `simulateWhatsapp()` for a real
 * Twilio / Meta Cloud API / local gateway call once AutoForce has credentials.
 */
class NotificationService
{
    private const TEMPLATES = [
        'checked_in' => 'Your vehicle :reg has arrived at AutoForce and is awaiting inspection.',
        'inspection_completed' => 'Your vehicle :reg inspection report is ready. View findings: :link',
        'approval_required' => 'We have identified additional work for :reg. Please review the quotation and approve or decline: :link',
        'vehicle_ready' => 'Your vehicle :reg repairs are complete and your vehicle is ready for collection.',
        'service_reminder' => 'Your vehicle :reg is approaching its next recommended service interval.',
    ];

    /**
     * When a brand-new customer account is created (e.g. during vehicle registration
     * at the counter), they have no idea what their password is — it's a random
     * string nobody ever sees. Email them a "set your password" link so they can
     * actually log in to the customer portal, instead of emailing a plaintext password.
     */
    public static function notifyNewCustomerAccount(User $customer): ?NotificationLog
    {
        $isRealEmail = $customer->email && ! str_ends_with($customer->email, '@customer.autoforce.local');

        if (! $isRealEmail) {
            return null;
        }

        $status = 'simulated';
        $message = "Welcome to AutoForce! An account has been created for you to track your vehicle's service "
            ."history online. Log in with this email address ({$customer->email}) — use the link below to set your password first.";

        try {
            Password::sendResetLink(['email' => $customer->email]);
            $status = 'sent';
        } catch (\Throwable $e) {
            $status = 'failed';
            Log::warning('AutoForce welcome email failed: '.$e->getMessage());
        }

        return NotificationLog::create([
            'vehicle_id' => null,
            'inspection_id' => null,
            'customer_id' => $customer->id,
            'type' => 'welcome_account_created',
            'channel' => 'email',
            'to' => $customer->email,
            'message' => $message,
            'status' => $status,
        ]);
    }

    public static function notify(Vehicle $vehicle, string $type, ?Inspection $inspection = null): NotificationLog
    {
        $customer = $vehicle->customer;

        $message = str_replace(
            [':reg', ':link'],
            [$vehicle->registration_number, $inspection?->portalUrl() ?? ''],
            self::TEMPLATES[$type] ?? $type
        );

        self::sendEmail($vehicle, $inspection, $customer, $type, $message);

        return self::sendWhatsapp($vehicle, $inspection, $customer, $type, $message);
    }

    private static function sendEmail(Vehicle $vehicle, ?Inspection $inspection, $customer, string $type, string $message): void
    {
        $isRealEmail = $customer->email && ! str_ends_with($customer->email, '@customer.autoforce.local');
        $status = 'simulated';

        if ($isRealEmail) {
            try {
                Mail::raw($message, function ($mail) use ($customer) {
                    $mail->to($customer->email)->subject('AutoForce UK — Vehicle Update');
                });
                $status = 'sent';
            } catch (\Throwable $e) {
                $status = 'failed';
                Log::warning('AutoForce email notification failed: '.$e->getMessage());
            }
        }

        NotificationLog::create([
            'vehicle_id' => $vehicle->id,
            'inspection_id' => $inspection?->id,
            'customer_id' => $customer->id,
            'type' => $type,
            'channel' => 'email',
            'to' => $customer->email,
            'message' => $message,
            'status' => $status,
        ]);
    }

    /**
     * Emails the customer their invoice with the PDF attached, and logs a WhatsApp
     * stub entry too. Separate from notify() because it needs to attach a file.
     */
    public static function notifyInvoiceIssued(Invoice $invoice): NotificationLog
    {
        $vehicle = $invoice->vehicle;
        $customer = $invoice->customer;

        $message = "Your invoice {$invoice->invoice_number} for {$vehicle->registration_number} is ready — total PKR "
            .number_format($invoice->total).'. It is attached as a PDF and also available in your AutoForce customer portal.';

        $isRealEmail = $customer->email && ! str_ends_with($customer->email, '@customer.autoforce.local');
        $status = 'simulated';

        if ($isRealEmail) {
            try {
                $invoice->loadMissing('items');
                $pdf = Pdf::loadView('staff.invoices.pdf', ['invoice' => $invoice]);

                Mail::raw($message, function ($mail) use ($customer, $invoice, $pdf) {
                    $mail->to($customer->email)
                        ->subject('AutoForce UK — Invoice '.$invoice->invoice_number)
                        ->attachData($pdf->output(), $invoice->invoice_number.'.pdf', ['mime' => 'application/pdf']);
                });
                $status = 'sent';
            } catch (\Throwable $e) {
                $status = 'failed';
                Log::warning('AutoForce invoice email failed: '.$e->getMessage());
            }
        }

        NotificationLog::create([
            'vehicle_id' => $vehicle->id,
            'inspection_id' => $invoice->inspection_id,
            'customer_id' => $customer->id,
            'type' => 'invoice_issued',
            'channel' => 'email',
            'to' => $customer->email,
            'message' => $message,
            'status' => $status,
        ]);

        return NotificationLog::create([
            'vehicle_id' => $vehicle->id,
            'inspection_id' => $invoice->inspection_id,
            'customer_id' => $customer->id,
            'type' => 'invoice_issued',
            'channel' => 'whatsapp',
            'to' => $customer->phone,
            'message' => $message,
            'status' => 'simulated',
        ]);
    }

    /**
     * A technician/manager posted a progress update on a job while it's being
     * worked on. The customer gets emailed immediately so they can follow the
     * repair as it happens, not just at the start/end of the job.
     */
    public static function notifyJobComment(\App\Models\JobComment $comment): NotificationLog
    {
        $inspection = $comment->inspection;
        $vehicle = $inspection->vehicle;
        $customer = $vehicle->customer;

        $message = "Update on your vehicle {$vehicle->registration_number} ({$inspection->statusLabel()}) from "
            ."{$comment->author->name}: \"{$comment->body}\" — View full details: {$inspection->portalUrl()}";

        self::sendEmail($vehicle, $inspection, $customer, 'job_comment', $message);

        return self::sendWhatsapp($vehicle, $inspection, $customer, 'job_comment', $message);
    }

    private static function sendWhatsapp(Vehicle $vehicle, ?Inspection $inspection, $customer, string $type, string $message): NotificationLog
    {
        // Stub channel — no WhatsApp Business API credentials configured yet.
        // Wire a real provider here (Twilio, Meta Cloud API, or a local gateway) and
        // flip the status to 'sent'/'failed' based on the provider's response.
        return NotificationLog::create([
            'vehicle_id' => $vehicle->id,
            'inspection_id' => $inspection?->id,
            'customer_id' => $customer->id,
            'type' => $type,
            'channel' => 'whatsapp',
            'to' => $customer->phone,
            'message' => $message,
            'status' => 'simulated',
        ]);
    }
}

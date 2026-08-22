<?php

declare(strict_types=1);

namespace App\Mail;

use App\Support\Contact;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * One Mailable for every message the clinic sends.
 *
 * A Mailable rather than a MailMessage, for one reason: MailMessage derives
 * its plain-text part by re-rendering the Markdown, and that output is
 * genuinely poor — link URLs collapsed into bare text, table pipes left in
 * place, the button rendered as a naked https:// line. A Mailable accepts a
 * hand-written text view alongside the Markdown one
 * (Mailable::buildMarkdownText prefers $textView), which is the only way to
 * get a real plain-text alternative out of the Markdown pipeline.
 *
 * Parameterised rather than subclassed eight times because nothing differs
 * between the messages except the view, the subject and the data — eight
 * near-identical classes would be eight places to forget the Reply-To.
 */
class AppointmentMailable extends Mailable
{
    /**
     * Property names avoid $view, $subject and $markdown: Mailable already
     * declares all three as ordinary properties, and redeclaring one as
     * readonly is a fatal error rather than an override.
     *
     * @param  string  $templateName  view name under emails/, e.g. 'booking-confirmed'
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private readonly string $templateName,
        private readonly string $subjectLine,
        private readonly array $payload,
        private readonly bool $replyToClinic = true,
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = $this->replyToClinic ? Contact::email() : null;

        return new Envelope(
            subject: $this->subjectLine,
            /*
             * Automated mail SENDS from no-reply@ and REPLIES to info@.
             *
             * The split matters in both directions. Sending from info@ would
             * put every bounce, every out-of-office and every autoresponder
             * into the mailbox a human reads, and reception would start
             * ignoring it. But a patient who receives an appointment
             * confirmation and hits Reply is not doing anything unusual — she
             * is answering the clinic — and if that lands at no-reply@ her
             * question about her own appointment is silently discarded.
             *
             * So the envelope carries both: the identity we send as, and a
             * mailbox a person actually opens. The footer says so in words as
             * well, because nobody reads headers.
             */
            replyTo: $replyTo === null ? [] : [$replyTo],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.'.$this->templateName,
            // Hand-written, not derived. See the class docblock.
            text: 'emails.text.'.$this->templateName,
            with: $this->payload,
        );
    }
}

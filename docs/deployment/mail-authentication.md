# Mail authentication: SPF, DKIM and DMARC

SPF, DKIM and DMARC are configured on `rehletsehha.com`. DMARC is currently at
`p=none`.

## Move DMARC to `p=quarantine` after two weeks of clean delivery

`p=none` means: check alignment, report what you find, and deliver the message
either way. It is the correct setting for the first weeks, because it collects
evidence without any risk of a legitimate message being filed as spam. It also
provides no protection — anyone can send mail claiming to be from
`rehletsehha.com` and receiving servers will still deliver it.

For a clinic that is a real exposure. A convincing "your appointment has been
moved, click here" from a spoofed clinic address is a plausible way to phish a
patient, and patients here have been trained by the real system to expect
exactly that message.

**After two weeks of clean aggregate reports** — meaning the reports show all
legitimate mail passing SPF and DKIM alignment, and no surprises from a service
nobody remembered was sending as the domain — raise the policy:

```
v=DMARC1; p=quarantine; rua=mailto:dmarc@rehletsehha.com; pct=100; adkim=s; aspf=s
```

Then, after another clean fortnight at `quarantine`, consider `p=reject`.

Read the aggregate reports before each change. The failure this sequence exists
to prevent is discovering, only after tightening the policy, that some
forgotten sender — a contact form on an old page, an invoicing tool, the
hosting control panel's own notifications — was sending as the domain and is
now being quarantined.

## Nothing in the application depends on this change

This is a DNS change, made in the domain's DNS panel. **No application code,
configuration or environment variable reads the DMARC policy**, and nothing in
the codebase behaves differently at `none`, `quarantine` or `reject`.

Mail is sent through Hostinger SMTP as `no-reply@rehletsehha.com` (see
`.env.example`), which is what SPF and DKIM are already aligned for. Tightening
DMARC changes how *receiving* servers treat mail that fails those checks. It
does not change what we send, and it cannot break the application.

The one operational consequence: if a future task adds a second sending path —
a transactional provider, a CRM, a mailing list tool — that path must be added
to SPF and given a DKIM key **before** it sends anything, or its mail will be
quarantined once the policy is raised. That is a reason to keep the sending
surface small, not a reason to stay at `p=none`.

@component('emails.layout', ['subject' => 'Selamat! Anda Diterima - ' . $vacancy_title, 'preheader' => 'Selamat! Anda berhasil diterima bekerja di ' . config('app.name') . '. Selamat datang di tim kami!'])

    <!-- ── Success Icon ──────────────────────────────────── -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
        <tr>
            <td align="center">
                <!-- Heroicon: check-circle (solid) in teal circle -->
                <div style="
                    display:inline-block;
                    width:72px;
                    height:72px;
                    border-radius:50%;
                    background:linear-gradient(135deg,#0d9488,#0f766e);
                    text-align:center;
                    line-height:72px;
                    box-shadow:0 8px 24px rgba(13,148,136,0.3);
                ">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ffffff" width="36" height="36" style="display:inline-block;vertical-align:middle;">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                    </svg>
                </div>
            </td>
        </tr>
    </table>

    <!-- ── Heading ───────────────────────────────────────── -->
    <h1 style="margin:0 0 8px;font-size:26px;font-weight:800;color:#18181b;letter-spacing:-0.5px;line-height:1.2;text-align:center;">
        Selamat, Anda Diterima!
    </h1>
    <p style="margin:0 0 28px;font-size:15px;color:#71717a;line-height:1.6;text-align:center;">
        Halo <strong style="color:#18181b;">{{ $candidate_name }}</strong>
    </p>

    <!-- ── Intro ─────────────────────────────────────────── -->
    <p style="margin:0 0 24px;font-size:15px;color:#3f3f46;line-height:1.7;">
        Kami dengan bangga memberitahu bahwa Anda telah <strong>berhasil melewati seluruh tahap seleksi</strong>
        dan diterima untuk bergabung bersama tim kami.
    </p>

    <!-- ── Position Info Card ────────────────────────────── -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background:linear-gradient(135deg,#f0fdfa 0%,#ccfbf1 100%);border:1px solid #99f6e4;border-radius:12px;margin-bottom:28px;">
        <tr>
            <td style="padding:20px 24px;">
                <table role="presentation" class="two-col" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="50%" style="vertical-align:top;padding-right:12px;padding-bottom:12px;">
                            <span style="font-size:11px;font-weight:700;letter-spacing:1px;color:#0f766e;text-transform:uppercase;">Posisi</span>
                            <p style="margin:4px 0 0;font-size:15px;font-weight:700;color:#134e4a;">{{ $position_name }}</p>
                        </td>
                        <td width="50%" style="vertical-align:top;padding-bottom:12px;">
                            <span style="font-size:11px;font-weight:700;letter-spacing:1px;color:#0f766e;text-transform:uppercase;">Departemen</span>
                            <p style="margin:4px 0 0;font-size:15px;font-weight:700;color:#134e4a;">{{ $department_name ?? '-' }}</p>
                        </td>
                    </tr>
                    @if(!empty($start_date))
                    <tr>
                        <td colspan="2" style="border-top:1px solid #99f6e4;padding-top:12px;">
                            <!-- Heroicon: calendar-days -->
                            <span style="font-size:11px;font-weight:700;letter-spacing:1px;color:#0f766e;text-transform:uppercase;display:inline-flex;align-items:center;gap:4px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="12" height="12" style="display:inline;vertical-align:middle;flex-shrink:0;">
                                    <path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" />
                                </svg>
                                Tanggal Mulai
                            </span>
                            <p style="margin:4px 0 0;font-size:15px;font-weight:700;color:#134e4a;">{{ $start_date }}</p>
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- ── Next Steps ────────────────────────────────────── -->
    @if(!empty($next_steps))
    <div style="background-color:#fafafa;border:1px solid #e4e4e7;border-radius:10px;padding:18px 20px;margin-bottom:28px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:10px;">
            <tr>
                <td width="20" style="vertical-align:middle;padding-right:8px;">
                    <!-- Heroicon: clipboard-document-list -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#18181b" width="16" height="16" style="display:block;">
                        <path d="M8 3.5A1.5 1.5 0 019.5 2h1A1.5 1.5 0 0112 3.5v.5h-4v-.5z" />
                        <path fill-rule="evenodd" d="M6.5 4H5a2 2 0 00-2 2v9a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2h-1.5v.75a.75.75 0 01-.75.75h-5a.75.75 0 01-.75-.75V4zM7 9.75a.75.75 0 000 1.5h6a.75.75 0 000-1.5H7zm0 3a.75.75 0 000 1.5h6a.75.75 0 000-1.5H7zM7 7a.75.75 0 000 1.5h6A.75.75 0 0013 7H7z" clip-rule="evenodd" />
                    </svg>
                </td>
                <td>
                    <span style="font-size:13px;font-weight:700;color:#18181b;text-transform:uppercase;letter-spacing:0.5px;">Langkah Selanjutnya</span>
                </td>
            </tr>
        </table>
        <p style="margin:0;font-size:14px;color:#52525b;line-height:1.7;">{{ $next_steps }}</p>
    </div>
    @endif

    <!-- ── Credentials ───────────────────────────────────── -->
    @if(!empty($login_email))
    <div style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:18px 20px;margin-bottom:28px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:10px;">
            <tr>
                <td width="20" style="vertical-align:middle;padding-right:8px;">
                    <!-- Heroicon: key (solid) -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#475569" width="16" height="16" style="display:block;">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-5.5-2.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM10 12a5.99 5.99 0 00-4.793 2.39A6.48 6.48 0 0010 16.5c1.673 0 3.205-.63 4.793-2.11A5.99 5.99 0 0010 12z" clip-rule="evenodd" />
                    </svg>
                </td>
                <td>
                    <span style="font-size:13px;font-weight:700;color:#1e293b;text-transform:uppercase;letter-spacing:0.5px;">Kredensial Akun Karyawan</span>
                </td>
            </tr>
        </table>
        <p style="margin:0 0 12px;font-size:14px;color:#475569;line-height:1.6;">
            Akun Anda telah dikonfigurasi untuk mengakses Dashboard Karyawan. Silakan gunakan kredensial berikut untuk masuk:
        </p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size:14px;color:#1e293b;background-color:#ffffff;border:1px solid #f1f5f9;border-radius:6px;padding:10px 12px;">
            <tr>
                <td width="80" style="padding:4px 0;color:#64748b;font-weight:600;">Email:</td>
                <td style="padding:4px 0;font-family:monospace;font-weight:700;color:#0f766e;">{{ $login_email }}</td>
            </tr>
            <tr>
                <td style="padding:4px 0;color:#64748b;font-weight:600;">Password:</td>
                <td style="padding:4px 0;font-family:monospace;font-weight:700;color:#0f766e;">{{ $login_password }}</td>
            </tr>
        </table>
    </div>
    @endif

    <!-- ── Body text ─────────────────────────────────────── -->
    <p style="margin:0 0 28px;font-size:14px;color:#52525b;line-height:1.7;">
        Tim HR kami akan segera menghubungi Anda dengan informasi lebih lanjut mengenai onboarding.
        Jangan ragu untuk menghubungi kami jika memiliki pertanyaan.
    </p>

    <!-- ── CTA Button ────────────────────────────────────── -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td align="center">
                <a href="{{ $portal_url ?? config('app.url') }}"
                   class="btn-primary"
                   style="
                       display:inline-block;
                       background:linear-gradient(135deg,#0d9488 0%,#0f766e 100%);
                       color:#ffffff;
                       text-decoration:none;
                       font-size:14px;
                       font-weight:700;
                       letter-spacing:0.3px;
                       padding:14px 36px;
                       border-radius:10px;
                       box-shadow:0 4px 14px rgba(13,148,136,0.35);
                   ">
                    Buka Portal Lamaran &rarr;
                </a>
            </td>
        </tr>
    </table>

    <!-- ── Divider ───────────────────────────────────────── -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
        <tr>
            <td style="border-top:1px solid #e4e4e7;"></td>
        </tr>
    </table>

    <p style="margin:0;font-size:13px;color:#a1a1aa;line-height:1.6;text-align:center;">
        Selamat bergabung! Kami sangat senang memiliki Anda di tim kami.
    </p>

@endcomponent

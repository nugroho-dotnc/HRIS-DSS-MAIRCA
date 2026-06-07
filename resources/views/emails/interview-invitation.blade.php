@component('emails.layout', ['subject' => 'Undangan Interview - ' . $vacancy_title, 'preheader' => 'Selamat! Anda diundang untuk mengikuti sesi interview di ' . config('app.name') . '.'])

    <!-- ── Greeting ─────────────────────────────────────── -->
    <h1 style="margin:0 0 8px;font-size:26px;font-weight:800;color:#18181b;letter-spacing:-0.5px;line-height:1.2;">
        Undangan Interview
    </h1>
    <p style="margin:0 0 24px;font-size:15px;color:#71717a;line-height:1.6;">
        Halo <strong style="color:#18181b;">{{ $candidate_name }}</strong>, selamat!
    </p>

    <!-- ── Intro text ────────────────────────────────────── -->
    <p style="margin:0 0 24px;font-size:15px;color:#3f3f46;line-height:1.7;">
        Kami dengan senang hati mengundang Anda untuk mengikuti tahap <strong>wawancara (interview)</strong>
        atas lamaran Anda pada posisi berikut:
    </p>

    <!-- ── Position Info Card ────────────────────────────── -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background:linear-gradient(135deg,#f0fdfa 0%,#ccfbf1 100%);border:1px solid #99f6e4;border-radius:12px;margin-bottom:28px;">
        <tr>
            <td style="padding:20px 24px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="padding-bottom:12px;">
                            <span style="font-size:11px;font-weight:700;letter-spacing:1px;color:#0f766e;text-transform:uppercase;">Posisi Dilamar</span>
                            <p style="margin:4px 0 0;font-size:17px;font-weight:700;color:#134e4a;">{{ $vacancy_title }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="border-top:1px solid #99f6e4;padding-top:12px;">
                            <table role="presentation" class="two-col" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td width="50%" style="vertical-align:top;padding-right:12px;">
                                        <!-- Heroicon: calendar-days -->
                                        <span style="font-size:11px;color:#0f766e;font-weight:600;display:inline-flex;align-items:center;gap:4px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="12" height="12" style="display:inline;vertical-align:middle;flex-shrink:0;">
                                                <path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" />
                                            </svg>
                                            Tanggal
                                        </span>
                                        <p style="margin:2px 0 0;font-size:14px;font-weight:600;color:#134e4a;">{{ $interview_date }}</p>
                                    </td>
                                    <td width="50%" style="vertical-align:top;">
                                        <!-- Heroicon: clock -->
                                        <span style="font-size:11px;color:#0f766e;font-weight:600;display:inline-flex;align-items:center;gap:4px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="12" height="12" style="display:inline;vertical-align:middle;flex-shrink:0;">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
                                            </svg>
                                            Waktu
                                        </span>
                                        <p style="margin:2px 0 0;font-size:14px;font-weight:600;color:#134e4a;">{{ $interview_time }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @if(!empty($interview_location))
                    <tr>
                        <td style="padding-top:12px;">
                            <!-- Heroicon: map-pin -->
                            <span style="font-size:11px;color:#0f766e;font-weight:600;display:inline-flex;align-items:center;gap:4px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="12" height="12" style="display:inline;vertical-align:middle;flex-shrink:0;">
                                    <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" />
                                </svg>
                                Lokasi / Platform
                            </span>
                            <p style="margin:2px 0 0;font-size:14px;font-weight:600;color:#134e4a;">{{ $interview_location }}</p>
                        </td>
                    </tr>
                    @endif
                    @if(!empty($interviewer_name))
                    <tr>
                        <td style="padding-top:12px;">
                            <!-- Heroicon: user-circle -->
                            <span style="font-size:11px;color:#0f766e;font-weight:600;display:inline-flex;align-items:center;gap:4px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="12" height="12" style="display:inline;vertical-align:middle;flex-shrink:0;">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-5.5-2.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM10 12a5.99 5.99 0 00-4.793 2.39A6.483 6.483 0 0010 16.5a6.483 6.483 0 004.793-2.11A5.99 5.99 0 0010 12z" clip-rule="evenodd" />
                                </svg>
                                Pewawancara
                            </span>
                            <p style="margin:2px 0 0;font-size:14px;font-weight:600;color:#134e4a;">{{ $interviewer_name }}</p>
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- ── Notes ─────────────────────────────────────────── -->
    @if(!empty($notes))
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td style="background-color:#fffbeb;border-left:4px solid #f59e0b;border-radius:0 8px 8px 0;padding:14px 18px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="20" style="vertical-align:top;padding-right:8px;padding-top:1px;">
                            <!-- Heroicon: exclamation-triangle -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#d97706" width="16" height="16" style="display:block;">
                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                        </td>
                        <td>
                            <p style="margin:0;font-size:13px;color:#92400e;line-height:1.6;">
                                <strong>Catatan:</strong> {{ $notes }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    @endif

    <!-- ── Body text ─────────────────────────────────────── -->
    <p style="margin:0 0 24px;font-size:14px;color:#52525b;line-height:1.7;">
        Mohon untuk hadir tepat waktu. Jika Anda memiliki pertanyaan atau perlu menjadwal ulang,
        segera hubungi tim HR kami sebelum tanggal interview.
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
                    Lihat Detail di Portal &rarr;
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

    <p style="margin:0;font-size:13px;color:#a1a1aa;line-height:1.6;">
        Kami menantikan kehadiran Anda. Semoga sukses!
    </p>

@endcomponent

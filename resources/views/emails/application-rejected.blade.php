@component('emails.layout', ['subject' => 'Pembaruan Status Lamaran - ' . $vacancy_title, 'preheader' => 'Kami menghargai minat Anda dan perlu menyampaikan pembaruan mengenai lamaran Anda.'])

    <!-- ── Heading ───────────────────────────────────────── -->
    <h1 style="margin:0 0 8px;font-size:24px;font-weight:800;color:#18181b;letter-spacing:-0.5px;line-height:1.2;">
        Pembaruan Status Lamaran
    </h1>
    <p style="margin:0 0 28px;font-size:15px;color:#71717a;line-height:1.6;">
        Halo <strong style="color:#18181b;">{{ $candidate_name }}</strong>,
    </p>

    <!-- ── Intro ─────────────────────────────────────────── -->
    <p style="margin:0 0 24px;font-size:15px;color:#3f3f46;line-height:1.7;">
        Terima kasih atas ketertarikan Anda dalam melamar di <strong>{{ config('app.name', 'HRIS') }}</strong>
        dan waktu yang telah Anda investasikan dalam proses seleksi kami.
    </p>

    <!-- ── Position Info Card ────────────────────────────── -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color:#fafafa;border:1px solid #e4e4e7;border-radius:12px;margin-bottom:24px;">
        <tr>
            <td style="padding:18px 20px;">
                <span style="font-size:11px;font-weight:700;letter-spacing:1px;color:#71717a;text-transform:uppercase;">Posisi yang Dilamar</span>
                <p style="margin:4px 0 0;font-size:15px;font-weight:700;color:#18181b;">{{ $vacancy_title }}</p>
                @if(!empty($position_name))
                <p style="margin:2px 0 0;font-size:13px;color:#71717a;">{{ $position_name }}</p>
                @endif
            </td>
        </tr>
    </table>

    <!-- ── Status indicator ──────────────────────────────── -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background:linear-gradient(135deg,#fef2f2 0%,#fee2e2 100%);border:1px solid #fecaca;border-left:4px solid #ef4444;border-radius:0 12px 12px 0;margin-bottom:24px;">
        <tr>
            <td style="padding:16px 20px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="22" style="vertical-align:middle;padding-right:10px;">
                            <!-- Heroicon: x-circle -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#991b1b" width="18" height="18" style="display:block;">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        </td>
                        <td>
                            <p style="margin:0;font-size:14px;font-weight:700;color:#991b1b;">
                                Status: Tidak Melanjutkan ke Tahap Berikutnya
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ── Main message ──────────────────────────────────── -->
    <p style="margin:0 0 16px;font-size:14px;color:#52525b;line-height:1.7;">
        Setelah melalui proses evaluasi yang cermat, kami mohon maaf untuk menyampaikan bahwa
        lamaran Anda <strong>tidak dapat kami lanjutkan</strong> ke tahap seleksi berikutnya pada
        kesempatan kali ini.
    </p>

    @if(!empty($rejection_reason))
    <p style="margin:0 0 20px;font-size:14px;color:#52525b;line-height:1.7;">
        <strong>Alasan:</strong> {{ $rejection_reason }}
    </p>
    @endif

    <!-- ── Encouragement box ─────────────────────────────── -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
        <tr>
            <td style="background-color:#f0fdfa;border:1px solid #99f6e4;border-radius:10px;padding:16px 20px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="22" style="vertical-align:top;padding-right:10px;padding-top:1px;">
                            <!-- Heroicon: light-bulb -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#0d9488" width="18" height="18" style="display:block;">
                                <path d="M10 1a6 6 0 00-3.815 10.631C7.237 12.5 8 13.443 8 14.456v.644a.75.75 0 00.572.729 6.016 6.016 0 002.856 0A.75.75 0 0012 15.1v-.644c0-1.013.762-1.957 3.815-2.825A6 6 0 0010 1zM8.863 17.414a.75.75 0 00-.226 1.483 9.066 9.066 0 002.726 0 .75.75 0 00-.226-1.483 7.553 7.553 0 01-2.274 0z" />
                            </svg>
                        </td>
                        <td>
                            <p style="margin:0;font-size:13px;color:#134e4a;line-height:1.7;">
                                Keputusan ini tidak mencerminkan kemampuan Anda secara keseluruhan. Kami mendorong
                                Anda untuk terus berkembang dan mencoba peluang-peluang lain yang mungkin lebih sesuai.
                                Anda dapat terus memantau lowongan kami di portal.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 28px;font-size:14px;color:#52525b;line-height:1.7;">
        Kami berterima kasih atas waktu dan usaha yang Anda berikan selama proses ini,
        dan mendoakan yang terbaik untuk perjalanan karier Anda ke depan.
    </p>

    <!-- ── CTA Button ────────────────────────────────────── -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        <tr>
            <td align="center">
                <a href="{{ $vacancies_url ?? config('app.url').'/vacancies' }}"
                   class="btn-primary"
                   style="
                       display:inline-block;
                       background:linear-gradient(135deg,#0d9488 0%,#0f766e 100%);
                       color:#ffffff;
                       text-decoration:none;
                       font-size:14px;
                       font-weight:700;
                       letter-spacing:0.3px;
                       padding:14px 32px;
                       border-radius:10px;
                       box-shadow:0 4px 14px rgba(13,148,136,0.3);
                   ">
                    Lihat Lowongan Lainnya &rarr;
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
        Semoga sukses dan terima kasih telah mempertimbangkan {{ config('app.name', 'HRIS') }}.
    </p>

@endcomponent

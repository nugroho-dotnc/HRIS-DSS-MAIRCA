@component('emails.layout', ['subject' => 'Lamaran Anda Telah Diterima - ' . $vacancy_title, 'preheader' => 'Lamaran Anda sedang dalam proses peninjauan oleh tim HR kami.'])

    <!-- ── Heading ───────────────────────────────────────── -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:8px;">
        <tr>
            <td>
                <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="vertical-align:middle;padding-right:10px;">
                            <!-- Heroicon: paper-airplane -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#0d9488" width="28" height="28" style="display:block;">
                                <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z" />
                            </svg>
                        </td>
                        <td style="vertical-align:middle;">
                            <h1 style="margin:0;font-size:24px;font-weight:800;color:#18181b;letter-spacing:-0.5px;line-height:1.2;">
                                Lamaran Berhasil Dikirim
                            </h1>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <p style="margin:0 0 28px;font-size:15px;color:#71717a;line-height:1.6;">
        Halo <strong style="color:#18181b;">{{ $candidate_name }}</strong>,
    </p>

    <!-- ── Intro ─────────────────────────────────────────── -->
    <p style="margin:0 0 24px;font-size:15px;color:#3f3f46;line-height:1.7;">
        Terima kasih telah melamar di <strong>{{ config('app.name', 'HRIS') }}</strong>.
        Kami telah menerima lamaran Anda dan saat ini sedang dalam proses peninjauan.
    </p>

    <!-- ── Application Summary ───────────────────────────── -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background:linear-gradient(135deg,#f0fdfa 0%,#ccfbf1 100%);border:1px solid #99f6e4;border-radius:12px;margin-bottom:28px;">
        <tr>
            <td style="padding:20px 24px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="padding-bottom:14px;">
                            <span style="font-size:11px;font-weight:700;letter-spacing:1px;color:#0f766e;text-transform:uppercase;">Ringkasan Lamaran</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table role="presentation" class="two-col" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td width="40%" style="vertical-align:top;padding-bottom:10px;">
                                        <span style="font-size:11px;color:#0f766e;">Kode Lamaran</span>
                                        <p style="margin:2px 0 0;font-size:13px;font-weight:700;color:#134e4a;font-family:monospace;">{{ $application_code }}</p>
                                    </td>
                                    <td width="60%" style="vertical-align:top;padding-bottom:10px;">
                                        <span style="font-size:11px;color:#0f766e;">Posisi</span>
                                        <p style="margin:2px 0 0;font-size:13px;font-weight:700;color:#134e4a;">{{ $vacancy_title }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="border-top:1px solid #99f6e4;padding-top:10px;">
                                        <!-- Heroicon: calendar-days -->
                                        <span style="font-size:11px;color:#0f766e;display:inline-flex;align-items:center;gap:4px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="12" height="12" style="display:inline;vertical-align:middle;flex-shrink:0;">
                                                <path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" />
                                            </svg>
                                            Tanggal Lamaran
                                        </span>
                                        <p style="margin:2px 0 0;font-size:13px;font-weight:700;color:#134e4a;">{{ $applied_at }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ── Process timeline ──────────────────────────────── -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:14px;">
        <tr>
            <td width="20" style="vertical-align:middle;padding-right:8px;">
                <!-- Heroicon: clipboard-document-list -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#18181b" width="16" height="16" style="display:block;">
                    <path d="M8 3.5A1.5 1.5 0 019.5 2h1A1.5 1.5 0 0112 3.5v.5h-4v-.5z" />
                    <path fill-rule="evenodd" d="M6.5 4H5a2 2 0 00-2 2v9a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2h-1.5v.75a.75.75 0 01-.75.75h-5a.75.75 0 01-.75-.75V4zM7 9.75a.75.75 0 000 1.5h6a.75.75 0 000-1.5H7zm0 3a.75.75 0 000 1.5h6a.75.75 0 000-1.5H7zM7 7a.75.75 0 000 1.5h6A.75.75 0 0013 7H7z" clip-rule="evenodd" />
                </svg>
            </td>
            <td>
                <span style="font-size:13px;font-weight:700;color:#18181b;text-transform:uppercase;letter-spacing:0.5px;">Tahapan Seleksi</span>
            </td>
        </tr>
    </table>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
        @php
            $steps = [
                ['label' => 'Lamaran Diterima',       'done' => true],
                ['label' => 'Screening & Peninjauan', 'done' => false],
                ['label' => 'Interview',               'done' => false],
                ['label' => 'Keputusan Akhir',         'done' => false],
            ];
        @endphp
        @foreach($steps as $step)
        <tr>
            <td style="padding:6px 0;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="28" style="vertical-align:middle;padding-right:12px;">
                            @if($step['done'])
                            {{-- Heroicon: check-circle (solid teal) --}}
                            <div style="width:24px;height:24px;border-radius:50%;background-color:#0d9488;text-align:center;line-height:20px;padding-top:2px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#ffffff" width="14" height="14" style="display:inline-block;vertical-align:middle;">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            @else
                            {{-- Heroicon: ellipsis-horizontal (grey pending) --}}
                            <div style="width:24px;height:24px;border-radius:50%;background-color:#e4e4e7;text-align:center;line-height:24px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#a1a1aa" width="12" height="12" style="display:inline-block;vertical-align:middle;">
                                    <path d="M3 10a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM8.5 10a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM15.5 8.5a1.5 1.5 0 100 3 1.5 1.5 0 000-3z" />
                                </svg>
                            </div>
                            @endif
                        </td>
                        <td style="vertical-align:middle;">
                            <span style="font-size:14px;color:{{ $step['done'] ? '#0d9488' : '#71717a' }};font-weight:{{ $step['done'] ? '700' : '400' }};">
                                {{ $step['label'] }}
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endforeach
    </table>

    <!-- ── Info text ─────────────────────────────────────── -->
    <p style="margin:0 0 28px;font-size:14px;color:#52525b;line-height:1.7;">
        Kami akan menghubungi Anda melalui email ini jika ada pembaruan status.
        Anda juga dapat memantau status lamaran secara langsung melalui portal kandidat kami.
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
                    Lacak Status Lamaran &rarr;
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
        Simpan email ini sebagai bukti pengiriman lamaran Anda. Kode referensi: <strong style="font-family:monospace;color:#52525b;">{{ $application_code }}</strong>
    </p>

@endcomponent

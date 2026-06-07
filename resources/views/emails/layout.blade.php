<!DOCTYPE html>
<html lang="id" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <title>{{ $subject ?? config('app.name') }}</title>
    <style>
        /* ─── Reset ─────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }
        a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-family: inherit !important; font-weight: inherit !important; line-height: inherit !important; }
        div[style*="margin: 16px 0"] { margin: 0 !important; }

        /* ─── Design Tokens ─────────────────────────────────────── */
        /* Matching project color palette:
           Accent:    teal-600  #0d9488
           Dark bg:   zinc-900  #18181b
           Mid bg:    zinc-800  #27272a
           Light bg:  zinc-50   #fafafa
           Border:    zinc-200  #e4e4e7  /  zinc-700  #3f3f46
           Text dark: zinc-900  #18181b
           Text mid:  zinc-500  #71717a
           Text light:#ffffff
        */

        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f4f4f5; /* zinc-100 */
            margin: 0;
            padding: 0;
        }

        /* ─── Mobile responsive ─────────────────────────────────── */
        @media only screen and (max-width: 620px) {
            .wrapper { width: 100% !important; padding: 12px !important; }
            .card    { border-radius: 16px !important; }
            .content-pad { padding: 28px 20px !important; }
            .btn-primary { display: block !important; text-align: center !important; }
            .two-col td  { display: block !important; width: 100% !important; padding: 4px 0 !important; }
            h1 { font-size: 22px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:'Instrument Sans',ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">

    <!-- Preheader (hidden preview text) -->
    <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;">
        {{ $preheader ?? '' }}&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌
    </div>

    <!-- Outer wrapper -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f4f5;">
        <tr>
            <td align="center" style="padding:40px 16px 24px;">

                <!-- ── Header Logo Bar ─────────────────────────────── -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;margin-bottom:0;">
                    <tr>
                        <td align="center" style="padding:0 0 16px;">
                            <!-- Logo pill -->
                            <a href="{{ config('app.url') }}" style="text-decoration:none;" aria-label="{{ config('app.name') }} home">
                                <span style="
                                    display:inline-flex;
                                    align-items:center;
                                    gap:8px;
                                    background-color:#0d9488;
                                    border-radius:12px;
                                    padding:8px 20px;
                                    font-size:18px;
                                    font-weight:800;
                                    color:#ffffff;
                                    letter-spacing:-0.5px;
                                    text-decoration:none;
                                ">
                                    <!-- Heroicon: briefcase -->
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20" style="display:inline-block;vertical-align:middle;flex-shrink:0;">
                                        <path fill-rule="evenodd" d="M7.5 5.25a3 3 0 013-3h3a3 3 0 013 3v.205c.933.085 1.857.197 2.774.334 1.454.218 2.476 1.483 2.476 2.917v3.033c0 1.211-.734 2.352-1.936 2.752A24.726 24.726 0 0112 15.75c-2.73 0-5.357-.442-7.814-1.259-1.202-.4-1.936-1.541-1.936-2.752V8.706c0-1.434 1.022-2.7 2.476-2.917A48.814 48.814 0 017.5 5.455V5.25zm7.5 0v.09a49.488 49.488 0 00-6 0v-.09a1.5 1.5 0 011.5-1.5h3a1.5 1.5 0 011.5 1.5zm-3 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
                                        <path d="M3 18.4v-2.796a4.3 4.3 0 00.713.31A26.226 26.226 0 0012 17.25c2.892 0 5.68-.468 8.287-1.335.252-.084.49-.189.713-.311V18.4c0 1.452-1.047 2.728-2.523 2.923-2.12.282-4.282.427-6.477.427a49.19 49.19 0 01-6.477-.427C4.047 21.128 3 19.852 3 18.4z" />
                                    </svg>
                                    {{ config('app.name', 'HRIS') }}
                                </span>
                            </a>
                        </td>
                    </tr>
                </table>

                <!-- ── Card ───────────────────────────────────────── -->
                <table role="presentation" class="card" width="100%" cellpadding="0" cellspacing="0" border="0"
                       style="max-width:600px;background-color:#ffffff;border-radius:20px;border:1px solid #e4e4e7;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.07);">

                    <!-- Accent top bar -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);height:5px;line-height:5px;font-size:0;">&nbsp;</td>
                    </tr>

                    <!-- Card body -->
                    <tr>
                        <td class="content-pad" style="padding:40px 40px 32px;">
                            {{ $slot }}
                        </td>
                    </tr>

                    <!-- ── Footer ──────────────────────────────────── -->
                    <tr>
                        <td style="background-color:#fafafa;border-top:1px solid #e4e4e7;padding:24px 40px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center">
                                        <p style="margin:0 0 6px;font-size:12px;color:#71717a;line-height:1.5;">
                                            Email ini dikirim secara otomatis oleh sistem <strong style="color:#18181b;">{{ config('app.name', 'HRIS') }}</strong>.
                                            Harap tidak membalas email ini.
                                        </p>
                                        <p style="margin:0;font-size:11px;color:#a1a1aa;">
                                            &copy; {{ date('Y') }} {{ config('app.name', 'HRIS') }} &middot;
                                            <a href="{{ config('app.url') }}" style="color:#0d9488;text-decoration:none;">{{ parse_url(config('app.url'), PHP_URL_HOST) }}</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
                <!-- /Card -->

            </td>
        </tr>
    </table>

</body>
</html>

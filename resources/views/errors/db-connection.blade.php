<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('Layanan Sedang Sibuk') }}</title>

        <style>
            body {
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
                background: #f3f4f6;
                margin: 0;
            }
            .wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
            .card {
                max-width: 480px; width: 100%; text-align: center; background: #fff;
                border-radius: 16px; padding: 40px 32px; box-shadow: 0 10px 30px rgba(0,0,0,.08);
            }
            .code { font-size: 56px; font-weight: 800; color: #4f46e5; line-height: 1; }
            .title { font-size: 20px; font-weight: 700; color: #111827; margin-top: 12px; }
            .msg { font-size: 14px; color: #6b7280; margin-top: 10px; line-height: 1.6; }
            .btn-retry {
                display: inline-block; margin-top: 26px; padding: 12px 32px; border-radius: 8px;
                background: #4f46e5; color: #fff; font-weight: 600; font-size: 14px;
                text-decoration: none; border: none; cursor: pointer; transition: background .2s ease;
            }
            .btn-retry:hover { background: #4338ca; }
        </style>
    </head>
    <body>
        <div class="wrap">
            <div class="card">
                <div class="code">503</div>
                <div class="title">{{ __('Layanan Sedang Sibuk') }}</div>
                <div class="msg">
                    Server sedang sibuk memproses data. Silakan tunggu beberapa saat lalu coba kembali.<br>
                    <em>The server is busy processing data. Please wait a moment and try again.</em>
                </div>
                <button type="button" class="btn-retry" onclick="location.reload()">{{ __('Coba Lagi / Try Again') }}</button>
            </div>
        </div>
    </body>
</html>

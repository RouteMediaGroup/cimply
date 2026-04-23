<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\System {
    use Cimply\System\License\LicenseException;

    final class FailureRenderer
    {
        public static function render(\Throwable $exception, ?int $statusCode = null, array $context = []): never
        {
            $statusCode = self::resolveStatusCode($exception, $statusCode);
            $title = self::resolveTitle($statusCode, $exception);
            $lead = self::resolveLead($statusCode, $exception);
            $detail = trim($exception->getMessage());
            $hint = self::resolveHint($statusCode, $exception);
            $project = trim((string)($context['project'] ?? ''));
            $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');

            if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
                self::renderCli($statusCode, $title, $lead, $detail, $project, $requestUri);
            }

            http_response_code($statusCode);

            if (self::wantsJson($requestUri)) {
                if (!headers_sent()) {
                    header('Content-Type: application/json; charset=utf-8');
                }

                echo json_encode([
                    'status' => 'error',
                    'code' => $statusCode,
                    'title' => $title,
                    'message' => $lead,
                    'detail' => $detail !== '' ? $detail : null,
                    'hint' => $hint,
                    'framework' => 'Cimply.Work',
                    'version' => \Cimply\Work::VERSION,
                    'project' => $project !== '' ? $project : null,
                    'request_uri' => $requestUri,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                exit(1);
            }

            if (!headers_sent()) {
                header('Content-Type: text/html; charset=utf-8');
            }

            $titleHtml = self::escape($title);
            $leadHtml = self::escape($lead);
            $detailHtml = self::escape($detail !== '' ? $detail : 'No additional diagnostic message was provided.');
            $hintHtml = self::escape($hint);
            $projectHtml = self::escape($project !== '' ? $project : 'unknown');
            $requestUriHtml = self::escape($requestUri);
            $versionHtml = self::escape(\Cimply\Work::VERSION);
            $statusHtml = self::escape((string)$statusCode);

            echo <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$titleHtml}</title>
    <style>
        :root {
            color-scheme: light;
            --sand: #f4ede2;
            --paper: rgba(255, 251, 245, 0.88);
            --ink: #201a15;
            --muted: #70675b;
            --accent: #b24a1b;
            --accent-soft: rgba(178, 74, 27, 0.12);
            --border: rgba(96, 73, 52, 0.14);
            --shadow: 0 24px 70px rgba(40, 24, 10, 0.12);
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 12% 18%, rgba(178, 74, 27, 0.16), transparent 28%),
                radial-gradient(circle at 88% 10%, rgba(51, 90, 74, 0.12), transparent 22%),
                linear-gradient(180deg, #faf5ee 0%, var(--sand) 100%);
            color: var(--ink);
            font: 16px/1.6 "Iowan Old Style", "Palatino Linotype", "Book Antiqua", Georgia, serif;
            display: grid;
            place-items: center;
            padding: 28px;
        }
        .frame {
            width: min(920px, 100%);
            border: 1px solid var(--border);
            border-radius: 28px;
            overflow: hidden;
            background: var(--paper);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow);
        }
        .hero {
            padding: 28px 32px 18px;
            background:
                linear-gradient(135deg, rgba(178, 74, 27, 0.14), transparent 48%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.4), transparent);
            border-bottom: 1px solid var(--border);
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(32, 26, 21, 0.06);
            color: var(--muted);
            font-size: 13px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .brand::before {
            content: "";
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: var(--accent);
            box-shadow: 0 0 0 6px var(--accent-soft);
        }
        h1 {
            margin: 18px 0 8px;
            font-size: clamp(34px, 5vw, 56px);
            line-height: 0.95;
            letter-spacing: -0.03em;
        }
        .lead {
            margin: 0;
            max-width: 48rem;
            color: var(--muted);
            font-size: 18px;
        }
        .content {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(260px, 0.85fr);
            gap: 22px;
            padding: 28px 32px 34px;
        }
        .panel {
            border: 1px solid var(--border);
            border-radius: 22px;
            padding: 22px;
            background: rgba(255, 255, 255, 0.46);
        }
        .panel h2 {
            margin: 0 0 12px;
            font-size: 12px;
            color: var(--muted);
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .detail {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
            font-family: "SFMono-Regular", Menlo, Monaco, Consolas, monospace;
            font-size: 14px;
            line-height: 1.65;
            color: #2d241d;
        }
        .meta {
            display: grid;
            gap: 14px;
        }
        .meta-row strong {
            display: block;
            margin-bottom: 4px;
            font-size: 12px;
            color: var(--muted);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .meta-row span,
        .meta-row code {
            font-size: 15px;
            color: var(--ink);
        }
        code {
            font-family: "SFMono-Regular", Menlo, Monaco, Consolas, monospace;
            word-break: break-word;
        }
        .status {
            display: inline-block;
            padding: 5px 11px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        @media (max-width: 760px) {
            body {
                padding: 16px;
            }
            .hero,
            .content {
                padding: 20px;
            }
            .content {
                grid-template-columns: 1fr;
            }
            h1 {
                line-height: 1.02;
            }
        }
    </style>
</head>
<body>
    <main class="frame">
        <section class="hero">
            <div class="brand">Cimply.Work Framework</div>
            <h1>{$titleHtml}</h1>
            <p class="lead">{$leadHtml}</p>
        </section>
        <section class="content">
            <article class="panel">
                <h2>Diagnostic</h2>
                <p class="detail">{$detailHtml}</p>
            </article>
            <aside class="meta">
                <div class="panel">
                    <span class="status">HTTP {$statusHtml}</span>
                    <div class="meta-row">
                        <strong>Framework</strong>
                        <span>Cimply.Work {$versionHtml}</span>
                    </div>
                    <div class="meta-row">
                        <strong>Project</strong>
                        <span>{$projectHtml}</span>
                    </div>
                    <div class="meta-row">
                        <strong>Request</strong>
                        <code>{$requestUriHtml}</code>
                    </div>
                </div>
                <div class="panel">
                    <h2>Hint</h2>
                    <p class="lead" style="font-size:15px; margin:0;">{$hintHtml}</p>
                </div>
            </aside>
        </section>
    </main>
</body>
</html>
HTML;

            exit(1);
        }

        private static function renderCli(
            int $statusCode,
            string $title,
            string $lead,
            string $detail,
            string $project,
            string $requestUri
        ): never {
            $output = [
                sprintf('[Cimply.Work] %s (HTTP %d)', $title, $statusCode),
                $lead,
            ];

            if ($detail !== '') {
                $output[] = $detail;
            }

            if ($project !== '') {
                $output[] = 'Project: ' . $project;
            }

            if ($requestUri !== '') {
                $output[] = 'Request: ' . $requestUri;
            }

            fwrite(STDERR, implode(PHP_EOL, $output) . PHP_EOL);
            exit(1);
        }

        private static function resolveStatusCode(\Throwable $exception, ?int $statusCode): int
        {
            if (\is_int($statusCode) && $statusCode >= 400 && $statusCode <= 599) {
                return $statusCode;
            }

            $exceptionCode = $exception->getCode();
            if (\is_int($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599) {
                return $exceptionCode;
            }

            if ($exception instanceof LicenseException) {
                return 403;
            }

            return 500;
        }

        private static function resolveTitle(int $statusCode, \Throwable $exception): string
        {
            if ($exception instanceof LicenseException) {
                return 'License Validation Failed';
            }

            return match ($statusCode) {
                404 => 'Route Not Found',
                403 => 'Access Restricted',
                default => 'Framework Warning',
            };
        }

        private static function resolveLead(int $statusCode, \Throwable $exception): string
        {
            if ($exception instanceof LicenseException) {
                return 'A valid Cimply.Work license is required before this project can start.';
            }

            return match ($statusCode) {
                404 => 'The requested route could not be resolved by Cimply.Work.',
                default => 'Cimply.Work stopped the request because a recoverable framework error was detected.',
            };
        }

        private static function resolveHint(int $statusCode, \Throwable $exception): string
        {
            if ($exception instanceof LicenseException) {
                return 'Place a RouteMedia-issued license.key in the project root and make sure it matches this installation, version and host.';
            }

            return match ($statusCode) {
                404 => 'Check routing.yml, the request path and the configured controller action for this route.',
                default => 'Review the current project configuration and controller targets before retrying the request.',
            };
        }

        private static function wantsJson(string $requestUri): bool
        {
            $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
            $path = (string)(parse_url($requestUri, PHP_URL_PATH) ?? $requestUri);

            return str_contains($accept, 'application/json')
                || str_starts_with($path, '/api')
                || $path === '/health';
        }

        private static function escape(string $value): string
        {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
    }
}

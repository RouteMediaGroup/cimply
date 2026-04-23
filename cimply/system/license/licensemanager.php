<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\System\License {
    final class LicenseManager
    {
        private const PRODUCT = 'cimply.work';
        private const DEFAULT_LICENSE_FILE = 'license.key';
        private const DEFAULT_PUBLIC_KEY_FILE = __DIR__ . DIRECTORY_SEPARATOR . 'routemedia-public.pem';

        public function __construct(private readonly string $frameworkVersion)
        {
        }

        public function validate(string $projectName, string $projectPath): array
        {
            $projectPath = rtrim((string)$projectPath, DIRECTORY_SEPARATOR);
            if ($projectPath === '') {
                throw new LicenseException('License validation requires a valid project path.');
            }

            $consumerRoot = $this->resolveConsumerRoot($projectPath);
            $licenseFile = $this->resolveLicenseFile($consumerRoot);
            $token = $this->loadFileContents($licenseFile, 'license key');
            $parsed = $this->parseToken(trim($token));

            $publicKeyFile = $this->resolvePublicKeyFile();
            $publicKey = $this->loadFileContents($publicKeyFile, 'public key');

            $this->verifySignature(
                $parsed['headerEncoded'],
                $parsed['payloadEncoded'],
                $parsed['signature'],
                $publicKey
            );

            $claims = $this->validateClaims(
                $parsed['header'],
                $parsed['payload'],
                $projectName,
                $projectPath,
                $consumerRoot
            );

            $claims['_validatedAt'] = gmdate(DATE_ATOM);
            $claims['_frameworkVersion'] = $this->frameworkVersion;
            $claims['_licenseFile'] = $licenseFile;
            $claims['_publicKeyFile'] = $publicKeyFile;

            return $claims;
        }

        private function validateClaims(
            array $header,
            array $claims,
            string $projectName,
            string $projectPath,
            string $consumerRoot
        ): array {
            $algorithm = strtoupper(trim((string)($header['alg'] ?? '')));
            if ($algorithm !== 'RS256') {
                throw new LicenseException('Unsupported license algorithm.');
            }

            $licenseId = $this->requireStringClaim($claims, 'licenseId');
            $issuer = $this->requireStringClaim($claims, 'issuer');
            $customer = $this->requireStringClaim($claims, 'customer');
            $status = strtolower($this->requireStringClaim($claims, 'status'));

            if ($status !== 'active') {
                throw new LicenseException('License is not active.');
            }

            $expectedProduct = self::PRODUCT;
            $expectedConsumer = strtolower(trim((string)basename($consumerRoot)));
            $expectedProject = strtolower(trim((string)basename($projectPath)));
            $expectedVersion = $this->frameworkVersion;

            $product = $this->requireStringClaim($claims, 'product');
            $consumer = $this->requireStringClaim($claims, 'consumer');
            $project = $this->requireStringClaim($claims, 'project');

            if (strtolower($product) !== strtolower($expectedProduct)) {
                throw new LicenseException(sprintf('License product "%s" does not match "%s".', $product, $expectedProduct));
            }

            if ($expectedConsumer === '') {
                throw new LicenseException('Unable to derive the licensed consumer from the installation path.');
            }

            if (strtolower($consumer) !== $expectedConsumer) {
                throw new LicenseException(sprintf('License consumer "%s" does not match "%s".', $consumer, $expectedConsumer));
            }

            if ($expectedProject === '') {
                throw new LicenseException(sprintf('Unable to derive the licensed project from "%s".', $projectName));
            }

            if (strtolower($project) !== $expectedProject) {
                throw new LicenseException(sprintf('License project "%s" does not match "%s".', $project, $expectedProject));
            }

            $this->validateVersion($claims, $expectedVersion);
            $this->validateDates($claims);

            $currentHost = $this->detectCurrentHost();
            $hosts = $this->normalizeStringArray($claims['hosts'] ?? []);

            if ($currentHost !== null && $hosts !== [] && $this->matchesAnyHost($currentHost, $hosts) !== true) {
                throw new LicenseException(sprintf('License is not valid for host "%s".', $currentHost));
            }

            $claims['licenseId'] = $licenseId;
            $claims['issuer'] = $issuer;
            $claims['customer'] = $customer;
            $claims['status'] = $status;
            $claims['product'] = $product;
            $claims['consumer'] = $consumer;
            $claims['project'] = $project;
            $claims['hosts'] = $hosts;
            $claims['features'] = $this->normalizeStringArray($claims['features'] ?? []);
            $claims['_currentHost'] = $currentHost;

            return $claims;
        }

        private function validateVersion(array $claims, string $expectedVersion): void
        {
            if ($expectedVersion === '') {
                return;
            }

            $exactVersion = trim((string)($claims['version'] ?? ''));
            $versionMin = trim((string)($claims['versionMin'] ?? ''));
            $versionMax = trim((string)($claims['versionMax'] ?? ''));

            if ($exactVersion !== '') {
                if (version_compare($expectedVersion, $exactVersion, '!=')) {
                    throw new LicenseException(sprintf('License version "%s" does not match framework version "%s".', $exactVersion, $expectedVersion));
                }

                return;
            }

            if ($versionMin === '' && $versionMax === '') {
                throw new LicenseException('License does not define a framework version constraint.');
            }

            if ($versionMin !== '' && version_compare($expectedVersion, $versionMin, '<')) {
                throw new LicenseException(sprintf('Framework version "%s" is below the licensed minimum "%s".', $expectedVersion, $versionMin));
            }

            if ($versionMax !== '' && version_compare($expectedVersion, $versionMax, '>')) {
                throw new LicenseException(sprintf('Framework version "%s" exceeds the licensed maximum "%s".', $expectedVersion, $versionMax));
            }
        }

        private function validateDates(array $claims): void
        {
            $now = time();
            $issuedAt = $this->parseDateClaim($claims, 'issuedAt', true);
            $notBefore = $this->parseDateClaim($claims, 'notBefore');
            $expiresAt = $this->parseDateClaim($claims, 'expiresAt');

            if ($issuedAt !== null && $issuedAt > $now) {
                throw new LicenseException('License issue date lies in the future.');
            }

            if ($notBefore !== null && $now < $notBefore) {
                throw new LicenseException('License is not active yet.');
            }

            if ($expiresAt !== null && $now > $expiresAt) {
                throw new LicenseException('License has expired.');
            }
        }

        private function verifySignature(string $headerEncoded, string $payloadEncoded, string $signature, string $publicKey): void
        {
            if (\function_exists('openssl_pkey_get_public') !== true || \function_exists('openssl_verify') !== true) {
                throw new LicenseException('OpenSSL extension is required for license validation.');
            }

            $publicKeyResource = \openssl_pkey_get_public($publicKey);
            if ($publicKeyResource === false) {
                throw new LicenseException('Unable to load the configured license public key.');
            }

            $verification = \openssl_verify(
                $headerEncoded . '.' . $payloadEncoded,
                $signature,
                $publicKeyResource,
                OPENSSL_ALGO_SHA256
            );

            if ($verification !== 1) {
                throw new LicenseException('License signature is invalid.');
            }
        }

        private function parseToken(string $token): array
        {
            $segments = explode('.', $token);
            if (count($segments) !== 3) {
                throw new LicenseException('License key format is invalid.');
            }

            [$headerEncoded, $payloadEncoded, $signatureEncoded] = $segments;

            return [
                'headerEncoded' => $headerEncoded,
                'payloadEncoded' => $payloadEncoded,
                'header' => $this->decodeJsonSegment($headerEncoded, 'header'),
                'payload' => $this->decodeJsonSegment($payloadEncoded, 'payload'),
                'signature' => $this->decodeBase64Url($signatureEncoded, 'signature'),
            ];
        }

        private function decodeJsonSegment(string $segment, string $name): array
        {
            $decoded = $this->decodeBase64Url($segment, $name);
            $data = json_decode($decoded, true);

            if (\is_array($data) !== true) {
                throw new LicenseException(sprintf('License %s segment is not valid JSON.', $name));
            }

            return $data;
        }

        private function decodeBase64Url(string $value, string $name): string
        {
            $padding = strlen($value) % 4;
            if ($padding > 0) {
                $value .= str_repeat('=', 4 - $padding);
            }

            $decoded = base64_decode(strtr($value, '-_', '+/'), true);
            if ($decoded === false) {
                throw new LicenseException(sprintf('License %s segment is not valid base64url.', $name));
            }

            return $decoded;
        }

        private function resolveLicenseFile(string $consumerRoot): string
        {
            return rtrim($consumerRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . self::DEFAULT_LICENSE_FILE;
        }

        private function resolvePublicKeyFile(): string
        {
            return self::DEFAULT_PUBLIC_KEY_FILE;
        }

        private function loadFileContents(string $path, string $label): string
        {
            if (is_file($path) !== true) {
                if ($label === 'license key') {
                    throw new LicenseException(
                        sprintf('No valid Cimply.Work license file was found. Expected path: %s', $path),
                        403
                    );
                }

                throw new LicenseException(
                    sprintf('The Cimply.Work license verification key is missing. Expected path: %s', $path),
                    500
                );
            }

            $contents = file_get_contents($path);
            if (\is_string($contents) !== true || trim($contents) === '') {
                if ($label === 'license key') {
                    throw new LicenseException(
                        sprintf('The Cimply.Work license file is empty or unreadable: %s', $path),
                        403
                    );
                }

                throw new LicenseException(
                    sprintf('The Cimply.Work license verification key is empty or unreadable: %s', $path),
                    500
                );
            }

            return $contents;
        }

        private function resolveConsumerRoot(string $projectPath): string
        {
            $projectsDir = dirname($projectPath);
            $consumerRoot = dirname($projectsDir);

            return is_dir($consumerRoot) ? $consumerRoot : $projectsDir;
        }

        private function detectCurrentHost(): ?string
        {
            $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? null;
            if (\is_string($host) !== true || trim($host) === '') {
                return null;
            }

            $host = trim($host);
            if (str_starts_with($host, '[')) {
                $end = strpos($host, ']');
                if ($end !== false) {
                    return strtolower(substr($host, 1, $end - 1));
                }
            }

            if (substr_count($host, ':') === 1) {
                $parts = explode(':', $host, 2);
                $host = $parts[0];
            }

            return strtolower(trim($host));
        }

        private function matchesAnyHost(string $host, array $patterns): bool
        {
            foreach ($patterns as $pattern) {
                if ($this->matchesHost($host, $pattern)) {
                    return true;
                }
            }

            return false;
        }

        private function matchesHost(string $host, string $pattern): bool
        {
            $host = strtolower(trim($host));
            $pattern = strtolower(trim($pattern));

            if ($pattern === '*' || $pattern === $host) {
                return true;
            }

            if (str_starts_with($pattern, '*.')) {
                $suffix = substr($pattern, 1);
                return $host !== ltrim($suffix, '.') && str_ends_with($host, $suffix);
            }

            return false;
        }

        private function parseDateClaim(array $claims, string $key, bool $required = false): ?int
        {
            if (!array_key_exists($key, $claims)) {
                if ($required) {
                    throw new LicenseException(sprintf('License claim "%s" is missing.', $key));
                }

                return null;
            }

            $value = trim((string)$claims[$key]);
            if ($value === '') {
                if ($required) {
                    throw new LicenseException(sprintf('License claim "%s" is empty.', $key));
                }

                return null;
            }

            $timestamp = strtotime($value);
            if ($timestamp === false) {
                throw new LicenseException(sprintf('License claim "%s" is not a valid date.', $key));
            }

            return $timestamp;
        }

        private function requireStringClaim(array $claims, string $key): string
        {
            $value = trim((string)($claims[$key] ?? ''));
            if ($value === '') {
                throw new LicenseException(sprintf('License claim "%s" is missing.', $key));
            }

            return $value;
        }

        private function normalizeStringArray(mixed $value): array
        {
            $values = \is_array($value) ? $value : (($value === null || $value === '') ? [] : [$value]);
            $result = [];

            foreach ($values as $item) {
                $item = trim((string)$item);
                if ($item !== '') {
                    $result[] = $item;
                }
            }

            return array_values(array_unique($result));
        }
    }
}

<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.2
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply {
    class Work {
        public const VERSION = '4.0.2';

        public bool $error = false;

        private ?string $projectName = null;
        private ?string $projectPath = null;

        protected static $loader = null;
        protected static array $autoloadRoots = [];
        protected static bool $autoloadRegistered = false;

        public function __construct(array $assembly = [])
        {
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . 'compat.php';

            self::autoLoader(function (array $usings = []) use ($assembly): void {
                $roots = array_merge($usings, $assembly);
                $normalizedRoots = [];

                foreach ($roots as $root) {
                    $normalized = self::normalizeRootPath($root);
                    if ($normalized !== null) {
                        $normalizedRoots[] = $normalized;
                    }
                }

                $normalizedRoots = array_values(array_unique($normalizedRoots));

                if ($normalizedRoots !== []) {
                    $existingRoots = array_values(array_filter(
                        self::$autoloadRoots,
                        static fn(string $root): bool => !in_array($root, $normalizedRoots, true)
                    ));

                    self::$autoloadRoots = array_merge($normalizedRoots, $existingRoots);
                }

                if (self::$autoloadRoots !== []) {
                    set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, self::$autoloadRoots));
                }

                spl_autoload_extensions('.php');

                if (!self::$autoloadRegistered) {
                    spl_autoload_register([self::class, 'autoloadClass']);
                    self::$autoloadRegistered = true;
                }
            }, $assembly);
        }

        public function app(?string $projectName = null, ?string $projectPath = null): static
        {
            if ($projectName === null || $projectName === '') {
                throw new \InvalidArgumentException('Error: load non-project.');
            }

            $this->projectName = $projectName;
            $this->projectPath = $projectPath;

            return $this;
        }

        public function run(array $extends = []): ?App\Run
        {
            if ($this->projectName === null || $this->projectName === '') {
                return null;
            }

            try {
                return new App\Run($this->projectName, self::$loader, ['extends' => $extends], $this->projectPath);
            } catch (\Throwable $exception) {
                \Cimply\System\FailureRenderer::render($exception, null, [
                    'project' => (string)$this->projectName,
                ]);
            }
        }

        protected static function autoLoader(callable $loader, array $assembly = []): void
        {
            if (self::$loader === null) {
                self::$loader = $loader;
            }

            (self::$loader)($assembly);
            $loader(array_merge((array)(System\Settings::Assembly ?? []), $assembly));
        }

        public static function autoloadClass(string $className): void
        {
            $className = ltrim($className, '\\');

            foreach (self::$autoloadRoots as $root) {
                foreach (self::candidateRelativePaths($className) as $relativePath) {
                    $resolvedPath = self::resolveCaseInsensitivePath($root, $relativePath);
                    if ($resolvedPath !== null && is_file($resolvedPath)) {
                        require_once $resolvedPath;
                        return;
                    }
                }
            }
        }

        protected static function candidateRelativePaths(string $className): array
        {
            $paths = [
                str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php',
            ];

            if (str_starts_with($className, __NAMESPACE__ . '\\')) {
                $paths[] = str_replace('\\', DIRECTORY_SEPARATOR, substr($className, strlen(__NAMESPACE__) + 1)) . '.php';
            }

            if (str_contains($className, '\\')) {
                $segments = explode('\\', $className);
                $paths[] = implode(DIRECTORY_SEPARATOR, array_slice($segments, 1)) . '.php';
            }

            return array_values(array_unique($paths));
        }

        protected static function normalizeRootPath(mixed $root): ?string
        {
            if (!is_string($root) || trim($root) === '') {
                return null;
            }

            $candidate = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, trim($root));
            $candidates = [
                $candidate,
                __DIR__ . DIRECTORY_SEPARATOR . ltrim($candidate, DIRECTORY_SEPARATOR),
                dirname(__DIR__) . DIRECTORY_SEPARATOR . ltrim($candidate, DIRECTORY_SEPARATOR),
            ];

            foreach ($candidates as $path) {
                $resolved = realpath($path);
                if ($resolved !== false && is_dir($resolved)) {
                    return $resolved;
                }
            }

            return is_dir($candidate) ? $candidate : null;
        }

        protected static function resolveCaseInsensitivePath(string $root, string $relativePath): ?string
        {
            $segments = array_values(array_filter(explode(DIRECTORY_SEPARATOR, $relativePath), static fn($value) => $value !== ''));
            $current = rtrim($root, DIRECTORY_SEPARATOR);

            foreach ($segments as $segment) {
                $directPath = $current . DIRECTORY_SEPARATOR . $segment;

                if (is_dir($directPath) || is_file($directPath)) {
                    $current = $directPath;
                    continue;
                }

                $entries = @scandir($current);
                if ($entries === false) {
                    return null;
                }

                $matchedEntry = null;
                $segmentLower = strtolower($segment);

                foreach ($entries as $entry) {
                    if (strtolower($entry) === $segmentLower) {
                        $matchedEntry = $entry;
                        break;
                    }
                }

                if ($matchedEntry === null) {
                    return null;
                }

                $current .= DIRECTORY_SEPARATOR . $matchedEntry;
            }

            return $current;
        }

        public function __destruct()
        {
            self::$loader = null;
        }
    }
}

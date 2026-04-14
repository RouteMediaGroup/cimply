<?php
/*
 * Cimply.Work Business Framework
 * Version 4.0.1
 * Copyright (c) 2012-2026 RouteMedia®. All rights reserved.
 * Proprietary software. Use permitted only under valid commercial license.
 * Unauthorized copying, modification, distribution, or use is prohibited.
 * Contact: direkt@route-media.info
 */

namespace Cimply\App {
    use Cimply\Interfaces\ICast;
    use Cimply\Basics\Repository\Support;
    use Cimply\System\FailureRenderer;
    use Cimply\System\License\LicenseManager;
    use Cimply\Core\{
        Request\Request,
        Request\Uri\UriManager,
        Routing\Routing,
        Model\Mapper,
        Model\Wrapper,
        View\Translate
    };
    use Cimply\Basics\{
        Basics,
        ServiceLocator\ServiceLocator
    };
    use Cimply\Interfaces\Support\Enum\{
        RootSettings,
        AppSettings
    };

    class Run extends Basics implements ICast
    {
        use \Cast, \Secure, \Files;

        public bool $isDebug = false;
        public bool $error = false;

        /** @var callable|null */
        public $callable = null;

        public array $args = [];

        protected mixed $instance = null;
        protected $autoloader = null;

        protected ?string $projectName = null;
        protected ?string $projectPath = null;
        protected mixed $settings = null;
        protected bool $licenseValidated = false;

        public function __construct(...$args)
        {
            parent::__construct();

            if (empty($args)) {
                return;
            }

            if (\session_status() === PHP_SESSION_NONE) {
                $sid = $args[0] ?? ('sessionid_' . \microtime(true));
                if (\is_string($sid) && $sid !== '') {
                    @\session_id($sid);
                }
                @\session_start();
            }

            $extends = null;
            if (isset($args[2]) && \is_array($args[2]) && isset($args[2]['extends'])) {
                $extends = $args[2]['extends'];
            }

            $this->instance = ServiceLocator::Cast(static::Add((object)['extends' => $extends]));
            $this->projectName = (string)($args[0] ?? '');
            $this->autoloader = $args[1] ?? null;

            $baseProjectPath = (isset($args[3]) && \is_string($args[3]) && $args[3] !== '')
                ? rtrim($args[3], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
                : (defined('Settings::ProjectPath') ? Settings::ProjectPath : '');

            $this->projectPath = \str_replace('%project%', $this->projectName, $baseProjectPath);

            $lastArg = \end($args);
            $fallbackCfg = (is_string($lastArg) && $lastArg !== '')
                ? $lastArg
                : ('..' . DIRECTORY_SEPARATOR . 'config.yml');

            $conf = parent::GetConfig()->loader(
                [
                    $fallbackCfg,
                    $this->projectPath . 'config.yml',
                    '.' . DIRECTORY_SEPARATOR . 'config.yml'
                ],
                self::$conf
            ) ?? self::$conf;

            $conf = $this->normalizeConfiguration((array)$conf);

            $this->settings = $this->instance->addInstance(new Support(
                \array_map(
                    function ($values) {
                        $encoded = \JsonDeEncoder::Encode($values);
                        $encoded = \str_replace('%project%', (string)$this->projectName, (string)$encoded);
                        return \JsonDeEncoder::Decode($encoded, true);
                    },
                    (array)$conf
                )
            ));

            $this->registerLicense();
            $this->isDebug = (bool)$this->settings->getSettings([], RootSettings::DEVMODE);
        }

        /**
         * Summary of Cast
         * @param mixed $mainObject
         * @param mixed $selfObject
         */
        final public static function Cast($mainObject, $selfObject = self::class): self
        {
            return static::Cull($mainObject, $selfObject, true);
        }

        /**
         * Register services
         */
        final public function register(): ServiceLocator
        {
            $this->assertLicenseValidated();

            $rootUrl = $this->settings->getSettings([], AppSettings::BASEURL);
            $rootUrl = (!empty($rootUrl)) ? (string)$rootUrl : null;

            $uri = new UriManager(null, null, $rootUrl);

            $routingKey = $uri->getRoutingPath();

            $routingConfig = parent::GetConfig()->loader(
                [$this->projectPath . 'routing.yml', 'routing.yml'],
                $this->routing($routingKey)
            );

            // add instance of routing
            $this->instance->addInstance(new Routing($routingConfig));

            // add instance of request-data
            $this->instance->addInstance(new Request($this->validate));

            // add instance of globale translations
            $pattern = Support::Cast($this->instance->getService())->getRootSettings(RootSettings::PATTERN);
            $globals = parent::GetConfig()->loader([$this->projectPath . 'globals.yml', 'globals.yml'], []) ?? [];
            $this->instance->addInstance((new Translate($pattern))->set($globals, true));

            // add instance of mapping
            $mapper = $this->instance->addInstance((new Mapper())->set(
                parent::GetConfig()->loader([$this->projectPath . 'mapper.yml', 'mapper.yml'], []) ?? [],
                true
            ));

            $models = [];
            foreach (Mapper::Cast($mapper)->getMappers() ?? [] as $value) {
                $models = parent::GetConfig()->loader([$this->projectPath . $value, $value], $models);
            }

            // add instance of model-wrappers
            $this->instance->addInstance((new Wrapper())->set($models));

            return $this->instance;
        }

        final public function execute(): Run
        {
            $this->assertLicenseValidated();

            if (\is_callable($this->autoloader)) {
                ($this->autoloader)($this->settings->getAssembly());
            }

            $app = (string)$this->settings->getSettings([], AppSettings::PROJECTNAMESPACE);

            if ($app === '' || \class_exists($app) !== true) {
                $this->error = true;
                FailureRenderer::render(
                    new \RuntimeException("App can not be run. Because \"{$app}\" not be found.", 500),
                    500,
                    ['project' => (string)$this->projectName]
                );
            }

            try {
                return self::Cast(new $app($this->register()));
            } catch (\Throwable $exception) {
                FailureRenderer::render($exception, null, [
                    'project' => (string)$this->projectName,
                ]);
            }
        }

        private function normalizeConfiguration(array $conf): array
        {
            $conf['App'] = \is_array($conf['App'] ?? null) ? $conf['App'] : [];
            $conf['Assembly'] = \is_array($conf['Assembly'] ?? null) ? $conf['Assembly'] : [];

            $projectName = (string)($this->projectName ?? '');
            $projectNamespace = $this->normalizeProjectNamespace($projectName);
            $projectPath = rtrim((string)($this->projectPath ?? ''), DIRECTORY_SEPARATOR);
            $appRoot = __DIR__;

            if ($projectPath !== '') {
                $conf['App']['projectPath'] = $projectPath;
                $conf['App']['cacheDir'] = $projectPath . DIRECTORY_SEPARATOR . 'cache';
                $conf['Assembly']['projectCtrl'] = $projectPath . DIRECTORY_SEPARATOR . 'controller';
            }

            if ($projectName !== '') {
                $conf['App']['project'] = $projectName;
            }

            if ($projectNamespace !== '') {
                $conf['App']['namespace'] = "Cimply\\App\\Projects\\{$projectNamespace}\\App";
            }

            $conf['Assembly']['baseCtrl'] = $appRoot . DIRECTORY_SEPARATOR . 'base';

            return $conf;
        }

        private function registerLicense(): void
        {
            $license = (new LicenseManager(\Cimply\Work::VERSION))->validate(
                (string)$this->projectName,
                (string)$this->projectPath
            );

            \Cimply\System\Helpers::Setter('License', $license, null, true);
            $this->instance->addInstance(new \ArrayObject($license, \ArrayObject::ARRAY_AS_PROPS), 'License');
            $this->licenseValidated = true;
        }

        private function assertLicenseValidated(): void
        {
            if ($this->licenseValidated !== true) {
                throw new \RuntimeException('Application bootstrap aborted because no valid license is loaded.');
            }
        }

        private function normalizeProjectNamespace(string $projectName): string
        {
            $spaced = preg_replace('/(?<!^)([A-Z])/', ' $1', $projectName);
            $spaced = str_replace(['-', '_'], ' ', (string)$spaced);
            $segments = preg_split('/\s+/', trim($spaced)) ?: [];

            $segments = array_filter($segments, static fn($segment) => $segment !== '');

            return implode('', array_map(
                static fn($segment) => ucfirst(strtolower((string)$segment)),
                $segments
            ));
        }
    }
}

<?php
namespace Cimply\App {
    /**
     * Description of CIMPLY.WORK
     *
     * @author Michael Eckebrecht
     */
    use \Cimply\Basics\Repository\Support;
    use \Cimply\Core\{Request\Request, Request\Uri\UriManager, Routing\Routing, Model\Mapper, Model\Wrapper, View\Translate};
    use \Cimply\Basics\{Basics, ServiceLocator\ServiceLocator};
    use \Cimply\Interfaces\Support\Enum\{RootSettings, AppSettings};

    class Run extends Basics {
        use \Cast;
        public $isDebug = false, $error = false, $callable, $args = [];
        protected $instance = null, $autoloader = null, $projectName = null, $projectPath = null, $settings = null;
        function __construct(...$args) {
            parent::__construct();
            if(!(empty($args))) {
                (session_id() === null) ? session_id($args[0]) : ( (session_status() != 1) ? session_start() : true );
                $this->instance = ServiceLocator::Cast(\Secure::Add(((object)$args[2])->extends));
                $this->projectName = $args[0];
                $this->autoloader = $args[1];
                $this->projectPath = (str_replace('%project%', $args[0], Settings::ProjectPath));
                //add instance of project settings
                $conf = parent::GetConfig()->loader([$this->projectPath.'config.yml', 'config.yml'], self::$conf) ?? self::$conf;
                $this->settings = $this->instance->addInstance(new Support(
                    array_map(
                        function($values) {
                            return \JsonDeEncoder::Decode(str_replace('%project%', ($this->projectName), \JsonDeEncoder::Encode($values)), true);
                        }, $conf
                    )
                ));
                $this->isDebug = $this->settings->getSettings([], RootSettings::DEVMODE);
            }
        }

        /**
         * Summary of Cast
         * @param mixed $mainObject
         * @param mixed $selfObject
         * @return mixed
         */
        final static function Cast($mainObject, $selfObject = self::class): self {
            return static::Cull($mainObject, $selfObject, true);
        }

        final function register(): ServiceLocator {
            //add instance of routing
            $rootUrl = !(empty($this->settings->getSettings([], AppSettings::BASEURL))) ? $this->settings->getSettings([], AppSettings::BASEURL) : null;
            $this->instance->addInstance(new Routing(parent::GetConfig()->loader([$this->projectPath.'routing.yml', 'routing.yml'], $this->routing((new UriManager)->getRoutingPath($rootUrl)))));
            //add instance of request-data
            $this->instance->addInstance(new Request($this->validate));
            //add instance of globale translations
            $this->instance->addInstance((new Translate(Support::Cast($this->instance->getService())->getRootSettings(RootSettings::PATTERN)))->set(parent::GetConfig()->loader([$this->projectPath.'globals.yml', 'globals.yml'], []) ?? [], true));
            //add instance of mapping
            $mapper = $this->instance->addInstance((new Mapper())->set(parent::GetConfig()->loader([$this->projectPath.'mapper.yml', 'mapper.yml'], []) ?? [], true));
            $models = [];
            foreach( Mapper::Cast($mapper)->getMappers() ?? [] as $value) {
                $models = parent::GetConfig()->loader([$this->projectPath.$value, $value], $models);
            }
            //add instance of model-wrappers
            $this->instance->addInstance((new Wrapper())->set($models));
            return $this->instance;
        }

        final function execute(): Run {
            ($this->autoloader)($this->settings->getAssembly());
            try {
                $app = \ucfirst($this->settings->getSettings([], AppSettings::PROJECTNAMESPACE));
                if(class_exists($app) !== true) {
                    $this->error = true;
                    throw new \Exception("App can not be run. Because \"{$app}\" not be found.");
                }
			} catch(\Exception $e) {
				\Debug::VarDump($e);
			}
            return self::Cast(new $app($this->register()));
        }
    }
}

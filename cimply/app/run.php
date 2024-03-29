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
                (session_id() === null) ? session_id($args[0] ?? 'sessionid_'.microtime()) : ( (session_status() != 1) ? session_start() : true );
                $this->instance = ServiceLocator::Cast(\Secure::Add(
                    (isset($args[2]['extends'])) ? (((object)($args[2]))->extends) : (object)['extends'=>null]
                ));
                $this->projectName = $args[0];
                $this->autoloader = $args[1];
                $this->projectPath = (str_replace('%project%', $args[0], (isset($args[3]) ? $args[3].DIRECTORY_SEPARATOR : Settings::ProjectPath)));
                //die(var_dump($this->projectPath));
                //add instance of project settings
                //die(var_dump(is_file('.'.DIRECTORY_SEPARATOR.'config.yml')));
                $conf = parent::GetConfig()->loader(
                    [
                        is_array(end($args) ? '..'.DIRECTORY_SEPARATOR.'config.yml' : end($args)),
                        $this->projectPath.'config.yml',
                        '.'.DIRECTORY_SEPARATOR.'config.yml'
                    ],
                    self::$conf) ?? self::$conf;
                $zdzu = [is_array(end($args) ? '.'.DIRECTORY_SEPARATOR.'config.yml' : end($args)),
                $this->projectPath.'config.yml',
                '.'.DIRECTORY_SEPARATOR.'config.yml'];
                
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
            $this->instance->addInstance(new Routing(parent::GetConfig()->loader([$this->projectPath.'routing.yml', 'routing.yml'], $this->routing((new UriManager(null,null,$rootUrl))->getRoutingPath()))));
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
                $app = ($this->settings->getSettings([], AppSettings::PROJECTNAMESPACE));
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
<?php
/**
 * Description of DefaultApp
 *
 * @author Michael Eckebrecht
 */

namespace Cimply\App\Projects\DefaultApp {
    use \Cimply\Core\{Routing\Routing, View\Scope, View\View, Gui\Gui, Database\Database};
    use \Cimply\Core\Validator\Validator;
    use \Cimply\Basics\{ServiceLocator\ServiceLocator, Repository\Support};
    use \Cimply\Interfaces\Support\Enum\RootSettings;
    class App {
        use \Annotation;
        function __construct(ServiceLocator $instance) {
            //Instanziere Route
            $route = Routing::Cast($instance->getService());
            $route->setValue('validates', new Validator($route->getParams()));
            //Instanziere Scope
            $scope = Scope::Cast($instance->addInstance((new Scope())->set($route->getScope())));

            //Instanziere View
            $view = $instance->addInstance((new View($scope))->set(Support::Cast($instance->getService())->getSettings([]), true));

            //Instanziere Gui
            $instance->addInstance((new Gui($view)));

            //Action
            View::Cast($instance->getService())->set(Scope::Cast($instance->getService()));
            $instance->addInstance(new Database(Support::Cast($instance->getService())->getRootSettings(RootSettings::DBCONNECT)));

            //Default Annotations
            View::Assign((self::GetAnnotations($scope->getAction()))->getParameters());

            ($scope->getAction())($instance);
        }
    }
}
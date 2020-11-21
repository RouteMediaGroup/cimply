<?php
namespace Cimply\App {
    interface Settings extends \Cimply\System\Settings {
        const AppPath = __DIR__.DIRECTORY_SEPARATOR;
        const Projects = self::AppPath.'projects'.DIRECTORY_SEPARATOR;
        const ProjectPath = self::Projects.'%project%'.DIRECTORY_SEPARATOR;
    }
}

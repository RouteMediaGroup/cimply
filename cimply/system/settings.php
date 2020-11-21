<?php
namespace Cimply\System {
    interface Settings {
        const SystemPath = __DIR__.DIRECTORY_SEPARATOR;
        const TempDir = self::SystemPath.'tmp';
        const Assembly = [
            'Framework' => 'vendor\\routemediagroup\\cimply',
            'System' => self::SystemPath.'helper',
            'Yaml' => self::SystemPath.'vendor'.DIRECTORY_SEPARATOR.'yaml',
            'Linq' => self::SystemPath.'vendor'.DIRECTORY_SEPARATOR.'linq'
        ];
    }
}
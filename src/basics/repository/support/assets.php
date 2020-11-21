<?php

namespace Cimply\App\Repository\Project
{
	/**
     * Directories short summary.
	 *
     * Directories description.
	 *
	 * @version 1.0
	 * @author MikeCorner
	 */
	class Assets
	{
        use \Properties, \Cast;

        /**
         * Summary of $session
         * @var mixed
         */
        private $stylesheets, $javascript, $html, $cache, $markup, $controller, $common;
        public $root;
        /**
         * Summary of Cast
         * @param mixed $mainObject
         * @param mixed $selfObject
         * @return mixed
         */
        final static function Cast($mainObject, $selfObject = self::class): self {
            return self::Cull($mainObject, $selfObject);
        }

        function getStylesheetPath(): ?string {
            return $this->AppendAssetsDirectory($this->stylesheets);
        }

        function getJavascriptPath(): ?string {
            return $this->AppendAssetsDirectory($this->javascript);
        }

        function getHtmlPath(): ?string {
            return $this->AppendAssetsDirectory($this->html);
        }

        function getCachePath(): ?string {
            return $this->AppendAssetsDirectory($this->cache);
        }

        function getMarkupPath(): ?string {
            return $this->AppendAssetsDirectory($this->markup);
        }

        function getControllerPath(): ?string {
            return $this->AppendAssetsDirectory($this->controller);
        }

        function getCommonPath(): ?string {
            return $this->AppendAssetsDirectory($this->common);
        }
        private function AppendAssetsDirectory($path = null): string {
            return $this->root.$path;
        }
    } 
}
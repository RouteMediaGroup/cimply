<?php
namespace Cimply {
     class App {
        public $error = false;
        private $app = null;
        function __construct($project = null) {
		$this->app = (new Work(['src\\']))->app($project)->run();
        }
        public function run():App\Run {
            return $this->app;
        }
    }
}

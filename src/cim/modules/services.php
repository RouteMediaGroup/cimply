<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of
 *
 * @author MikeCorner ModelCtrl
 */

declare(strict_types=1);
namespace Cim\Modules {
    use \Cimply\App\Settings;
    use \Cimply\Core\View\View;
    class Services extends \Cimply\Service\Cli\Base
    {
        public $tableHeaderPrinted = false;
        public $iterations = [1, 100, 1000, 10000];

        /**
         *
         * @Author Michael Eckebrecht
         * @Info Execute Svervices:
         * @Menu 1: ElasticSearch | 2: JavaBridge
         * @Back To leave the program, press Return without any input
         * @Execute "enter your option:"
         *
         */
        final static function Init(): void {
            print "\n\r";
            print View::ParseTplVars("[+Description+]");
            print "\n\r";
            print "-------------------------------------------------------------------------";
            print "\n\r";
            print View::ParseTplVars("[+Info+]");
            print "\n\r";
            print View::ParseTplVars("[+Menu+]");
            print "\n\r\n\r";
            print View::ParseTplVars("[+Back+]");
            print "\n\r";
            print "-------------------------------------------------------------------------";
            print "\n\r";
            print(View::ParseTplVars("[+Execute+]"). " ");
            self::Calculate(parent::GetMessage());
            print "\n\r";
        }

        private static function Calculate($selected = null) {
            (bool)(strstr($selected, '(off)')) === true  ? die() : self::Execute($selected);
        }

        private static function Execute($select) {
            //ini_set('display_errors', 'true');
            $bm = new self();
            $start_total_time = $bm->getTimeMs();
            echo PHP_EOL;
            $start_connection_time = $bm->getTimeMs();

            // BENCHING CONNECTION
            switch($select) {
                case 1:
                case 'elasticsearch':
                    passthru(__DIR__ . DIRECTORY_SEPARATOR ."java".DIRECTORY_SEPARATOR."elasticsearch".DIRECTORY_SEPARATOR."bin".DIRECTORY_SEPARATOR."elasticsearch.bat");
                    break;

                case 2:
                case 'javabridge':
                    try {
                        if(exec("java -jar ".__DIR__ . DIRECTORY_SEPARATOR ."java".DIRECTORY_SEPARATOR."JavaBridge.war", $output)) {
                            if(!empty($context = end($output))) {
                                print $context;
                            } else {
                                print "connection failt.";
                            }
                        }
                    }
                    catch (\Exception $e) {
                        die(sprintf(
                            'Error connecting: %s (%s)',
                            $e->getMessage(), get_class($e)
                        ));
                    }
                    break;
            }
            $end_connection_time = $bm->getTimeMs();
            $connection_time = $bm->getFormattedTimeMs($start_connection_time, $end_connection_time);
            $end_total_time = $bm->getTimeMs();
            $total_time = $bm->getFormattedTimeMs($start_total_time, $end_total_time);
            echo PHP_EOL;
            echo '- Connection time: '.$connection_time.PHP_EOL;
            echo '- Total time     : '.$total_time.PHP_EOL;
            echo PHP_EOL;
        }

        /**
         * Return formatted time .
         *
         * @param int $start_time
         * @param int $end_time
         */
        public function getFormattedTimeMs($start_time, $end_time)
        {
            $time = $end_time - $start_time;
            return number_format($time, 0, '.', '').' ms';
        }
        /**
         * Get ms time (only 64bits platform).
         *
         * @return int
         */
        public function getTimeMs()
        {
            $mt = explode(' ', microtime());
            return ((int) $mt[1]) * 1000 + ((int) round($mt[0] * 1000));
        }

    }
}
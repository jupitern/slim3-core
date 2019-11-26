<?php

namespace Jupitern\Slim3\App\Console;

class Command
{

	public $output = false;
	public $log = "";


    public function __construct()
    {
        if (app()->getConfig('consoleOutput')) {
            $this->output = true;
            ob_end_flush();
        }
    }


    public function output($str, $lineBreak = true)
    {
        if ($lineBreak) {
            $str .= PHP_EOL;
        }

        $this->log .= $str;
        if ($this->output) {
            print $str;
        }
    }

}
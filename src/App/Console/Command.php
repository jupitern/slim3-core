<?php

namespace Jupitern\Slim3\App\Console;

class Command
{

    public function __construct()
    {
        if (app()->getConfig('consoleOutput')) {
            ob_implicit_flush();
            if (ob_get_length()) {
                ob_end_flush();
            }
        }
    }

}
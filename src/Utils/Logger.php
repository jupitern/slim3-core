<?php
namespace Jupitern\Slim3\Utils;

final class Logger
{

    public $startTime;
    private $entries = [];

    /**
     * Private constructor so nobody else can instantiate it
     */
    private function __construct()
    {
        $this->startTime = microtime(true);
    }

    /**
     * @return Logger
     */
    public static function instance()
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new Logger();
        }
        return $inst;
    }


    /**
     * @param string $message
     * @return Logger
     */
    public function add(string $message): Logger
    {
        $this->entries[] = (object)[
            'time' => round(microtime(true) - $this->startTime, 2),
            'message' => $message
        ];

        return $this;
    }


    /**
     * @return array
     */
    public function get(): array
    {
        return $this->entries;
    }


    /**
     * @param bool $displayTime
     * @return string
     */
    public function getAsString(bool $displayTime = true): string
    {
        $str = "";
        foreach ($this->entries as $entry) {
            if ($displayTime) {
                $str .= "{$entry->time}s => {$entry->message}".PHP_EOL;
            } else {
                $str .= $entry->message .PHP_EOL;
            }
        }

        return $str;
    }

}
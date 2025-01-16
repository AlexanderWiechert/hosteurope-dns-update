<?php

namespace Lib;

class ArgsParser
{

    /** @var array Global arguments */
    protected $argv = [];

    /** @var array Polished arguments */
    protected $args = [];

    /** @var array The allowed options/parameters */
    protected $allowedOptions = [];

    /**
     * ArgsParser constructor.
     * @param $argv
     * @param array $allowedOptions
     */
    public function __construct($argv, $allowedOptions = [])
    {
        $this->allowedOptions = $allowedOptions;

        try {

            // Remove first arguments key because this is the script name itself
            array_shift($argv);

            for ($i = 0; $i < count($argv); $i++)
            {

                // Ignore all parameters starting without a minus char
                if (strpos($argv[$i], '-') !== 0) {
                    continue;
                }

                if (in_array($argv[$i], array_keys($this->allowedOptions))) {

                    // If we receive a flag option define its boolean value
                    if (strpos($argv[$i], '--') !== false)
                        $this->args[str_replace('-', '', $argv[$i])] = true;
                    else
                        $this->args[str_replace('-', '', $argv[$i])] = $argv[$i+1];

                    continue;

                } else {
                    throw new \Exception('Option ' . $argv[$i] . ' is not defined within allowed options', 1);
                }

            }

        } catch (\Exception $e) {

            $this->write($e->getMessage(), 'error');
            exit(1);

        }
    }

    public function get($key)
    {
        if (isset($this->args[$key])) {
            return $this->args[$key];
        }
        return null;
    }

    public function set($key, $value)
    {
        $this->args[$key] = $value;
    }

    /**
     * @return string
     */
    public function writeBlankLine()
    {
        print "\n";
    }

    /**
     * @param $message
     * @param string $type
     */
    public function write($message, $type = 'notice')
    {
        switch ($type) {

            case 'error':
                $pattern = "\n\033[0;31m%s\n\n"; // red color
                break;

            case 'warning':
                $pattern = "\n\033[1;33m%s\n\n"; // yellow color
                break;

            case 'success':
                $pattern = "\n\033[1;32m%s\n"; // green color
                break;

            case 'headline':
                $pattern = "\033[1;34m%s\n"; // green color
                break;

            default:
                $pattern = "\033[0m%s\n"; // default color
                break;

        }

        print sprintf($pattern, $message); // Print message
        print "\033[0m"; // Reset colors
    }

    public function showHelp()
    {
        $this->writeBlankLine();
        $this->write('HostEurope DNS Updater', 'headline');
        $this->writeBlankLine();

        $this->write('This script provides an opportunity to automatically '
            . 'update your desired dns entries in HostEurope\'s domain administration tool.'
            . "\n\033[1;33mNotice: If you provide a host name that doesn't exists it will be created.");

        $this->writeBlankLine();

        foreach ($this->allowedOptions AS $key => $option) {

            $key = str_pad($key, 12, ' ');

            $this->write($key . '  ' . $option);
        }

        $this->writeBlankLine();

        exit(0);
    }

}
<?php


namespace vnukga\migrationGenerator\helpers;


class BaseHelper
{
    /**
     * Sends command to CLI.
     * @param string $command
     */
    protected function applyConsoleCommand(string $command)
    {
        $consoleHandle = popen($command,'w');
        fwrite($consoleHandle,'Y');
        pclose($consoleHandle);
        sleep(1);
    }
}
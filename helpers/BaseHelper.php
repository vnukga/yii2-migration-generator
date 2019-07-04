<?php


namespace vnukga\migrationGenerator\helpers;


class BaseHelper
{
    protected function applyConsoleCommand(string $command)
    {
        $consoleHandle = popen($command,'w');
        fwrite($consoleHandle,'Y');
        pclose($consoleHandle);
        sleep(1);
    }
}
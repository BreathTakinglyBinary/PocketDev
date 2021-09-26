<?php
declare(strict_types=1);

echo("    - Updating PocketMine-MP -\n");

@mkdir(__DIR__ . "/server");
if(file_exists(getcwd() . "/server/PocketMine-MP.phar")){
    unlink(getcwd() . "/server/PocketMine-MP.phar");
}
if(!copy("https://github.com/pmmp/PocketMine-MP/releases/latest/download/PocketMine-MP.phar", getcwd() . "/server/PocketMine-MP.phar")){
    throw new \RuntimeException("Failed to download PocketMine-MP.phar");
}
echo("    - PocketMine-MP Update Completed -\n");
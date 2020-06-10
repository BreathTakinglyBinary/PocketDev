<?php
declare(strict_types=1);

echo("    - Updating DeVirion -\n");

@mkdir(__DIR__ . "/server/plugins");
@mkdir(__DIR__ . "/server/virions");
$devirionData = json_decode(file_get_contents("https://poggit.pmmp.io/releases.json?name=DeVirion&latest-only"), true);
$url = (string) $devirionData[0]["artifact_url"];
if(!copy($url, getcwd() . "/server/plugins/DeVirion.phar")){
    throw new \RuntimeException("Failed to download");
}
echo("    - DeVirion Update Complete -\n");
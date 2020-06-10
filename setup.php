<?php
declare(strict_types=1);

echo PHP_BINARY;

echo("--- Setting up Development Environment ---\n");
@mkdir(__DIR__ . "/plugins");
include "UpdatePMMP.php";
include "UpdateDeVirion.php";
$execString = PHP_BINARY . " " . getcwd() . DIRECTORY_SEPARATOR . "GeneratePluginSkeleton.php DemoPlugin TheDeveloper MyOrganization";
exec($execString);

echo("--- Setup Completed ---");

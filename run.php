<?php

declare(strict_types=1);

namespace this_build_script;

use function array_map;
use function count;
use function dirname;
use function file_exists;
use function glob;
use function implode;
use function microtime;
use function mkdir;
use function passthru;
use function preg_quote;
use function realpath;
use function round;
use function rtrim;
use function sprintf;
use function strpos;
use function trim;
use function unlink;
use const DIRECTORY_SEPARATOR as DS;
use const PHP_BINARY;

const DIR_PLUGINS_SOURCE = __DIR__ . DS ."plugins" . DS;
const DIR_PLUGINS_SERVER = __DIR__ . DS . "server" . DS . "plugins" . DS;
const PLUGIN_STUB = '
<?php
echo "%s PocketMine-MP plugin v%s, developed by %s. Built on %s.
----------------
";
if(extension_loaded("phar")){
	$phar = new \Phar(__FILE__);
	foreach($phar->getMetadata() as $key => $value){
		echo ucfirst($key) . ": " . (is_array($value) ? implode(", ", $value) : $value) . "\n";
	}
}
__HALT_COMPILER();
';


/**
 * @param string[]    $strings
 * @param string|null $delim
 *
 * @return string[]
 */
function preg_quote_array(array $strings, string $delim = null) : array{
    return array_map(function(string $str) use ($delim) : string{ return preg_quote($str, $delim); }, $strings);
}

function isPluginDir(string $path) : bool{
    return (is_dir($path) and file_exists($path . DS . "plugin.yml"));
}

/**
 * @param string   $pharPath
 * @param string   $basePath
 * @param string[] $includedPaths
 * @param string   $stub
 * @param int      $signatureAlgo
 * @param int|null $compression
 */
function buildPhar(string $pharPath, string $basePath, array $includedPaths, string $stub = PLUGIN_STUB, int $signatureAlgo = \Phar::SHA1, ?int $compression = null){
    $basePath = realpath($basePath);
    if(file_exists($pharPath)){
        echo "Phar file already exists, overwriting...\n";
        try{
            \Phar::unlinkArchive($pharPath);
        }catch(\PharException $e){
            //unlinkArchive() doesn't like dodgy phars
            unlink($pharPath);
        }
    }
    @mkdir(dirname($pharPath));
    echo "Adding files...\n";

    $start = microtime(true);
    $phar = new \Phar($pharPath);
    $metadata = generatePluginMetadataFromYml($basePath . DS . "plugin.yml") ?? [];
    $phar->setMetadata($metadata);
    $phar->setStub($stub);
    $phar->setSignatureAlgorithm($signatureAlgo);
    $phar->startBuffering();

    //If paths contain any of these, they will be excluded
    $excludedSubstrings = preg_quote_array([
        realpath($pharPath), //don't add the phar to itself
    ], '/');

    $folderPatterns = preg_quote_array([
        DS . 'tests' . DS,
        DS . '.' //"Hidden" files, git dirs etc
    ], '/');


    $basePattern = preg_quote(rtrim($basePath, DS), '/');
    foreach($folderPatterns as $p){
        $excludedSubstrings[] = $basePattern . '.*' . $p;
    }

    $regex = sprintf('/^(?!.*(%s))^%s(%s).*/i',
        implode('|', $excludedSubstrings), //String may not contain any of these substrings
        preg_quote($basePath, '/'), //String must start with this path...
        implode('|', preg_quote_array($includedPaths, '/')) //... and must be followed by one of these relative paths, if any were specified. If none, this will produce a null capturing group which will allow anything.
    );

    $directory = new \RecursiveDirectoryIterator($basePath, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::CURRENT_AS_PATHNAME); //can't use fileinfo because of symlinks
    $iterator = new \RecursiveIteratorIterator($directory);
    $regexIterator = new \RegexIterator($iterator, $regex);

    $count = count($phar->buildFromIterator($regexIterator, $basePath));
    echo "Added $count files\n";

    if($compression !== null){
        echo "Checking for compressible files...\n";
        foreach($phar as $file => $finfo){
            /** @var \PharFileInfo $finfo */
            if($finfo->getSize() > (1024 * 512)){
                echo "Compressing " . $finfo->getFilename() . "\n";
                $finfo->compress($compression);
            }
        }
    }
    $phar->stopBuffering();

    echo "Built $pharPath in " . round(microtime(true) - $start, 3) . "s\n";
}

function generatePluginMetadataFromYml(string $pluginYmlPath) : ?array{
    if(!file_exists($pluginYmlPath)){
        return null;
    }

    $pluginYml = yaml_parse_file($pluginYmlPath);
    return [
        "name" => $pluginYml["name"],
        "version" => $pluginYml["version"],
        "main" => $pluginYml["main"],
        "api" => $pluginYml["api"],
        "depend" => $pluginYml["depend"] ?? "",
        "description" => $pluginYml["description"] ?? "",
        "author" => $pluginYml["author"] ?? "",
        "authors" => $pluginYml["authors"] ?? "",
        "website" => $pluginYml["website"] ?? "",
        "creationDate" => time()
    ];
}

foreach(glob(DIR_PLUGINS_SERVER . '*.phar') as $file){
    if(strpos($file, "DevTools") or strpos($file, "DeVirion")){
        continue;
    }
    echo "Removing $file\n";
    unlink($file);
}

$plugins = [];

if($argc === 2 and strtolower($argv[1]) === "--all"){
    echo "Trying to get all plugins...\n";
    foreach(glob(DIR_PLUGINS_SOURCE . "*") as $pluginDir){
        echo "Testing $pluginDir...";
        if(isPluginDir($pluginDir)){
            $pathParts = explode(DS, $pluginDir);
            $plugins[] = array_pop($pathParts);
        }
    }
}elseif($argc > 1){
    foreach($argv as $index => $value){
        if($index > 0){
            $plugins[] = $value;
        }
    }
}else{
    $valid = false;
    do{
        echo "Enter the name of the plugin you would like to build: ";
        $pluginName = trim(fgets(STDIN));
        if($pluginName === "" or isPluginDir(DIR_PLUGINS_SOURCE . $pluginName)){
            $valid = true;
            $plugins[] = $pluginName;
        }else{
            echo "Invalid plugin directory \"$pluginName\".  Please try again.\n\n";
        }
    }while(!$valid);
}

if(empty($plugins)){
    echo "Starting server with no plugins defined...\n\n";
}

@mkdir(DIR_PLUGINS_SOURCE);
//@mkdir(__DIR__ . DS ."server" . DS . "plugin_data" . DS . "VirionTools" . DS . "plugins");
foreach($plugins as $plugin){
    $pharPath = (__DIR__ . DS . "server" . DS . "plugins" . DS . "$plugin.phar");
    echo "Building $plugin\n";
    buildPhar($pharPath, "plugins" . DS . "$plugin", []);
    //copy($pharPath, __DIR__ . DS ."server" . DS . "plugin_data" . DS . "VirionTools" . DS . "plugins" .DS . "$plugin.phar");
}

const pmPaths = [
    __DIR__ . DS . "server" . DS . "PocketMine-MP.phar",
    __DIR__ . DS ."server" . DS . "src" . DS . "pocketmine" . DS . "PocketMine.php"
];

foreach(pmPaths as $p){
    if(file_exists($p)){
        passthru("\"" . PHP_BINARY . "\" \"$p\" --data=\"" . __DIR__ . DS . "server\" --plugins=\"" . __DIR__ . DS ."server" . DS . "plugins\" --no-wizard --debug.level=2");
        die;
    }
}

die("PocketMine-MP entry point not found (tried " . implode(", ", pmPaths));
<?php
declare(strict_types=1);

/**
 * This script will generate the basic files needed to get a plugin to load.  When called with no arguments,
 * it will prompt the user for the name of the plugin and the author's name.  This script will also accept
 * 3 arguments to allow automatic generation of plugin files.
 */

const DS = DIRECTORY_SEPARATOR;
const PLUGIN_DIR = __DIR__ . DS . "plugins" . DS;

$pluginName = $authorName = $organizationName = "";

function promptForInput(string $msg) : string{
    echo $msg . ": ";
    return trim(fgets(STDIN));
}

function validFileName(string $fileName) : bool{
    return !is_numeric($fileName[0]) && !($fileName === "");
}

function pluginExists(string $pluginName) : bool{
    if(!is_dir(PLUGIN_DIR . $pluginName)) return false;
    if(!is_dir(PLUGIN_DIR . $pluginName . DS . "src")) return false;
    if(!is_file(PLUGIN_DIR . $pluginName . DS . "plugin.yml")) return false;
    return true;
}

function promptForPluginName() : void{
    global $pluginName;
    $invalidName = true;
    $pluginExists = true;
    while($pluginExists or $invalidName){
        $pluginName = promptForInput("Enter a name for the new plugin");
        $invalidName = !validFileName($pluginName);
        if($invalidName){
            echo "Plugin names cannot start with a number and cannot be empty.";
        }
        $pluginExists = pluginExists($pluginName);
        if($pluginExists){
            echo "That plugin already exists.  Please try again.\n";
        }
    }
}

function promptForAuthorName() : void{
    global $authorName;
    $validAuthor = false;
    while(!$validAuthor){
        $authorName = promptForInput("Enter the author's name");
        $validAuthor = validFileName($authorName);
        if(!$validAuthor){
            echo "Author's name cannot start with a number.  Please try again. \n";
        }
    }
}

function promptForOrganizationName() : void{
    global $organizationName;
    $validOrg = false;
    while(!$validOrg){
        $organizationName = promptForInput("Enter the organization name");
        $validOrg = !is_numeric($organizationName[0]);
        if(!$validOrg){
            echo "Organization name can not start with a number.  Please try again. \n";
        }
    }
}

if(isset($argv[1]) && !pluginExists($argv[1])){
    $pluginName = $argv[1];
}else{
    promptForPluginName();
}

if(isset($argv[2])){
    if(is_numeric($argv[2][0])){
        echo "Author's cannot start with a number.  Please try again. \n";
        promptForAuthorName();
    }else{
        $authorName = $argv[2];
    }
}else{
    promptForAuthorName();
}

// Some user's may not have an organization name and may get stuck on this.  Intentionally leaving it as silently optional.
if(isset($argv[3])){
    if(is_numeric($argv[3][0])){
        echo "Organization name can not start with a number.  Please try again. \n";
        promptForOrganizationName();
    }else{
        $organizationName = $argv[3];
    }
}else{
    $organizationName = $authorName;
}



$namespace = $organizationName . "\\" . strtolower($pluginName);

$manifest = [
    "name" => $pluginName,
    "version" => "0.0.1",
    "main" => $namespace . "\\$pluginName",
    "api" => "3.11.0"
];
@mkdir(PLUGIN_DIR);
mkdir(PLUGIN_DIR . $pluginName);
yaml_emit_file(PLUGIN_DIR . $pluginName. DS . "plugin.yml", $manifest);
mkdir(PLUGIN_DIR . $pluginName . DS . "resources");
yaml_emit_file(PLUGIN_DIR . $pluginName . DS . "resources" . DS . "config.yml", "# Add configuration data for your plugin to this file. ");
mkdir(PLUGIN_DIR . $pluginName . DS . "src");
mkdir(PLUGIN_DIR . $pluginName . DS . "src" . DS . $organizationName);
$pluginDir = PLUGIN_DIR . $pluginName. DS . "src" . DS . $organizationName . DS . strtolower($pluginName) . DS;
mkdir($pluginDir);

$pluginData = "<?php\n";
$pluginData .= "declare(strict_types=1);\n\n";
$pluginData .= "namespace $namespace;\n\n";
$pluginData .= "use pocketmine\plugin\PluginBase;\n";
$pluginData .= "class $pluginName extends PluginBase{\n\n}";


file_put_contents($pluginDir . $pluginName . ".php", $pluginData);

echo "Generated skeleton files for $pluginName in ". PLUGIN_DIR . $pluginName;

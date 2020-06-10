# PocketMine-MP Development Environment

This is a PhpStorm project that will work as a development environment for working with PocketMine-MP plugins.

## Prerequisites ##

For this project to work correctly, you must have a compatible PHP binary on you computer and PhpStorm setup to properly use it.
- Download a PHP binary for your OS from https://jenkins.pmmp.io/job/PHP-7.3-Aggregate/
- Extract the files to a location on your computer (e.g. C:\php\php7.3)
- Go to PhpStorm's settings for Language and Frameworks, then choose the PHP section
- Update "PHP language level" to 7.3
- Click the triple dot button next the "CLI Interpreter".  This will open a new window
- In the "CLI Interpreter" window, change the name to something like PHP PocketMine
- Click the folder icon in the "PHP executable" option.
- Locate your php executable file (e.g C:\php\php7.3\php.exe)
- Click "Apply" then "OK" in the "CLI Interpreter" window
- Click the "PHP Runtime" tab just below the "CLI Interpreter" box
- Click the button at the bottom that says "Sync Extensions with Interpreter"
- Click "Apply" then "OK" in the "Settings" window

You are now ready to use this project.

## First Use
This project comes with several pre-built Run Configurations.  The first one that should be used is "Setup Environment".  It will create the necessary folders for use with the other runtime configurations and setup a copy of PocketMine-MP for library and test purposes.

Once you've used the "Setup Environment" run configuration, you can start adding plugin source code folders to the "plugins" folder that was created.

## After Setup
After running "Setup Environment" a "plugins" folder and "server" folder will be in the project folder. These folder contain the various parts and pieces that will be used for developing and testing your plugins. 

>#### Plugins Folder
>You can put the source code of your plugins in sub-directories of the "plugins" folder.  It is important that the subdirectory contain the plugin.yml file and src folder of your plugin.  An example of what this needs to look like will have been created during the setup.

>___
>#### Server Folder
>The server folder contains a PocketMine-MP server environment.  Most of the server settings files will be generated the first time you use the "Run Server" or "Run Server (All Plugins)" options.

>___
>#### Making New Plugins
>You can start a new plugin by using the "Generate Plugin Skeleton" run configuration.  This will prompt you for the plugin name and the author's name, then generate the necessary files to start a plugin.

>___
>#### Updating PocketMine-MP
>Occasionally, you may need to update the version of PocketMine-MP your test environment is using. The easiest way to do this is to use the "Update PocketMine-MP" run configuration.  This will remove the current PocketMine-MP server file and replace it with whatever the most current, stable version is on Jenkins.  Optionally, you can manually update the file by downloading a replacement from [Jenkins](https://jenkins.pmmp.io/job/PocketMine-MP/).

>___
>#### DeVirion
>The test environment will include a plugin to help you test called DeVirion. This plugin loads special libraries that some plugins may need. If you need details on how to use it, you can get that from its page on [Poggit](https://poggit.pmmp.io/p/Devirion).  It can be updated as needed using the "Update DeVirion" run configuration.

# vQmod for Osclass
An adaptation inspired in OpenCart vQmod for Osclass.

### What is vQmod?

vQmod™ (aka Virtual Quick Mod) is an override system designed to avoid having to change core files. Instead of making changes to the core files directly, the changes are created as xml search/replace script files. These script files are parsed during page load as each "source" core file is loaded with the "include" or "require" php functions. The source is then patched with the script file changes, and saved to a temp file. That temp file is then substituted for the original during execution. The original source file is never altered. This results in a "virtual" change to the core during execution without any actual modification to the core files. Visit the [vQmod™ official repository](https://github.com/vqmod/vqmod).

## Installation

1) Download.

2) Install vQmod plugin uploading the osclass-vqmod-vX.X.X.zip file in your Osclass.

3) Go to the 'Manage mods' tab and click in 'Enable' button. It's all!

## Usage

1) Click in 'Manage mods' tab, then clic in 'Add mod file'.

2) Select and upload a zip file that contains the mod's xml file.

3) Enable the xml file.

## Tips for making mods

- The mods it's a .xml files.

- IMPORTANT - To make mods aimed at specifically modifying another Osclass plugin, it is recommended that the name of the xml file SHOULD BE THE SAME NAME AS THE FOLDER of the target plugin. If you uninstall the target plugin, the mod xml will automatically be disabled, and the cache will be purged as well.

- The location path of a mod starts from the same folder of the vQmod plugin (oc-content/plugins/vqmod), therefore if you want to apply a mod to another plugin, you should consider pointing to a previous location in the 'file' tag of the xml file, for example:

my_plugin.xml:
```xml
<file name="../my_plugin/index.php">
```

- Or if you want to change some other file outside environment plugins should target three times at a previous location, for example:

custom_mod.xml:
```xml
<file name="../../../oc-admin/ajax/ajax.php">
```
For more informatoin about it, visit [How to make vQmod Scripts
](https://github.com/vqmod/vqmod/wiki/Scripting#how-to-make-vqmod-scripts) and learn some [Examples](https://github.com/vqmod/vqmod/wiki/Examples).

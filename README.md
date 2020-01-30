# vQmod for Osclass
An adaptation of OpenCart vQmod for Osclass.

## Installation

1) Download.

2) Install vQmod plugin uploading the osclass-vqmod-vX.X.X.zip file in your Osclass.

3) Go to the 'Manage mods' tab and click in 'Enable' button. It's all!

## Usage

1) Click in 'Manage mods' tab, then clic in 'Add mod file'.

2) Select and upload a zip file that contains the mod's xml file.

3) Enable the xml file.

## Tips for making mods

a) The mods it's a xml files.

b) IMPORTANT: To make mods aimed at specifically modifying another Osclass plugin, it is recommended that the name of the xml file SHOULD BE THE SAME NAME AS THE FOLDER of the target plugin. If you uninstall the target plugin, the mod xml will automatically be disabled, and the cache will be purged as well.

c) The location path of a mod starts from the same folder of the vQmod plugin (oc-content/plugins/vqmod), therefore if you want to apply a mod to another plugin, you should consider pointing to a previous location in the 'file' tag of the xml file, for example:

my_plugin.xml:
```xml
<file name="../my_plugin/index.php">
```

d) If you want to change some other file outside environment plugins should target three times at a previous location, for example:

custom_mod.xml:
```xml
<file name="../../../oc-admin/ajax/ajax.php">
```

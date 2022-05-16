# Doom Quickbuild
A simple build script for building doom projects into a coherent pk3 file, with ACC compilation, DECORATE/ZSCRIPT index generation, and bundling handled for you.

# Prerequisites
You need to have PHP available in your path.

On linux, php can be installed with

    sudo apt-get install php

On windows, PHP can be downloaded from php.net and installed to anywhere. There are plenty of guides online which talk about how to add a folder to your PATH

Once you have PHP installed, just copy the folder structure into your project. That should be the build folder, make.sh and config.json

To be able to create zip archives, you will need to have a zip program (7zip is recommended) available.

# Usage

Copy the build folder (or symlink it) into your project folder, at the root level. You will also need to create config.json, an example can be found in src/config_example.json.

Config files consist of a header and 3 parts:

- Inputs
- Steps
- Outputs

## Header

a "debug" element can be specified, and if set to true, will cause the program to output more information when running.

## Inputs

Inputs determine which folders are used when building. Generally you should only need 1 input, and it should be the folder where your project source files are located. All files from the source folder will be automatically copied into each output (with the exception of ACS source files if you have set include_src as false in an ACS step).

## Steps

Steps determine what is done to the input files. These are done before files are copied into the outputs. There are currently 3 supported step types:

- ACS Steps
- DECORATE Steps
- ZSCRIPT Steps

ACS Steps have the following syntax:

    {"type":"acs", "settings":{ "acc":"~/_dev/gzdoom_build/acc/acc", "dir":"SRC", "pattern":"*.*", "include_src":"true"} }

An ACS Step will automatically compile all acs script files in the directory specified by "dir", determined by a pattern set by "pattern". It is generally a good idea to use *.* or * as your pattern, unless you are doing something very specific. Setting "include_src" to anything other than true will exclude all original ACS source files from every output. The ACS step will automatically compile every script, stopping if any errors occur during compilation, and all compiled output files will be available to each output.

    {"type":"acs", "settings":{ "dir":"SRC", "pattern":"*.*", "include_src":"true"} }

Optionally, ACS steps may be specified without the acc parameter, which will allow quickbuild to use the version of acc installed in the `build/acc` folder. You will need to provide your own version of ACC, downloadable from [zdoom.org](zdoom.org)

Decorate Steps have the following syntax

    {"type":"decorate", "settings":{ "dir":"actors"} }

ZScript steps have a very similar syntax, with one additional parameter

    {"type":"zscript", "settings":{ "dir":"zsc", "version":"4.1.2"} }

In both cases, the contents of directory "dir" will be scanned, and an index file will be generated, which includes #include statements for every file found within the directory, including subdirectories. In zscript, the zscript version will also be made available at the top of the index file, or the default version of 4.1.3 will be used if no version is specified.

the files decorate.includes.txt and zscript.indluces.txt will be created and made available to all outputs.

All steps can be specified multiple times, if multiple directories are required.

## Outputs

Currently, 2 types of outputs are supported

- zip
- dir

WAD support is not available at this time, but may be implemented in the future.

When using zip support, any format can be used, however it is recommended to use zip or 7z, both of which can be created using 7zip. The example config shows which command should be specified to create zips in the right format using 7zip.

zip outputs should generally look similar to this

    {"type":"zip", "settings":{ "cmd":"7z -mx1 a","path":"./rel","name":"MyMod.pk7", "split":"false"} }

"cmd" specifies the command to be run when creating the zip. This should be the name of a command line tool as well as some arguments. The example above creates a 7zip archive using no compression. "path" determines the output directory, and "name" specifies the filename. If "split" is true, multiple archives will be created based on the folder structure of the inputs. This is designed to allow large mods to be split up into smaller files to make gameplay less choppy in gzdoom. In this case, a LOAD directive will automatically be added to [GAMEINFO](https://zdoom.org/wiki/GAMEINFO). It is still recommended to use directory outputs for very large mods, as gzdoom can read from directories with much less hiccups and stuttering.

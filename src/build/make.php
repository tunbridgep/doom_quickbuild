<?php

##include dependencies
require("basics.php");

$config = Config::GetConfig("config.json");
$temp = new Folder($config->GetTempDir());
$src = new Folder($config->GetSrcDir());


$temp->Create(true);

ensure_path_exists($src);

if ($config->GetDebug())
{
	echo "Source folder located at ".$src->GetPath().PHP_EOL;
	echo "Temp folder located at ".$temp->GetPath().PHP_EOL;
}

##compile scripts
if ($config->HasACS())
{
	$acc = new File($config->GetACCPath());
	$acs_src = new Folder($src->GetPath().$config->GetACSPath());
	$acs_error = new File($acs_src->GetPath()."acs.err");
	$acs_output = new Folder($temp->GetPath()."acs");
	$pattern = $config->GetACSPattern();

	$acs_output->Create();

	ensure_path_exists($acc);
	ensure_path_exists($acs_src);

	$acs_error->Delete(); #if we ended on an error last time, it will be the first thing we compile, which will itself cause an error

	echo "Compiling ACS scripts...".PHP_EOL;
	foreach($acs_src->GetContents($pattern) as $sc)
	{
		$script = new File($sc);

		$output_path = new File($acs_output->GetPath().$script->GetName(false).".o");
		compile_script($script,$output_path,$acc,$acs_error);
	}
}

#handle DECORATE autogeneration
if ($config->HasDecorate())
{
	$include_dir = new Folder($src->GetPath().$config->GetDecorateDir());
	$output = new File($temp->GetPath()."decorate.includes.txt");
	echo "Generating decorate.includes.txt and adding decorate files from ".$include_dir->GetPath()."...".PHP_EOL;

	$includes = "";
	foreach($include_dir->GetContents() as $path)
	{
		#remove src dir from our paths, so that they are relative to the source directory
		$relative_path = str_replace($src->GetPath(),"",$path);
		$includes .= '#include "'.$relative_path.'"'.PHP_EOL;
	}
	$output->Write($includes);
}

#handle ZSCRIPT autogeneration
if ($config->HasZScript())
{
	$include_dir = new Folder($src->GetPath().$config->GetZScriptDir());
	$output = new File($temp->GetPath()."zscript.includes.txt");
	echo "Generating zscript.includes.txt and adding decorate files from ".$include_dir->GetPath()."...".PHP_EOL;

	$includes = 'version "'.$config->GetZScriptVersion().'"'.PHP_EOL;
	foreach($include_dir->GetContents() as $path)
	{
		#remove src dir from our paths, so that they are relative to the source directory
		$relative_path = str_replace($src->GetPath(),"",$path);
		$includes .= '#include "'.$relative_path.'"'.PHP_EOL;
	}
	$output->Write($includes);
}

#move over all other files from src, except the ACS source folder
#this has to happen regardless of whether or not we are zipping
foreach($src->GetFoldersInside() as $f)
{
	$folder = new Folder($f);
	$destination = new Folder($temp->GetPath().$folder->GetName());

	#skip copying this folder if it's our ACS source folder, and we aren't allowing source code (the include_src parameter)
	if ($config->HasACS())
	{
		$acs_src = new Folder($src->GetPath().$config->GetACSPath());
		if ($folder->GetPath() == $acs_src->GetPath() && !$config->GetACSIncludeSource())
			continue;
	}

	echo "Copying ".$folder->GetPath()." to ".$destination->GetPath()."...".PHP_EOL;
	$folder->CopyTo($destination);
}
foreach($src->GetFilesInside() as $f)
{
	$file = new File($f);
	echo "Copying loose-file ".$file->GetPath()." to ".$temp->GetPath()."...".PHP_EOL;
	$file->CopyTo($temp);
}


#OKAY, our temp folder has been created. Now to make our build methods
if ($config->HasZip())
{
	echo "Generating zip archive...".PHP_EOL;
	$zip_dir = new Folder($config->GetZipDir());
	$zip_dir->Create(true);
	$zip_cmd = $config->GetZipCmd();
	$zip_input = $temp->GetPath(); 
	$zip_output = $zip_dir->GetPath().$config->GetZipFilename();
	generate_zip($zip_cmd,$zip_input,$zip_output,$config->GetZipSplitFile());
}
#this has to go last because it does something funky
#it doesn't actually "generate" a build deliverable,
#it just steals the temp folder
if ($config->HasBuildDir())
{
	$out = new Folder($config->GetBuildDir());
	$out->Create(true);
	echo "Generating build folder...".PHP_EOL;
	$temp->Rename($out->GetPath());
}
else
{
	#clean up our mess
	$temp->Delete();
}
?>

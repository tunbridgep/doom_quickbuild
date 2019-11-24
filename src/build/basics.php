<?php

#include everything from the build folder
$includes = array_merge(glob("./build/core/*"),glob("./build/inputs/*"),glob("./build/steps/*"),glob("./build/outputs/*"));
foreach ($includes as $include_file)
	require($include_file);

function ensure_path_exists(FilesystemObject $path)
{
	if (!$path->Exists())
		build_error_missing_path($path);
}

#make some quick and dirty debug functions
function config_error(string $text)
{
	die("CONFIG ERROR: ".$text." - please check config.json".PHP_EOL);
}
function build_error(string $text)
{
	die("BUILD ERROR: ".$text.PHP_EOL);
}
function acs_error(File $errorfile)
{
	die("BUILD ERROR: Compilation failed".PHP_EOL."ACS ERROR: ".file_get_contents($errorfile->GetPath()));
}
function build_error_missing_path(FilesystemObject $path)
{
	die("BUILD ERROR: Path does not exist (".$path->GetPath().")".PHP_EOL);
}
function build_warning(string $text)
{
	echo("BUILD WARNING: ".$text.PHP_EOL);
}
?>

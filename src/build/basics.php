<?php

#include everything from the build folder
foreach (glob("./build/*") as $include_file)
{
	$filename = basename($include_file);
	if ($filename !== "basics.php" && $filename !== "make.php") #don't include ourselves
	{
		#echo $include_file.PHP_EOL;
		require($include_file);
	}
}

function ensure_path_exists(FilesystemObject $path)
{
	if (!$path->Exists())
		build_error_missing_path($path);
}

#make some quick and dirty debug functions
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

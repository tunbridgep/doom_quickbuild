<?php

function compile_script(File $script_path, File $output_path, File $acc_path, File $acc_error)
{
	$cmd = $acc_path->GetPath().' "'.$script_path->GetPath().'" "'.$output_path->GetPath().'"';

	echo "Compiling ".$script_path->GetName()."...".PHP_EOL;

	#we need to get rid of the spammy output of ACC
	if (IsWindows())
		exec ($cmd . " 1> NUL 2> NUL");
	else
		exec ($cmd . " > /dev/null 2> /dev/null");

	#check for any error files
	if ($acc_error->Exists())
		acs_error($acc_error);

}

?>

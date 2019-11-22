<?php

function generate_zip(string $zip, string $input_path, string $output_path, bool $split = false)
{

	#generate zip - super easy if we aren't using split
	if (!$split)
	{
		$cmd = $zip.' "'.$output_path.'" "'.$input_path.'*"';
		$cmd = str_replace("  "," ",$cmd); #clean up
		#echo "Running zip command: ".$cmd.PHP_EOL;
		exec($cmd);
	}
	else #we have to do it manually! We need to zip everything except the big folders
	{
		$gameinfo = "";
		foreach(glob($input_path.'*') as $path)
		{
			$cmd = "";
			if (is_dir($path))
			{
				$extension = ".".pathinfo($output_path,PATHINFO_EXTENSION);
				$name_without_extension = rtrim(str_replace($extension,"",$output_path),".");
				$name_suffix = strtolower(basename($path));
				$name = $name_without_extension."_".$name_suffix.$extension;

				#if we have the "hires" folder, we need to add it to gameinfo last
				if ($name_suffix != "hires")
					$gameinfo .= '"'.basename($name).'", ';
				$cmd = $zip.' "'.$name.'" "'.$path.'"';
			}
			else
			{
				$cmd = $zip.' "'.$output_path.'" "'.$path.'"';
			}

			#echo "cmd: ".$cmd.PHP_EOL;
			exec ($cmd);
		}

		#add gameinfo to first file
		if ($gameinfo != "")
		{
			$gameinfo = "LOAD = ".rtrim($gameinfo,", "); #remove any trailing commas and spaces, and add the "LOAD = " string
			$gameinfo_path = $input_path."gameinfo.txt";
			if (file_exists($gameinfo_path))
				file_put_contents($gameinfo_path,PHP_EOL.$gameinfo,FILE_APPEND);
			else
				file_put_contents($gameinfo_path,$gameinfo);
			$cmd = $zip.' "'.$output_path.'" "'.$gameinfo_path.'"';
			#echo "gameinfo cmd: ".$cmd.PHP_EOL;
			exec ($cmd);
		}
	}
}

?>

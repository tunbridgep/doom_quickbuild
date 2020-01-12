<?php

class ZScriptStep extends Step
{
	private $dir;
	private $version;

	public function __construct(Folder $dir, string $version, Folder $working_dir)
	{
		$this->dir = $dir;
		$this->version = $version;
		$this->working_dir = $working_dir;
	}

	public function Perform(Folder $input)
	{
		$decorate_path = $input->GetSubPath($this->dir->GetPath());

		$files = $decorate_path->GetContents();

		if (is_null($files) || count($files) == 0)
		{
			echo "no zscript files".PHP_EOL;
		}
		else
		{

			echo "Generating zscript.includes.txt and adding zscript files from ".$decorate_path->GetPath()."...".PHP_EOL;
			$includes = "version ".$this->version.PHP_EOL;
			$output = new File($this->working_dir->GetPath()."zscript.includes.txt");

			foreach($files as $path)
			{
				#remove src dir from our paths, so that they are relative to the source directory
				$relative_path = str_replace($input->GetPath(),"",$path);
				$includes .= '#include "'.$relative_path.'"'.PHP_EOL;
			}
			$output->Write($includes);
		}
	}
	
	public function GetFancyName()
	{
		return "ZScript Index Generation";
	}

	public function GetType()
	{
		return 'zscript';
	}
}
?>

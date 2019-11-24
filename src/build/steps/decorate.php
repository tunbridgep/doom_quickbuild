<?php

class DecorateStep extends Step
{
	private $dir;

	public function __construct(Folder $dir, Folder $working_dir)
	{
		$this->dir = $dir;
		$decorate_folder = new Folder('decorate');
		$this->working_dir = $working_dir;
	}

	public function Perform(Folder $input)
	{
		$decorate_path = $input->GetSubPath($this->dir->GetPath());
		$output = new File($this->working_dir->GetPath()."decorate.includes.txt");
		echo "Generating decorate.includes.txt and adding decorate files from ".$input->GetPath()."...".PHP_EOL;

		$includes = "";
		foreach($decorate_path->GetContents() as $path)
		{
			#remove src dir from our paths, so that they are relative to the source directory
			$relative_path = str_replace($input->GetPath(),"",$path);
			$includes .= '#include "'.$relative_path.'"'.PHP_EOL;
		}
		$output->Write($includes);
	}
	
	public function GetFancyName()
	{
		return "Decorate Index Generation";
	}

	public function GetType()
	{
		return 'decorate';
	}
}

?>

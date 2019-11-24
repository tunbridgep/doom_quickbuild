<?php

class ACSStep extends Step
{
	private $acc;
	private $dir;
	private $pattern;
	private $include_src;

	public function __construct(File $acc, Folder $dir, string $pattern, bool $include_src, Folder $working_dir)
	{
		$this->acc = $acc;
		$this->dir = $dir;
		$this->pattern = $pattern;
		$this->include_src = $include_src;
		$acs_folder = new Folder('acs');
		$this->working_dir = $working_dir->GetSubPath($acs_folder->GetPath());
	}

	#compile all ACS scripts
	public function Perform(Folder $input)
	{
		#create our subdirectory
		$this->working_dir->Create();

		$acs_path = $input->GetSubPath($this->dir->GetPath());
		$acs_error = $acs_path->GetSubPath('acs.err');
		echo $acs_path->GetPath().PHP_EOL;
		echo $acs_error->GetPath().PHP_EOL;
		echo $this->working_dir->GetPath().PHP_EOL;

		#remove acs.err if it exists
		if ($acs_error->Exists())
			$acs_error->Delete();

		foreach($acs_path->GetContents($this->pattern) as $acs_file)
		{
			$cmd = $this->acc->GetPath().' "'.$acs_file.'" "'.$this->working_dir->GetPath().'"';

			echo "Compiling ".$acs_file."...".PHP_EOL;

			#we need to get rid of the spammy output of ACC
			if (IsWindows())
				exec ($cmd . " 1> NUL 2> NUL");
			else
				exec ($cmd . " > /dev/null 2> /dev/null");

			#check for any error files
			if ($acs_error->Exists())
				acs_error($acs_error);
		}
	}

	public function GetFancyName()
	{
		return "ACS Compilation";
	}

	public function GetType()
	{
		return 'acs';
	}
}

?>

<?php

class ACSStep extends Step
{
	private $acc;
	private $dir;
	private $pattern;
	private $include_src;
	private $recursive;
	private $keep_error_file;

	public function __construct(File $acc, Folder $dir, string $pattern, bool $include_src, bool $recursive, Folder $working_dir, bool $keep_error_file)
	{
  		$acs_folder = new Folder('acs');
	
		$this->acc = $acc;
		$this->dir = $dir;
		$this->pattern = $pattern;
		$this->include_src = $include_src;
		$this->recursive = $recursive;
		$this->keep_error_file = $keep_error_file;
		$this->working_dir = $working_dir->GetSubPath($acs_folder->GetPath());
	}

	#compile all ACS scripts
	public function Perform(Folder $input)
	{
		#create our subdirectory
		$this->working_dir->Create();

		$acs_path = $input->GetSubPath($this->dir->GetPath());
		$acs_error = $acs_path->GetSubPath('acs.err');
		//echo $acs_path->GetPath().PHP_EOL;
		//echo $acs_error->GetPath().PHP_EOL;
		//echo $this->working_dir->GetPath().PHP_EOL;

		#remove acs.err if it exists
		if ($acs_error->Exists() && !$this->keep_error_file)
			$acs_error->Delete();

		$files = $acs_path->GetContents($this->pattern,$this->recursive);

		if (is_null($files) || count($files) == 0)
		{
			echo "nothing to compile".PHP_EOL;
		}
		else
		{

			foreach($files as $acs_file)
			{
				$filename = pathinfo($acs_file,PATHINFO_FILENAME);
				$cmd = $this->acc->GetPath().' "'.$acs_file.'" "'.$this->working_dir->GetPath().$filename.'.o'.'"';

				echo "Compiling ".$acs_file."...".PHP_EOL;

				#we need to get rid of the spammy output of ACC
				if (IsWindows())
					exec ($cmd . " 1> NUL 2> NUL");
				else
					exec ($cmd . " > /dev/null 2> /dev/null");

				#check for any error files
				if ($acs_error->Exists())
					acs_error($acs_error,!$this->keep_error_file);
			}
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

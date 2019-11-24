<?php

class DirInput extends Input
{
	private $location;
	private $working_dir;
	function __construct(Folder $location,Folder $working_dir)
	{
		$this->location = $location;
		$this->working_dir = $working_dir;
	}
	
	public function GetPath()
	{
		return $this->location->GetPath();
	}

	public function GetSource(FilesystemObject $source)
	{
		return $this->location->GetFile($source->GetPath());
	}

	public function AddToWorkingDirectory()
	{

	}
}

?>

<?php

abstract class Step
{
	protected $working_dir;
	public abstract function GetFancyName();
	public abstract function GetType();
	public abstract function Perform(Folder $input);
	public function GetWorkingDir()
	{
		return $this->working_dir;
	}
}

?>

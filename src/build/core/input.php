<?php

abstract class Input
{
	public abstract function GetPath();
	public abstract function GetSource(FilesystemObject $source);
	public abstract function AddToWorkingDirectory();
}

?>

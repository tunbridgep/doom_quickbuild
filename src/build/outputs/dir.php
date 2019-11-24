<?php

class DirOutput extends Output
{
	private $src;
	private $dest;
	public function __construct(Folder $src,Folder $dest)
	{
		$this->src = $src;
		$this->dest = $dest;
	}

	public function Generate()
	{
		echo "Generating output directory: ".$this->dest->GetPath().PHP_EOL;
		$this->dest->Create();

		$this->src->CopyTo($this->dest);
	}
}

?>

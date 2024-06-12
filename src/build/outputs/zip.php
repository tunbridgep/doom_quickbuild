<?php

class ZipOutput extends Output
{
	private $cmd;
	private $src;
	private $output;
	private $dest;
	private $split;

	public function __construct(string $cmd,Folder $src,Folder $dest,string $name,bool $split)
	{
		$this->cmd = $cmd;
		$this->src = $src;
		$this->dest = $dest;
		$this->output = $dest->GetSubPath($name);
		$this->split = $split;
	}

	public function Generate()
	{
		echo "Generating output zip: ".$this->output->GetPath().PHP_EOL;
		#generate zip - super easy if we aren't using split
		if (!$this->split)
		{
			$this->output->Delete();
			$this->dest->Create();
			$cmd = $this->cmd.' "'.$this->output->GetPath().'" "'.$this->src->GetPath().'*"';
			#echo "Running zip command: ".$cmd.PHP_EOL;
			#die();
			exec($cmd);
		}
		else #we have to do it manually! We need to zip everything except the big folders
		{
			$this->dest->Delete();
			$this->dest->Create();
			$gameinfo = "";
			$hires = false;
			
			$extension = ".".pathinfo($this->output->GetPath(),PATHINFO_EXTENSION);
			$name_without_extension = rtrim(str_replace($extension,"",$this->output->GetPath()),".");
			foreach(glob($this->src->GetPath().'*') as $path)
			{
				$cmd = "";
				if (is_dir($path) && strtolower(basename($path)) != "hires")
				{
					$name_suffix = strtolower(basename($path));
					$name = $name_without_extension."_".$name_suffix.$extension;

					#if we have the "hires" folder, we need to add it to gameinfo last
					if ($name_suffix != "hires")
						$gameinfo .= '"'.basename($name).'", ';
					else
						$hires = true;
					$cmd = $this->cmd.' "'.$name.'" "'.$path.'"';
				}
				else
				{
					$cmd = $this->cmd.' "'.$this->output->GetPath().'" "'.$path.'"';
				}

				#echo "cmd: ".$cmd.PHP_EOL;
				exec ($cmd);
			}
			#add hires to gameinfo if we need to
			if ($hires)
				$gameinfo .= '"'.basename($name_without_extension)."_hires".$extension.'", ';


			#add gameinfo to first file
			if ($gameinfo != "")
			{
				$gameinfo = "LOAD = ".rtrim($gameinfo,", "); #remove any trailing commas and spaces, and add the "LOAD = " string
				$gameinfo_path = $this->src->GetPath()."gameinfo.txt";
				if (file_exists($gameinfo_path))
					file_put_contents($gameinfo_path,PHP_EOL.$gameinfo,FILE_APPEND);
				else
					file_put_contents($gameinfo_path,$gameinfo);
				$cmd = $this->cmd.' "'.$this->output->GetPath().'" "'.$gameinfo_path.'"';
				#echo "gameinfo cmd: ".$cmd.PHP_EOL;
				exec ($cmd);
			}
		}
	}
}

?>

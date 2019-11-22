<?php

class Config
{
	public static function GetConfig(string $filename)
	{
		##read config
		if (!file_exists($filename))
			die($filename." not found...aborting".PHP_EOL);
		$json = json_decode(file_get_contents($filename), $options = JSON_THROW_ON_ERROR);

		if ($json == null)
			die ("could not decode ".$filename." - please ensure your JSON syntax is correct".PHP_EOL);

		#turn json into a class
		$class = new Config();
		foreach ($json as $key => $value)
			$class->{$key} = $value;

		if (!isset($class->src) && !isset($class->src['dir']))
			die("config: 'src' array not correctly specified".PHP_EOL);
		else if (isset($class->acs) && (!isset($class->acs['acc']) || !isset($class->acs['dir'])))
			die("config: 'acs' array not correctly specified".PHP_EOL);
		else if (isset($class->decorate) && (!isset($class->decorate['dir'])))
			die("config: 'decorate' array not correctly specified".PHP_EOL);
		else if (isset($class->zscript) && (!isset($class->zscript['dir'])))
			die("config: 'zscript' array not correctly specified".PHP_EOL);
		else if (isset($class->out) && !isset($class->out['dir']))
			die("config: 'out' array not correctly specified".PHP_EOL);
		else if (isset($class->zip) && (!isset($class->zip['cmd']) || !isset($class->zip['dir']) || !isset($class->zip['name'])))
			die("config: 'zip' array not correctly specified".PHP_EOL);

		return $class;
	}
	private function __construct() {}

	public function GetSrcDir()
	{
		return $this->src['dir'];
	}

	public function GetTempDir()
	{
		return ".".DIRECTORY_SEPARATOR."temp";
	}
	
	public function HasBuildDir()
	{
		return isset($this->out);
	}

	public function GetBuildDir()
	{
		return isset($this->out['dir']) ? $this->out['dir'] : null;
	}

	public function HasACS()
	{
		return isset($this->acs);
	}

	public function GetACSPath()
	{
		if (!isset($this->acs))
			return null;
		else
			return $this->acs['dir'];
	}

	public function GetACSPattern()
	{
		if (!isset($this->acs['pattern']))
			return "*.acs";
		else
			return $this->acs['pattern'];
	}

	public function GetACCPath()
	{
		if (!isset($this->acs))
			return null;
		else
			return $this->acs['acc'];
	}

	public function GetACSIncludeSource()
	{
		return (isset($this->acs['include_src']) && $this->acs['include_src'] == "true") ? true : false;
	}

	public function HasDecorate()
	{
		return isset($this->decorate);
	}

	public function GetDecorateDir()
	{
		return isset($this->decorate) ? $this->decorate['dir'] : null;
	}

	public function HasZScript()
	{
		return isset($this->zscript);
	}

	public function GetZScriptDir()
	{
		return isset($this->zscript) ? $this->zscript['dir'] : null;
	}

	public function GetZScriptVersion()
	{
		return isset($this->zscript['version']) ? $this->zscript['version'] : "4.1.3";
	}
	
	public function GetDebug()
	{
		return (isset($this->debug) && $this->debug == "true") ? true : false;
	}
	
	public function HasZip()
	{
		return isset($this->zip);
	}

	public function GetZipCmd()
	{
		return isset($this->zip['cmd']) ? $this->zip['cmd'] : null;
	}
	
	public function GetZipSplitFile()
	{
		return (isset ($this->zip['split']) && $this->zip['split'] == "true") ? true : false;
	}
	
	public function GetZipFilename()
	{
		return isset($this->zip['name']) ? $this->zip['name'] : null;
	}
	
	public function GetZipDir()
	{
		return isset ($this->zip['dir']) ? $this->zip['dir'] : null;
	}
}

?>

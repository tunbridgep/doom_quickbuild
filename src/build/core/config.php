<?php

class Config
{
	private $steps;
	private $outputs;
	private $inputs;
	private $temp_dir;
	private $debug;

	public function __construct(string $filename)
	{
		$this->steps = array();
		$this->outputs = array();
		$this->inputs = array();
		$json = null;

		##read config
		if (!file_exists($filename))
			die($filename." not found...aborting".PHP_EOL);
		$json = json_decode(file_get_contents($filename), $options = JSON_THROW_ON_ERROR);

		if ($json == null)
			die ("could not decode ".$filename." - please ensure your JSON syntax is correct".PHP_EOL);

		if (!array_key_exists('inputs',$json))
			config_error('no inputs specified');
		if (!array_key_exists('outputs',$json))
			config_error('no outputs specified');
		//if (!array_key_exists('steps',$json))
			//config_error('no steps specified');

		#set top-level values
		$this->debug = Config::GetArrayKeyOrDefault($json,'debug',false) == "true";
		$this->temp_dir = new Folder(Config::GetArrayKeyOrDefault($json,'temp_path',"./temp"));

		#set other values
		foreach ($json['inputs'] as $input)
			$this->generateInput($input);
		foreach ($json['steps'] as $step)
			$this->generateStep($step);
		foreach ($json['outputs'] as $output)
			$this->generateOutput($output);
	}

	#TODO: Make this work properly
	private function generateInput(array $input)
	{
		if (array_key_exists('dir',$input))
		{
			$dir = new Folder($input['dir']);
			array_push($this->inputs,$dir);
		}
	}

	private function generateStep(array $step)
	{
		if (!array_key_exists('type',$step))
		{
			if (!array_key_exists('_type',$step))
				config_error("step specified with no type");
			else
				return;
		}
		if (!array_key_exists('settings',$step))
			config_error("step of type '".$step['type']."' specified with no settings");

		$settings = $step['settings'];
		switch($step['type'])
		{
			case "acs":
				if (!array_key_exists('dir',$settings))
					config_error("'dir' setting must be present when using a step of type 'acs'");
				if (!array_key_exists('acc',$settings))
					config_error("'acc' setting must be present when using a step of type 'acs'");

				$acc = new File($settings['acc']);
				if (!$acc->Exists())
					config_error("'acc' refers to a location that does not exist (".$acc->GetPath().")");

				$include_src = Config::GetArrayKeyOrDefault($settings,'include_src',false) == "true";
				$pattern = Config::GetArrayKeyOrDefault($settings,'pattern',"*.*");
				$recursive = Config::GetArrayKeyOrDefault($settings,'recursive',true) == "true";
				$keep_error_file = Config::GetArrayKeyOrDefault($settings,'keep_error_file',false) == "true";
				$dir = new Folder($settings['dir']);
				array_push($this->steps,new ACSStep($acc,$dir,$pattern,$include_src,$recursive,$this->temp_dir,$keep_error_file));
				break;
			case "decorate":
				if (!array_key_exists('dir',$settings))
					config_error("'dir' setting must be present when using a step of type 'decorate'");

   				$outfile = Config::GetArrayKeyOrDefault($settings,'output',"decorate.txt");
				$dir = new Folder($settings['dir']);
				array_push($this->steps,new DecorateStep($dir,$this->temp_dir,$outfile));
				break;
			case "zscript":
				if (!array_key_exists('dir',$settings))
					config_error("'dir' setting must be present when using a step of type 'zscript'");

				$dir = new Folder($settings['dir']);
				$version = Config::GetArrayKeyOrDefault($settings,'version','4.1.3');
   				$outfile = Config::GetArrayKeyOrDefault($settings,'output',"zscript.txt");
				array_push($this->steps,new ZScriptStep($dir,$version,$this->temp_dir,$outfile));
				break;
			default:
				config_error("unknown step type '".$step['type']."'");
		}

	}
	
	private function generateOutput(array $output)
	{
		if (!array_key_exists('type',$output))
			config_error("output specified with no type");
		if (!array_key_exists('settings',$output))
			config_error("output of type '".$output['type']."' specified with no settings");

		$settings = $output['settings'];
		switch($output['type'])
		{
			case "dir":
				if (!array_key_exists('path',$settings))
					config_error("'path' setting must be present when using an output of type 'dir'");
				
				$dir = new Folder($settings['path']);
				array_push($this->outputs,new DirOutput($this->temp_dir,$dir));
				break;
			case "zip":
				if (!array_key_exists('path',$settings))
					config_error("'path' setting must be present when using an output of type 'zip'");
				if (!array_key_exists('cmd',$settings))
					config_error("'cmd' setting must be present when using an output of type 'zip'");
				
				$cmd = $settings['cmd'];
				$dir = new Folder($settings['path']);
				$name = Config::GetArrayKeyOrDefault($settings,'name',"out.zip");
				$split = Config::GetArrayKeyOrDefault($settings,'split',false) == "true";
				array_push($this->outputs,new ZipOutput($cmd,$this->temp_dir,$dir,$name,$split));
				break;
			default:
				config_error("unknown output type '".$output['type']."'");
		}
	}

	public function IsDebug()
	{
		return $this->debug;
	}

	public function GetTempDir()
	{
		return $this->temp_dir;
	}

	public function GetInputs()
	{
		return $this->inputs;
	}

	public function GetSteps()
	{
		return $this->steps;
	}

	public function GetOutputs()
	{
		return $this->outputs;
	}

	#this returns the value of an array key, or if it doesn't exist, returns a specified default value
	private static function GetArrayKeyOrDefault($array,$value,$default)
	{
		if (!isset($array))
			return $default;
		else if (isset($array[$value]))
			return $array[$value];
		else
			return $default;

	}
}

?>

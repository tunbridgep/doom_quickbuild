<?php

##include dependencies
require("basics.php");

if ($argc > 1)
    $config = new Config($argv[1]);
else
	$config = new Config("config.json");
$temp = $config->GetTempDir();
$temp->Create(true);


if ($config->IsDebug())
{
	echo "Temp folder located at ".$temp->GetPath().PHP_EOL;
}

#copy each input file to the temp dir
foreach($config->GetInputs() as $input)
{
	echo "Input ->'".$input->GetPath()."'...".PHP_EOL;
	echo "Copying files from ".$input->GetPath()." to ".$temp->GetPath().PHP_EOL;
	$input->CopyTo($temp,array('.git'));
}

#execute each of our steps on our temp folder
foreach ($config->GetSteps() as $step)
{
	echo "Performing step ".$step->GetFancyName()."...".PHP_EOL;
	$step->Perform($temp);
}

#everything is done, run out outputs
foreach($config->GetOutputs() as $output)
{
	$output->Generate();
}

#delete temp dir
$temp->Delete();

echo "BUILD COMPLETE".PHP_EOL;

?>

<?php

##include dependencies
require("basics.php");

$config = new Config("config.json");
$temp = $config->GetTempDir();
$temp->Create(true);


if ($config->IsDebug())
{
	echo "Temp folder located at ".$temp->GetPath().PHP_EOL;
}

#execute each of our steps on each of our inputs
foreach($config->GetInputs() as $input)
{
	#perform all our steps on it
	echo "Input ->'".$input->GetPath()."'...".PHP_EOL;
	foreach ($config->GetSteps() as $step)
	{
		echo "Performing step ".$step->GetFancyName()."...".PHP_EOL;
		$step->Perform($input);
	}

	#we have processed our input. Copy it over to the temp folder
	echo "Copying files from ".$input->GetPath()." to ".$temp->GetPath().PHP_EOL;
	$input->CopyTo($temp);
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

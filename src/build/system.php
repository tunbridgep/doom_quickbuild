<?php

function IsWindows()
{
	return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
}

?>

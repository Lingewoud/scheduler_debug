<?php
if (!defined('TYPO3_cliMode')) {
	die('You cannot run this script directly!');
}

$parameters = $_SERVER['argv'];
if(isset($parameters[1])) {

	if($parameters[1]=='list') {
		echo "Configured Tasks\n";
		echo "Not yet implemented\n";
	}
	elseif($parameters[1]=='info')
	{
		if(is_numeric($parameters[2])) {

			echo "Running as single task\n";

			$scheduler = t3lib_div::makeInstance('tx_scheduler');
			$task = $scheduler->fetchTask($parameters[2]);
			$scheduler->executeTask($task);
			if (!$task->areMultipleExecutionsAllowed()) {
				echo "Parallel execution:    not allowed\n";
			}
			else
			{
				echo "Parallel execution:    allowed\n";
			}

			if ($task->isExecutionRunning()) {
				echo "Job status:            running\n";
			}
			else
			{
				echo "Job status:            not running\n";
			}
		}
		else
		{
			echo "ERROR: status needs numeric job id as second argument\n";
			print_usage();
		}
	}
	elseif($parameters[1]=='run')
	{
		if(is_numeric($parameters[2])) {

			echo "Running as single task\n";

			$scheduler = t3lib_div::makeInstance('tx_scheduler');
			$task = $scheduler->fetchTask($parameters[2]);
			$scheduler->executeTask($task);
			if (!$task->areMultipleExecutionsAllowed() && $task->isExecutionRunning()) {
				echo "ERROR: still running.\n";
				exit(1);
			}
		}
		else
		{
			echo "ERROR: run needs numeric job id as second argument\n";
			print_usage();
		}
	}
	else {
		print_usage();
	}
}
else {
	print_usage();
}

function print_usage()
{
	echo "scheduler_debug. Helps debugging the scheduler tasks.\n";
	echo "\n";
	echo "usage: cli_dispatch.phpsh schedulerdebug [command]\n";
	echo "\n";
	echo "Available commands:\n";
	echo "list                  List all Scheduler Tasks\n";
	echo "run [id]              Run schedular taks \n";
	echo "status [id]           Show if job is running or available to start\n";
}

?>

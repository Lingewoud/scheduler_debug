<?php
if (!defined('TYPO3_cliMode')) {
	die('You cannot run this script directly!');
}

$parameters = $_SERVER['argv'];
if(isset($parameters[1])) {

	if($parameters[1]=='list') {
		echo "Configured Tasks\n";

		listTasks();
	}
	elseif($parameters[1]=='info')
	{
		if(is_numeric($parameters[2])) {

			echo "Running as single task\n";

			$scheduler = t3lib_div::makeInstance('tx_scheduler');
			$task = $scheduler->fetchTask($parameters[2]);
			#$scheduler->executeTask($task);
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
	elseif($parameters[1]=='stop')
	{
		if(is_numeric($parameters[2])) {

			echo "Stopping ...\n";

			$scheduler = t3lib_div::makeInstance('tx_scheduler');
			$task = $scheduler->fetchTask($parameters[2]);
			if (!$task->isExecutionRunning()) {
				echo "ERROR: Job is not running. Can't be stopped.\n";
				exit(1);
			}
			else
			{
				$result = $task->unmarkAllExecutions();
				if ($result) {
					echo "Job has been stopped.\n";
				} else {
					echo "Error: failed stopping job.\n";
				}
			}
		}
		else
		{
			echo "ERROR: stop needs numeric job id as second argument\n";
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


/**
 * Assemble display of list of scheduled tasks
 *
 * @return string Table of pending tasks
 */
 function listTasks() {
	// Define display format for dates
	$dateFormat = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'];
	$content = '';

	$scheduler = t3lib_div::makeInstance('tx_scheduler');
	// Get list of registered classes
	$registeredClasses = getRegisteredClasses();

	// Get all registered tasks
	$query = array(
		'SELECT'  => '*',
		'FROM'    => 'tx_scheduler_task',
		'WHERE'   => '1=1',
		'ORDERBY' => 'nextexecution'
	);

	$res = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($query);
	$numRows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
	// No tasks defined, display information message
	if ($numRows == 0) {
		echo "No tasks defined";

	} else {

		$row[0] = 'ID      ';
		$row[1] = ' | ';
		$row[2] = 'TASK NAME  ';

		$table[] = $row;
		$row[0] = '--------';
		$row[1] = '---';
		$row[2] = '--------------------------------------';

		$table[] = $row;
		// Loop on all tasks
		while (($schedulerRecord = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
			$lastExecution = '-';
			$isRunning = FALSE;
			$executionStatus = 'scheduled';
			$executionStatusOutput = '';
			$name = '';
			$nextDate = '-';
			$execType = '-';
			$frequency = '-';
			$multiple = '-';
			$startExecutionElement = '&nbsp;';

			// Restore the serialized task and pass it a reference to the scheduler object
			/** @var $task tx_scheduler_Task */
			$task = unserialize($schedulerRecord['serialized_task_object']);

			if ($scheduler->isValidTaskObject($task)) {
				// The task object is valid
				$name = htmlspecialchars($registeredClasses[$schedulerRecord['classname']]['title']. ' (' . $registeredClasses[$schedulerRecord['classname']]['extension'] . ')');

				$row[0] = $schedulerRecord['uid'];
				$row[1] = ' | ';
				$row[2] = $name;
				
				$table[]=$row;

			} else {
				$row[0] = $schedulerRecord['uid'];
				$row[1] = ' | ';
				$row[2] = 'INVALID TASK';

				$table[]=$row;
			}

			$tr++;
		}
		// Render table
		$padding=strlen($table[0][0]);
		echo "\n";
		foreach ($table as $r){
			echo str_pad($r[0],$padding,' ', STR_PAD_LEFT);
			echo $r[1];
			echo str_pad($r[2],50);
			echo "\n";
		}
		echo "\n";
	}

	$GLOBALS['TYPO3_DB']->sql_free_result($res);
}

	/**
	 * This method a list of all classes that have been registered with the Scheduler
	 * For each item the following information is provided, as an associative array:
	 *
	 * ['extension']	=>	Key of the extension which provides the class
	 * ['filename']		=>	Path to the file containing the class
	 * ['title']		=>	String (possibly localized) containing a human-readable name for the class
	 * ['provider']		=>	Name of class that implements the interface for additional fields, if necessary
	 *
	 * The name of the class itself is used as the key of the list array
	 *
	 * @return array List of registered classes
	 */
	 function getRegisteredClasses() {
		$list = array();
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'] as $class => $registrationInformation) {

				$title         = isset($registrationInformation['title'])         ? $GLOBALS['LANG']->sL($registrationInformation['title'])         : '';
				$description   = isset($registrationInformation['description'])   ? $GLOBALS['LANG']->sL($registrationInformation['description'])   : '';

				$list[$class] = array(
					'extension'     => $registrationInformation['extension'],
					'title'         => $title,
					'description'   => $description,
					'provider'		=> isset($registrationInformation['additionalFields']) ? $registrationInformation['additionalFields'] : ''
				);
			}
		}

		return $list;
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

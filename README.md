# TYPO3 Scheduler Debug

TYPO3 extension which provides a CLI tool to query for Scheduler jobs and to test singular scheduler jobs.

# How it works

## list configured tasks

	typo3/cli_dispatch.phpsh schedulerdebug list

## run a tasks

	typo3/cli_dispatch.phpsh schedulerdebug run 18

## get job status info

	typo3/cli_dispatch.phpsh schedulerdebug info 18
	
## stop job 

	typo3/cli_dispatch.phpsh schedulerdebug stop 18

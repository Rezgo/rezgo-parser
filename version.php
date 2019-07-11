<?php
	$version = file_get_contents($_SERVER[DOCUMENT_ROOT].'/../README.md');
	/*
	if(file_exists('/vol/conf/revision')) {
		$version .= '-'.file_get_contents('/vol/conf/revision');
	}
	*/
	echo $version;
	
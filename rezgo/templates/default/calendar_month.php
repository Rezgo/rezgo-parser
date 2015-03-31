<?php

	$site->getCalendar($_REQUEST['uid'], $_REQUEST['date']);

	$calendar_days = $site->getCalendarDays();
	
	foreach ( $site->getCalendarDays() as $day ) {
		
		if ($day->cond == 'a') { $class = ''; } // available
		elseif ($day->cond == 'p') { $class = 'passed'; }
		elseif ($day->cond == 'f') { $class = 'full'; }
		elseif ($day->cond == 'i' || $day->cond == 'u') { $class = 'unavailable'; }
		elseif ($day->cond == 'c') { $class = 'cutoff'; }		

		if ($day->date) { // && (int)$day->lead != 1
			$calendar_events .= '"'.date('Y-m-d', $day->date).'":{"class": "'.$class.'"},'."\n";
		}
				
	}
		
	$calendar_events = trim($calendar_events, ','."\n");

?>

<script>
	$('.responsive-calendar').responsiveCalendar('edit', {
			<?=$calendar_events?>
	});
</script>
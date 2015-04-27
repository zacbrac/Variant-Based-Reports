<?php
date_default_timezone_set('America/Los_Angeles');
/**
 * IntervalMaker
 */
class IntervalMaker {
	// public function format_timestamps($startdate, $finishdate) {
	// 	$day1 = strtotime(date('Y:m:d', $startdate) . ' 00:00:00');
	// 	$day2 = strtotime(date('Y:m:d', $finishdate) . ' 00:00:00');
	// 	return [$day1, $day2];
	// }

	public function createDates($startdate, $finishdate, $desired_interval, $format = 'm/d/Y H:i:s', $timezone = 'PST') {

		// $formatted_timestamps = $this->format_timestamps($startdate, $finishdate);
		// $startdate = $formatted_timestamps[0];
		// $finishdate = $formatted_timestamps[1];

		$interval = $this->setInterval($desired_interval);

		//DETERMINES INITIAL NEXT DATE, THIS WILL BE RESET IN THE WHILE LOOP
		$nextdate = strtotime($interval, $startdate);

		if ($startdate !== $finishdate) {
			//ADDS FIRST TWO DATES
			$times = array(date($format, $startdate) . " " . $timezone, date($format, $nextdate) . " " . $timezone);
			$nextdate2 = '';

			if ($nextdate > $finishdate) {
				return array(date($format, $startdate) . " " . $timezone);
			} else {
				//ADDS THE REST OF THE DATES
				while ($nextdate2 < $finishdate) {
					$nextdate = strtotime($interval, $nextdate);
					$nextdate2 = strtotime($interval, $nextdate);

					$times[] = date($format, $nextdate) . " " . $timezone;
				}
			}

		} else {
			$times[] = array($startdate, $finishdate);
		}

		return $times;
	}

	public function createTimestamps($startdate, $finishdate, $desired_interval) {

		// $formatted_timestamps = $this->format_timestamps($startdate, $finishdate);
		// $startdate = $formatted_timestamps[0];
		// $finishdate = $formatted_timestamps[1];

		$interval = $this->setInterval($desired_interval);

		//DETERMINES INITIAL NEXT DATE, THIS WILL BE RESET IN THE WHILE LOOP
		$nextdate = strtotime($interval, $startdate);

		if ($startdate !== $finishdate) {
			//ADDS FIRST TWO DATES
			$timestamps[] = array($startdate, $nextdate);
			$timestamps[] = array($nextdate, strtotime($interval, $nextdate));
			$nextdate2 = '';

			if ($nextdate > $finishdate) {
				return array(array($startdate, $finishdate));
			} else {
				//ADDS THE REST OF THE DATES
				while ($nextdate2 < $finishdate) {
					$nextdate = strtotime($interval, $nextdate);
					$nextdate2 = strtotime($interval, $nextdate);

					$timestamps[] = array($nextdate, $nextdate2);
				}
			}

		} else {
			$timestamps[] = array($startdate, $finishdate);
		}

		return $timestamps;
	}

	protected function setInterval($desired_interval) {
		if ($desired_interval == 'hours') {$interval = "+1 hour";}
		if ($desired_interval == 'days') {$interval = "+1 day";}
		if ($desired_interval == 'weeks') {$interval = "+1 week";}
		if ($desired_interval == 'months') {$interval = "+1 month";}
		if ($desired_interval == 'years') {$interval = "+1 year";}
		return $interval;
	}
}

// $IntervalMaker = new IntervalMaker;
// $dates = $IntervalMaker->createDates(1429488000, 1429747200, 'months');
// $timestamps = $IntervalMaker->createTimestamps(1429488000, 1429747200, 'months');
// echo date('m/d/Y H:i:s', $timestamps[0][0]) . "\n";
// echo date('m/d/Y H:i:s', $timestamps[0][1]) . "\n";
// var_export($timestamps);
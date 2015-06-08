<?php
date_default_timezone_set('America/Los_Angeles');
/**
* IntervalMaker
*/
class IntervalMaker {

    public function createDates($startdate, $finishdate, $desired_interval, $format = 'm/d/Y H:i:s', $timezone = 'PST') {
        $times = array();
		
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
        $timestamps = array();

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
        switch ($desired_interval) {
            case 'hours':
                return '+1 hour';
            case 'days':
                return '+1 day';
            case 'weeks':
                return '+1 week';
            case 'months':
                return '+1 month';
            case 'years':
                return '+1 year';
            default:
                return null;
        }
    }
}
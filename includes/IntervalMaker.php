<?php
/**
 * IntervalMaker
 */
class IntervalMaker {

    public function createDates(\DateTime $startDate, \DateTime $finishDate, \DateInterval $desiredInterval) {

        $times = array();

        //DETERMINES INITIAL NEXT DATE, THIS WILL BE RESET IN THE WHILE LOOP
        $nextDate = clone $startDate;
        $nextDate->add($desiredInterval);

        if ($nextDate > $finishDate) {

            $times[] = array($startDate, $nextDate);

        } else {

            //ADDS THE REST OF THE DATES
            while ($nextDate < $finishDate) {

                $times[] = array(clone $startDate, clone $nextDate);
                $startDate->add($desiredInterval);
                $nextDate->add($desiredInterval);

            }

            $times[] = array($startDate, $nextDate);

        }

        return $times;

    }

}
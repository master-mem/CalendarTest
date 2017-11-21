<?php
namespace Calendar;

use DateTimeInterface;
use DateTime;
use DateInterval;
use DatePeriod;
use Exception;

class Calendar implements CalendarInterface
{
    private $date;
    /**
     * @param DateTimeInterface $datetime
     */
    public function __construct(DateTimeInterface $date)
    {
        $this->date = $date;
    }
    /**
     * Get the day
     *
     * @return int
     */
    public function getDay()
    {
        return $this->getFormattedDate('D');
    }
    /**
     * Get the weekday (1-7, 1 = Monday)
     *
     * @return int
     */
    public function getWeekDay()
    {
        return $this->getFormattedDate('WD');
    }
    /**
     * Get the first weekday of this month
     *
     * @return int
     */
    public function getFirstWeekDay()
    {
        return $this->getFormattedDate('FWD');
    }
    /**
     * Get the first week of this month
     *
     * @return int
     */
    public function getFirstWeek()
    {
        return $this->getFormattedDate('FW');
    }
    /**
     * Get the number of days in this month
     *
     * @return int
     */
    public function getNumberOfDaysInThisMonth()
    {
        return $this->getFormattedDate('DIM');
    }
    /**
     * Get the number of days in the previous month
     *
     * @return int
     */
    public function getNumberOfDaysInPreviousMonth()
    {
        return $this->getFormattedDate('DIPM');
    }

    /**
     * Formats the date for the given flag
     * D = Day
     * WD = Week Days
     * WS = Week Start etc
     * This part could be refactored and created as Renderer Abstract Class, Facade etc
     *
     * @throws Exception
     * @returns Mixed
     */
    private function getFormattedDate(string $dateType)
    {
        try {
            if (empty($dateType) || !is_string($dateType)) {
                throw new \Exception("The dateType is undefined.");
            }
            switch ($dateType) {
                case 'D':
                    return (int)$this->date->format('j');
                    break;
                case 'WD':
                    return (int)$this->date->format('N');
                    break;
                case 'FWD':
                    $weekday = new DateTime( $this->date->format('Y-m-1'));
                    return (int)$weekday->format('N');
                    break;
                case 'FW':
                    $firstWeek = new DateTime( $this->date->format('Y-m-1'));
                    return (int)$firstWeek->format('W');
                    break;
                case 'WS':
                    return new DateTime($this->date->format('Y-m-1'));
                    break;
                case 'LW':
                    $lastWeek = new DateTime($this->date->format('Y-m-t'));
                    return $lastWeek->format('W');
                    break;
                case 'CW':
                    return (int)$this->date->format('W');
                    break;
                case 'CY':
                    return (int)$this->date->format('Y');
                    break;
                case 'DIM':
                    $noOfDaysInMonth = new DateTime( $this->date->format('Y-m-d'));
                    return (int) $noOfDaysInMonth->format('t');
                    break;
                case 'DIPM':
                    $noOfDaysInPreviousMonth = new DateTime( $this->date->format('Y-m-1') );
                    return (int)$noOfDaysInPreviousMonth->modify('-1 month')->format('t');
                    break;
                case 'MW':
                    $firstWeek = $this->getFormattedDate('FW');
                    $lastWeek = $this->getFormattedDate('LW');

                    $weeksYear = ($this->getFormattedDate('CY') % 4 === 0) ? 53 : 52;
                    if($firstWeek > $lastWeek) {
                        return (($weeksYear + $lastWeek) - $firstWeek) + 1;
                    }
                    return ($lastWeek - $firstWeek) + 1;
                    break;
                default:
                    throw new \Exception("The dateType is unknown.");
                    break;
            }
        }
        catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * Returns a date range at 1 day interval
     *
     * @param $weekDay
     * @return array
     */
    private function getIntervalPeriod(DateTime $weekDay)
    {
        return new DatePeriod($weekDay, new DateInterval('P1D'), 6);
    }
    /**
     * Returns an array of days, with values set to true if the days is in the given week
     *
     * @param $weekNo
     * @param $weekDay
     * @return array
     */
    private function getDaysInWeek($weekNo, DateTime $weekDay)
    {
        $currentWeek = $this->getFormattedDate('CW');
        $range = $this->getIntervalPeriod($weekDay);

        $weekDays = array();

        $validWeek = $currentWeek - 1 <= 0 ? 53 : $currentWeek - 1;
        $highLight = ($validWeek === $weekNo);
        foreach($range as $day) {
            $weekDays[$day->format('j')] = $highLight;
        }

        return $weekDays;
    }


    /**
     * Get the calendar array
     *
     * @return array
     */
    public function getCalendar()
    {
        $weeks = array();
        $firstWeek = $this->getFormattedDate('FW');

        $weekDay = new DateTime();
        $weekStart = $this->getFormattedDate('WS');

        $weekDay->setISODate((int)$weekStart->format('o'), $firstWeek);

        for($i = 1, $weekTotal = $this->getFormattedDate('MW'); $i <= $weekTotal; $i++) {
            //Write calendar
            $weeks[$firstWeek] = $this->getDaysInWeek($firstWeek, $weekDay);
            //Set valid week number
            $firstWeek = $firstWeek + 1 > 53 ? 1 : $firstWeek + 1;
            $weekDay->modify('+1 week');
        }
        return $weeks;
    }

}
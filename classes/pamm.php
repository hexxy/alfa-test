<?php
/**
 * Created by JetBrains PhpStorm.
 * User: hexxy
 * Date: 4/26/14
 * Time: 3:11 PM
 * To change this template use File | Settings | File Templates.
 */

require_once BASE_PATH . '/classes/model.php';

/**
 * Модель для работы с таблицами pamm, pamm_stat,pamm_stat_monthly
 */
class PammAccount extends Model
{

    /**
     * Возвращает все ПАММ счета из БД
     *
     * @return array
     */
    public function fetchPamms()
    {
        $pamms = $this->getConnection()->fetchAll(
            "SELECT `pamm_id`,`account_id`,`name` FROM `pamm`"
        );

        return $pamms;
    }

    /**
     * Возвращает статистику по ПАММ счетам: лучший\худший месяцы, средний месячный доход, доход за последний год
     *
     * @return array
     */
    public function fetchAll()
    {
        $pamms = $this->fetchPamms();
        foreach ($pamms as &$pamm) {
            $pamm['best_month'] = $this->getBestMonth($pamm['pamm_id']);
            $pamm['worst_month'] = $this->getWorstMonth($pamm['pamm_id']);
            $pamm['avg_monthly_profit'] = $this->getAverageMonthlyProfit($pamm['pamm_id']);
            $pamm['yearly_profit'] = $this->getYearlyProfit($pamm['pamm_id']);
        }

        return $pamms;
    }

    /**
     * Возвращает наибольший месячный доход для указанного ПАММ счета
     *
     * @param int $pamm_id
     * @return int
     */
    public function getBestMonth($pamm_id)
    {
        $result = $this->getConnection()->fetchAll(
            "SELECT profit FROM pamm_stat_monthly psm WHERE psm.`pamm_id` = ? ORDER BY profit DESC LIMIT 1",
            array((int) $pamm_id)
        );

        $value = 0;
        if (isset($result[0])){
            $value = $result[0]['profit'];
        }
        return $value;
    }

    /**
     * Возвращает наименьший месячный доход для указанного ПАММ счета
     *
     * @param int $pamm_id
     * @return int
     */
    public function getWorstMonth($pamm_id)
    {
        $result = $this->getConnection()->fetchAll(
            "SELECT profit FROM pamm_stat_monthly psm WHERE psm.`pamm_id` = ? ORDER BY profit ASC LIMIT 1",
            array((int) $pamm_id)
        );

        $value = 0;
        if (isset($result[0])){
            $value = $result[0]['profit'];
        }
        return $value;
    }

    /**
     * Возвращает средний месячный доход для указанного ПАММ счета
     *
     * @param int $pamm_id
     * @return int
     */
    public function getAverageMonthlyProfit($pamm_id)
    {
        $result = $this->getConnection()->fetchAll(
            "SELECT profit FROM pamm_stat_monthly psm WHERE psm.`pamm_id` = ?",
            array((int) $pamm_id)
        );

        $number_of_months = count($result);
        if ($number_of_months <= 0)
        {
            return 0;
        }
        $profit = 0;
        foreach ($result as $month)
        {
            $profit += $month['profit'];
        }

        return number_format($profit/$number_of_months, 2);
    }

    /**
     * Возвращает доход за последний год для указанного ПАММ счета
     *
     * @param int $pamm_id
     * @return int
     */
    public function getYearlyProfit($pamm_id)
    {

        // статистика за последние 12 месяцев (или меньше)
        $result = $this->getConnection()->fetchAll(
            "SELECT profit FROM pamm_stat_monthly psm WHERE psm.`pamm_id` = ? ORDER BY `date` DESC LIMIT 12",
            array((int) $pamm_id)
        );

        $number_of_months = count($result);
        if ($number_of_months <= 0)
        {
            return 0;
        }
        $profit = 0;
        foreach ($result as $month)
        {
            $profit += $month['profit'];
        }

        return number_format($profit/$number_of_months, 2);
    }
    /**
     * Возвращает подневную статистику по указанным ПАММ счетам для вывода на графике
     * Статистика возвращается в виде ассоциативного массива:
     * - ключ labels содержит массив названий ПАММ счетов
     * - ключ stat содержит массив с подневной статистикой по каждому счету:
     * array('date' => ..., 'счет1' => доходность, 'счет2' => доходность,...)
     *
     * При расчете статистики используются сложные проценты.
     *
     * @param int $ids
     * @return array
     */
    public function getStatsForGraph($ids)
    {
        $data = array();
        $labels = array();

        foreach ($ids as $id)
        {
            $pamm_info = $this->getPammInfo($id);
            $pammName = $pamm_info[0]['name'];
            $labels[] = $pammName;
            $stat = $this->getPammStatistics($id);

            $cumulativeProfit = 100;
            foreach ($stat as $record)
            {
                if (!$data[$record['date']])
                {
                    $data[$record['date']] = array('date' => $record['date']);
                }

                $cumulativeProfit += $cumulativeProfit*$record['profit']/100;
                $data[$record['date']][$pammName] = round($cumulativeProfit - 100,2);
            }
        }
        return array('labels' => $labels, 'stat' => array_values($data));
    }

    /**
     * Возвращает информацию по указанному ПАММ счету
     *
     * @param $id
     * @return array
     */
    public function getPammInfo($id)
    {
        $result = $this->getConnection()->fetchAll(
            "SELECT *  FROM pamm p  WHERE p.`pamm_id` = ? LIMIT 1",
            array((int) $id)
        );
        return $result;
    }

    /**
     * Возвращает подневную статистику для указанного ПАММ счета
     *
     * @param $id
     * @return array
     */
    public function getPammStatistics($id)
    {
        $result = $this->getConnection()->fetchAll(
            "SELECT `date`,`profit` FROM pamm_stat ps WHERE ps.`pamm_id` = ? ORDER BY `date` ASC",
            array((int) $id)
        );
        return $result;

    }
}
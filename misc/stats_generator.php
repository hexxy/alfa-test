<?php
/**
 * Created by JetBrains PhpStorm.
 * User: hexxy
 * Date: 4/26/14
 * Time: 5:08 PM
 * To change this template use File | Settings | File Templates.
 */

/**
 *  Скрипт генерирует SQL стейтменты с тестовыми данными. На вход скрипту подается pamm_id счета, для которого будет генериться статистика, и количество записей в статистике
 *  Ex: php stats_generator.php 2 400
 */

$pamm_id = $argv[1];

$records_number = $argv[1];
$date = strtotime('2011-01-01');

$processing_month = "01";
$monthly_profit = 0;

for ($i=1; $i<$records_number;$i++)
{
    $profit = rand(-100,100)/100;
    $date = strtotime("+1 day",$date);

    $month =  date("m", $date);
    if ($month != $processing_month)
    {
        $monthly_date = date("Y-m-01",strtotime("-1 day",$date));
        echo "INSERT INTO `pamm_stat_monthly` (`pamm_id`,`date`,`profit`) VALUES ({$pamm_id},'{$monthly_date}','{$monthly_profit}');" . PHP_EOL;
        $monthly_profit = 0;
        $processing_month = $month;
    }
    $monthly_profit += $profit;

    $date_string = date("Y-m-d", $date);
    echo "INSERT INTO `pamm_stat` (`pamm_id`,`date`,`profit`) VALUES ({$pamm_id},'{$date_string}','{$profit}');" . PHP_EOL;
}
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: hexxy
 * Date: 4/26/14
 * Time: 3:22 PM
 * To change this template use File | Settings | File Templates.
 */

/**
 * Класс для работы с базой данных.
 *
 * Содержит методы для работы с БД.
 */
abstract class Model
{
    /**
     * Подключение к базе.
     *
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    public function __construct(\Doctrine\DBAL\Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Возвращает соединение с базой данных.
     *
     * @return \Doctrine\DBAL\Connection
     */
    protected function getConnection()
    {
        return $this->connection;
    }
}
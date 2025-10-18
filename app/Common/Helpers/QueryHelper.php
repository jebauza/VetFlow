<?php

namespace App\Common\Helpers;

class QueryHelper
{
    public static function getRealQuery($query, $dumpIt = false)
    {
        $queryString = $query->toSql();
        $bindings = $query->getBindings();

        $arr = explode('?', $queryString);
        $result = '';
        foreach ($arr as $key => $item) {
            if ($key < count($arr) - 1) {
                $result = $result . $item . "'" . $bindings[$key] . "'";
            }
        }
        $result = $result . $arr[count($arr) - 1];

        try {
            $connectionQuery = $query->getConnection();
            $connection = $connectionQuery->getName();
            $driverName = $connectionQuery->getDriverName();
            $databaseName = $connectionQuery->getDatabaseName();
            $host = config("database.connections.$connection")['host'];
            $port = config("database.connections.$connection")['port'];

            $databaseConnected = "Connection: $connection, Driver: $driverName, Host: $host, Port: $port, Database: $databaseName, SQL =>";
        } catch (\Exception $e) {
            $databaseConnected = 'Error: ' . $e->getMessage() . ', SQL =>';
        }

        if ($dumpIt) {
            dd("$databaseConnected  $result");
        }

        return "$databaseConnected  $result";
    }

    public static function insertModel($model, array $data)
    {
        $item = $model::insert([$data]);

        return $item;
    }
    public static function addConnectionToTable(string $table, string $connection_name = 'pgsql_etl')
    {
        $connections = config('database.connections');
        if (($connection_name == 'pgsql_etl' || $connection_name == 'pgsql') && !empty($connections[$connection_name])) {
            return '"' . $connections[$connection_name]['database'] . '"."' . $connections[$connection_name]['schema'] . '"."' . $table . '"';
        }

        return $table;
    }

    public static function getSqlYearMonthExpression(string $column, string $table = null)
    {
        $tableColumn = $table ? "$table.$column" : $column;

        $yearMonthExpression = "TO_CHAR($tableColumn, 'yyyy-mm-01')";
        $monthExpression = "TO_CHAR($tableColumn, 'mm')";

        return (object) compact('yearMonthExpression', 'monthExpression');
    }
}

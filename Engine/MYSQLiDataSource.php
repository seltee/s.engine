<?php
namespace Engine;

abstract class MYSQLiDataSource extends \Engine\DataSource
{
    abstract protected function GetRequestString();
    abstract protected function GetDataArray($request);

    protected $result = null;
    static $connection = null;

    protected function ObtainData($request){
        $connection = $this->GetConnection();

        $string = $this->GetRequestString();
        $dataArray = $this->GetDataArray($request);

        try {
            $this->result = $connection->prepare($string);
            $this->result->execute($dataArray);
        }catch (\PDOException $e){
            throw new \Exceptions\DefaultException($e->getMessage());
        }

        $out = array();
        while ($row = $this->result->fetch()) {
            $out[] = $row;
        }

        return $out;
    }

    protected function GetConnection(){
        if (!self::$connection){
            $dsn = "mysql:host=".SQL_SERVER.";dbname=".SQL_DB.";charset=".SQL_CHARSET;

            $opt = [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                $pdo = new \PDO($dsn, SQL_USER, SQL_PASSWORD, $opt);
            } catch (\PDOException $e) {
                throw new \Exceptions\DefaultException("Connection to DataBase failed. Check connection settings in init.php");
            }

            self::$connection = $pdo;
        }

        return self::$connection;
    }

    protected function GetRowCount(){
        if ($this->result){
            return $this->result->rowCount();
        }else{
            return null;
        }
    }

    protected function GetLastInsertId(){
        if ($this->result){
            return $this->GetConnection()->lastInsertId();
        }else{
            return null;
        }
    }
}
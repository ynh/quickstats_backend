<?php

class DB
{


    /**
     * @param String $query
     * @param array $parameters
     * @return PDOStatement
     */
    public static function execute($query = null, $parameters = array())
    {
        global $dbnew;

        $statement = $dbnew->prepare($query);


        foreach ($parameters as $k => $v) {
            if (is_array($v)) {
                $statement->bindValue($k, $v[0], $v[1]);
            } else {
                $statement->bindValue($k, $v);
            }
        }


        $statement->execute();
        return $statement;
    }

    /**
     * @param String $query
     * @param array $parameter
     * @return array|null
     */
    public static function run($query, $parameter = array())
    {
        $statement = self::execute($query, $parameter);
        if (startsWith($query, "SELECT")) {
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return null;
        }

    }
}
<?php

class DB
{


    /**
     * @param String $query
     * @param array $parameters
     * @return PDOStatement
     */
    public static function execute($query = null, $parameters = array(),$tdb=null)
    {
        global $dbnew;
        if($tdb==null)
            $tdb=$dbnew;
        $statement = $tdb->prepare($query);


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
    public static function run($query, $parameter = array(),$tdb=null)
    {
        $statement = self::execute($query, $parameter,$tdb);
        if (startsWith($query, "SELECT")) {
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return null;
        }

    }
}
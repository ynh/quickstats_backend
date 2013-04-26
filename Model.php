<?php

abstract class Model
{
    /**
     * @var array
     */
    private $_olddata = array();

    /**
     * @param array $properties
     */
    public function __construct($properties = array())
    {
        $reflect = new ReflectionObject($this);
        if (count($properties) > 0) {

            foreach ($reflect->getProperties() as $property) {
                if ($property->name[0] != '_') {
                    if (!array_key_exists($property->name, $properties)) {
                        throw new Exception("Property missing: {$property->name}");
                    }

                    $this->{$property->name} = $properties[$property->name];
                    $this->_olddata[$property->name] = $properties[$property->name];
                }
            }
        } else {
            foreach ($reflect->getProperties() as $property) {
                if ($property->name[0] != '_') {


                    $this->{$property->name} = null;

                }
            }
        }
        $this->onLoad();
    }
    public function setOld($key,$value){
        $this->_olddata[$key] = $value;
    }
    /**
     *
     */
    public function onLoad()
    {

    }

    public static function getKeys($haskey)
    {
        return $haskey ? array('id') : array();
    }

    /**
     *
     */
    public function beforeSave()
    {

    }

    /**
     * @param $glue
     * @param $conditions
     * @param $parameters
     * @param $counter
     * @return string
     */
    private static function build($glue, $conditions, &$parameters, &$counter)
    {
        if ($conditions == null) {
            return "";
        }
        $parts = array();
        foreach ($conditions as $k => $condition) {
            if ($k == "AND" || $k == "OR") {
                $parts[] = self::build($k, $condition, $parameters, $counter);
            } else {
                if (strpos($k, " ") == FALSE) {
                    $k = "`" . $k . "` = :val";
                }

                $parts[] = str_replace(':val', ':param' . $counter, $k);
                $parameters[":param" . $counter] = $condition;
                $counter++;
            }
        }


        return "(" . implode(" " . $glue . " ", $parts) . ")";
    }

    /**
     * @return string
     */
    public static function getTableName()
    {

        return "none";
    }

    /**
     * @return mixed
     */
    protected static function getFields()
    {
        static $fields = array();
        $called_class = get_called_class();

        if (!array_key_exists($called_class, $fields)) {
            $reflection_class = new ReflectionClass($called_class);

            $properties = array();

            foreach ($reflection_class->getProperties() as $property) {
                if ($property->name[0] != '_') {
                    $properties[] = '`' . $property->name . '`';
                }
            }

            $fields[$called_class] = $properties;
        }

        return $fields[$called_class];
    }


    /**
     * @param null $condition
     * @param null $order
     * @param null $limits
     * @return PDOStatement
     */
    protected static function getSelect($condition = null, $order = null, $limits = null)
    {
        $query = "SELECT " . implode(', ', static::getFields()) . " FROM `" . static::getTableName() . "`";
        $parameters = array();
        $counter = 0;
        $conditiondata = static::build("AND", $condition, $parameters, $counter);
        if ($conditiondata != "") {
            $query .= " WHERE " . $conditiondata;
        }
        if ($order != null) {
            foreach($order as $o){
                //Check alpha numeric
            }
            $query .= " ORDER BY " . implode(", ", $order);
        }
        if ($limits != null) {
            $query .= " LIMIT " . $limits[0] . " , " . $limits[1];
        }

        return DB::execute($query, $parameters);
    }


    /**
     *
     */
    public function save()
    {

        $this->beforeSave();
        global $dbnew;

        $fields = static::getFields();

        $hasid = in_array("`id`", $fields);
        $keys = static::getKeys($hasid);
        $update = count($keys) != 0;
        foreach ($keys as $k) {
            $update = $update && ($this->{$k} != NULL);
        }
        if ($update) {

            $updates = array();
            $parameters = array();
            $counter = 1;
            foreach ($fields as $f) {
                $fname = substr($f, 1, -1);
                if (!isset($this->_olddata[$fname]) || $this->_olddata[$fname] != $this->{$fname}) {
                    if ($this->{$fname} == null) {
                        $updates[] = $f . " = null";
                    } else {

                        $updates[] = $f . " = :" . $fname;
                        $parameters[":" . $fname] = $this->{$fname};
                    }
                    $counter++;
                }
            }

            if (count($updates) > 0) {

                $query = "UPDATE `" . static::getTableName() . "` SET ";
                $query .= implode(' , ', $updates);

                $filter = array();
                foreach ($keys as $k) {
                    $filter[$k] = ($this->{$k});
                }
                $counter = 0;
                $conditiondata = static::build("AND", $filter, $parameters, $counter);
                if ($conditiondata != "") {
                    $query .= " WHERE " . $conditiondata;
                }

                $statement = DB::execute($query, $parameters);
                if ($statement->rowCount() > 0) {
                    return;
                }
            }else{
                return;
            }

        }

        $insertfields = array();

        $parameters = array();
        $counter = 1;
        foreach ($fields as $f) {
            $fname = substr($f, 1, -1);
            if (!isset($this->_olddata[$fname]) || $this->_olddata[$fname] != $this->{$fname}) {
                if ($this->{$fname} != null) {
                    $insertfields[] = $f;
                    $parameters[":" . $fname] = $this->{$fname};
                }
                $counter++;
            }
        }

        if (count($insertfields) > 0) {
            $query = "INSERT INTO `" . static::getTableName() . "` ( ";
            $query .= implode(' , ', $insertfields);
            $query .= ") VALUES (";
            $query .= implode(' , ', array_keys($parameters));
            $query .= ")";
            DB::execute($query, $parameters);
            if ($hasid) {
                $this->id = $dbnew->lastInsertId();

            }
        }
        return;
    }

    public function resave()
    {
        if (isset($this->id)) {
            $this->id = null;
            $this->_olddata = array();
            return $this->save();
        }


    }

    /**
     * @param $id
     * @return null|static
     */
    public static function get($id)
    {
        return static::findOne(array('id' => array($id, PDO::PARAM_INT)));
    }


    /**
     * @param null $condition
     * @param null $order
     * @param null $limits
     * @return  static[]
     */
    public static function findAll($condition = null, $order = null, $limits = null)
    {
        $result = static::getSelect($condition, $order, $limits)->fetchAll(PDO::FETCH_ASSOC);
        $return = array();
        if (!$result) {


        } else {
            foreach ($result as $row) {
                $return[] = new static($row);
            }
        }
        return $return;
    }

    /**
     * @param null $condition
     * @return null|static
     */
    public static function findOne($condition = null)
    {
        $result = static::findAll($condition, null, array(0, 1));
        if (count($result) == 0) {
            return null;
        }
        return $result[0];
    }


    /**
     * @param null $condition
     * @return int
     */
    public static function count($condition = null)
    {
        return static::getSelect($condition, null, null)->rowCount();
    }


    /**
     *
     */
    public function delete()
    {
        DB::run("DELETE FROM " . static::getTableName() . " WHERE `id` = :id", array(':id' => array($this->id, PDO::PARAM_INT)));

    }
}

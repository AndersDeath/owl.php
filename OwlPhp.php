<?php

/**
 * @author VNBStudio.ru <andersdeath@yandex.ru>
 * @version 0.1 alpha
 * @package OwlPhp
 * @category OwlPhp
 * @description Helper for simplify work with php
 * @copyright Copyright (c) 2017, VNBStudio.ru
 */
class OwlPhp
{
    const OWL_ERROR              = "<span style='color:red; font-weight:bold;'>[ERROR]</span> <b>OWL SAY</b>: ";
    const MYSQL_CONNECTION_ERROR = self::OWL_ERROR."DATABASE NOT INIT";

    /**
     * OwlPhp constructor.
     */
    public function __construct($option = [])
    {
        if (count($option) > 0) {
            if (isset($option['mysql'])) {
                $this->db = false;
                $this->dbInit($option['mysql']);
            }
        }
    }

    /**
     * OwlPhp destructor.
     */
    public function __destruct()
    {
        
    }

    /**
     * @param array $option Settings for connection to mysql or postgresql
     */
    public function dbInit($option)
    {
        $driver   = isset($option['driver']) ? $option['driver'] : 'mysql';
        $host     = isset($option['host']) ? $option['host'] : '127.0.0.1';
        $dbname   = isset($option['dbname']) ? $option['dbname'] : '';
        $user     = isset($option['user']) ? $option['user'] : 'root';
        $password = isset($option['password']) ? $option['password'] : '';
        $connet   = $driver.':dbname='.$dbname.';host='.$host;
        try {
            $this->db = new PDO($connet, $user, $password);
        } catch (PDOException $e) {
            echo 'PDO connection failed: '.$e->getMessage();
        }
    }

    /**
     * @param string $sql SQL expression
     * @param string $data Data for select
     * @return result of select
     */
    public function dbSelect($sql, $data = [])
    {
        if (self::dbConnectTest()) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * @param string $sql SQL expression
     * @param string $data Data for insert
     * @return result of insert
     */
    public function dbInsert($sql, $data)
    {
        if (self::dbConnectTest()) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            return $stmt;
        }
    }

    /**
     * @param string $sql SQL expression
     * @param string $data Data for update
     * @return result of update
     */
    public function dbUpdate($sql, $data)
    {
        if (self::dbConnectTest()) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            return $stmt;
        }
    }

    /**
     * Connection test
     * @return boolean init or not init
     */
    private function dbConnectTest()
    {
        if (!$this->db) {
            $this->pf(self::MYSQL_CONNECTION_ERROR);
            return false;
        } else {
            return true;
        }
    }

    /**
     * Print format on screen, method for debug
     * @param mixed $input What is display
     * @param array $options option for print format
     * @return result of update
     */
    public function pf($input, $options = array())
    {
        if (!$input) {
            return false;
        }
        if (in_array('json', $options)) {
            echo json_encode($input);
        } else {
            if (gettype($input) == "boolean") {
                echo var_export($input);
            } else {
                echo "<pre>".var_export($input, true)."</pre>";
            }
        }
        if (in_array('exit', $options)) {
            exit();
        }
    }

    /**
     * get json from file
     * @param string $path Path to file
     * @param boolean $toString returns on string or array
     * @return json after parse
     */
    public function getJsonFromFile($path, $toString = false)
    {
        $file = file_get_contents($path);
        if ($toString) {
            return $file;
        } else {
            return json_decode($file, true);
        }
    }

    /**
     * Print on display phpinfo() and exit();
     */
    public function phpInfo()
    {
        phpinfo();
        exit();
    }
}
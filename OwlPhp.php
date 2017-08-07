<?php

/**
 * @author VNBStudio.ru <andersdeath@yandex.ru>
 * @version 0.3 alpha
 * @package OwlPhp
 * @category OwlPhp
 * @description Library for simplify work with php
 * @copyright Copyright (c) 2017, VNBStudio.ru
 */
class OwlPhp
{
    const OWL_ERROR                  = "<span style='color:red; font-weight:bold;'>[ERROR]</span> <b>OWL SAY</b>: ";
    const MYSQL_CONNECTION_ERROR     = self::OWL_ERROR."DATABASE NOT INIT";
    const JSON_FILE_CREATING_ERROR   = self::OWL_ERROR."FILE IS EXIST";
    const BASE64_FILE_CREATING_ERROR = self::OWL_ERROR."FILE IS EXIST";

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
            echo self::MYSQL_CONNECTION_ERROR;
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
     * get json from file
     * @param string $path Path where you want to create file
     * @param sting $name Name of file
     * @param array $data Data to encode
     * @param array $options Array with options to create
     * @return json after parse
     */
    public function putJsonToFile($path, $name, $data, $options = [])
    {
        $fileName = $path."/".$name.".json";
        if (file_exists($fileName)) {
            print self::JSON_FILE_CREATING_ERROR;
            exit();
        }
        $permissons = isset($options['permissions']) ? $options['permissions'] : 654;
        $fp         = fopen($fileName, "w");
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));
        fclose($fp);
        chmod($fileName, $permissons);
        return file_exists($fileName);
    }

    /**
     * get file form path or url and returns vase64 css string
     * @param string $path Path or Url to image
     * @param array $options Options
     * @return sting Base64 css sting
     */
    public function getBase64ImgCssString($path, $options = [])
    {
        if (in_array('fromUrl', $options)) {
            $curl     = curl_init();
            curl_setopt($curl, CURLOPT_URL, $path);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $resource = curl_exec($curl);
            if (!curl_errno($curl)) {
                $info = curl_getinfo($curl);
                if ($info["http_code"] == 200) {
                    $errmsg = "File uploaded successfully";
                }
            } else {
                $errmsg = curl_error($curl);
            }
            curl_close($curl);
            $base64 = base64_encode($resource);
            $mime   = $info['content_type'];
            return "data: {$mime};base64, {$base64}";
        } else {
            $type         = pathinfo($path, PATHINFO_EXTENSION);
            $data         = file_get_contents($path);
            $base64String = 'data:image/'.$type.';base64,'.base64_encode($data);
            return $base64String;
        }
    }

    /**
     * get base64File from path
     * @param string $path Path or Url to image
     * @return sting Base64 css sting
     */
    public function getBase64File($path)
    {
        $info = pathinfo($path);
        $data = file_get_contents($path);
        return [
            'info' => $info,
            'base64' => base64_encode($data),
        ];
    }

    /**
     * decode base64 string and put to file
     * @param string $path Path for file
     * @param string $base64String Base64 string
     * @param array $options Options
     * @return sting Base64 css sting
     */
    public function putBase64File($path, $base64String, $options = [])
    {
        if (file_exists($path)) {
            print self::BASE64_FILE_CREATING_ERROR;
            exit();
        }
        $permissons = isset($options['permissions']) ? $options['permissions'] : 654;
        $fp         = fopen($path, "w");
        fwrite($fp, base64_decode($base64String));
        fclose($fp);
        chmod($path, $permissons);
        return file_exists($path);
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
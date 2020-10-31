<?php

/**
  * This file contains the db class which is used to connect to the database
  * The methods used are for getting and setting data into the database
  *
  * $db->all() Returns all matched rows in the database, thus a multidimensional array
  * $db->row() Returns one (last) row matched, thus a single array
  * $db->one() Returns one (last) record matched, thus a single var. Mostly used for COUNT(*) statements
  * $db->none() Returns only the mysql answer, but no value. Used for INSERT, UPDATE and DELETE
  * $db->last() Returns the last id added into the database
  */

class db {
    
    public $db_debug;
    public $db_pdo;

    /**
     *
     * Create new PDO connection with the passed variables
     * Set attributes: Switch on errors and exceptions
     *
     * @param string $db_name        The name of the database
     * @param string $db_host        The location of the database
     * @param string $db_username    The username to access the database
     * @param string $db_password    The password of the username
     *
     */

    function __construct($db_name, $db_host, $db_username, $db_password)
    {

        $this->db_pdo = new PDO('mysql:dbname='.$db_name.';host='.$db_host, $db_username, $db_password);
        $this->db_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     *
     * Function to return all matched rows from database
     * TRY to run the SQL query
     * Return all of the results (if succeeded) in a multidimensional array
     * CATCH and save the error when failed
     *
     * @param string $query          SQL Query
     * @param array  $variables      Array of the variables to be passed (optional)
     *
     * @return array (multidimensional)
     *
     */

    public function all($query, $variables = array()) : array
    {

        try {
            $run = $this->db_pdo->prepare($query);
            $run->execute($variables);
            return $run->fetchAll(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            $this->db_debug = 'db::all() failed: '.$e->getMessage();
            $this->db_debug .= '<br>'.$query;
        }
    }

    /**
     *
     * Function to return one (first) row from the database
     * TRY to run the SQL query
     * Return all of the results (if succeeded) in a multidimensional array
     * CATCH and save the error when failed
     *
     * @param string $query          SQL query
     * @param array  $variables      Array of the variables to be passed (optional)
     *
     * @return array
     *
     */

    public function row($query, $variables = array()) : array
    {

        try {
            $run = $this->db_pdo->prepare($query);
            $run->execute($variables);
            return $run->fetch(PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            $this->db_debug = 'db::row() failed: '.$e->getMessage();
            $this->db_debug .= '<br>'.$query;
        }
    }

    /**
     *
     * Function to return one value from the database
     * TRY to run the SQL query
     * Return all of the results (if succeeded) in a multidimensional array
     * CATCH and save the error when failed
     *
     * @param string $query          SQL query
     * @param array  $variables      Array of the variables to be passed (optional)
     *
     * @return string
     *
     */

    public function one($query, $variables = array()) : string
    {

        try {
            $run = $this->db_pdo->prepare($query);
            $run->execute($variables);
            return $run->fetchColumn();

        } catch(PDOException $e) {
            $this->db_debug = 'db::one() failed: '.$e->getMessage();
            $this->db_debug .= '<br>'.$query;
        }
    }

    /**
     *
     * Function returns no values from the database
     * TRY to run the SQL query
     * Return all of the results (if succeeded) in a multidimensional array
     * CATCH and save the error when failed
     *
     * @param string $query          SQL query
     * @param array  $variables      Array of the variables to be passed (optional)
     *
     * @return string
     *
     */

    public function none($query, $variables = array()) : string
    {

        try {
            $run = $this->db_pdo->prepare($query);
            return $run->execute($variables);

        } catch(PDOException $e) {
            $this->db_debug = 'db::none() failed: '.$e->getMessage();
            $this->db_debug .= '<br>'.$query;
        }
    }

    /**
     *
     * Function returns the last added id
     * TRY to run the SQL query
     * CATCH and save the error when failed
     *
     * @return string
     *
     */

    public function last() : string
    {

        try {
            return $this->db_pdo->lastInsertId();

        } catch(PDOException $e) {
            $this->db_debug = 'db::last() failed: '.$e->getMessage();
            $this->db_debug .= '<br>'.$query;
        }
    }
}

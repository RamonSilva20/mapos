<?php

class Seeder
{
    private $CI;

    protected $db;

    protected $dbforge;

    /** @var \Faker\Generator */
    protected $faker;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->dbforge();
        $this->db = $this->CI->db;
        $this->dbforge = $this->CI->dbforge;
    }

    /**
     * Run another seeder
     *
     * @param string $seeder Seeder classname
     */
    public function call($seeder)
    {
        $file = APPPATH . 'database/seeds/' . $seeder . '.php';
        require_once $file;
        $obj = new $seeder;
        $obj->run();
    }

    public static function create()
    {
        return new Seeder();
    }

    public function __get($property)
    {
        return $this->CI->$property;
    }
}

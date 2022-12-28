<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * CI Migrations Generator Library
 *
 * Create a base file for migrations to start off with;
 *
 * @author Fastworkx S.R.L. <development@fastworkx.com>
 * @license Free to use and abuse
 * @version 0.1.0 Beta
 *
 */
class Sqltoci
{
    public $db_user;
    public $db_pass;
    public $db_host;
    public $db_name;
    public $email;
    public $tables = '*';
    public $newline = '\n';
    public $write_file = true;
    public $file_name = '';
    public $file_per_table = true;
    public $path = 'migrations';
    public $skip_tables = [];
    public $add_view = false;

    /*
     * defaults;
     */

    public function __construct($params = null)
    {
        // parent::__construct();
        isset($this->ci) or $this->ci = get_instance();
        $this->ci->db_master = $this->ci->db;
        $this->db_user = $this->ci->db_master->username;
        $this->db_pass = $this->ci->db_master->password;
        $this->db_host = $this->ci->db_master->hostname;
        $this->db_name = $this->ci->db_master->database;
        $this->path = APPPATH . $this->path;
        if ($params) {
            $this->init_config($params);
        }
    }

    /**
     * Init Config if there is any passed
     *
     *
     * @param type $params
     */
    public function init_config($params = [])
    { //apply config
        if (count($params) > 0) {
            foreach ($params as $key => $val) {
                if (isset($this->$key)) {
                    $this->$key = $val;
                }
            }
        }
    }

    /**
     * Generate the file.
     *
     * @param string $tables
     * @return boolean|string
     */
    public function generate($tables = null)
    {
        if ($tables) {
            $this->tables = $tables;
        }

        $return = '';
        /* open file */
        if ($this->write_file) {
            if (!is_dir($this->path) or !is_really_writable($this->path)) {
                $msg = "Unable to write migration file: " . $this->path;
                log_message('error', $msg);
                echo $msg;
                return;
            }

            if (!$this->file_per_table) {
                $file_path = $this->path . '/' . $this->file_name . '.sql';
                $file = fopen($file_path, 'w+');

                if (!$file) {
                    $msg = "no file";
                    log_message('error', $msg);
                    echo $msg;
                    return false;
                }
            }
        }


        // if default, then run all tables, otherwise just do the list provided
        if ($this->tables == '*') {
            $query = $this->ci->db_master->query('SHOW full TABLES FROM ' . $this->ci->db_master->protect_identifiers($this->ci->db_master->database));

            $retval = [];


            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    $tablename = 'Tables_in_' . $this->ci->db_master->database;

                    if (isset($row[$tablename])) {
                        /* check if table in skip arrays, if so, go next */
                        if (in_array($row[$tablename], $this->skip_tables)) {
                            continue;
                        }

                        /* check if views to be migrated */
                        if ($this->add_view) {
                            ## not implemented ##
                            //$retval[] = $row[$tablename];
                        } else {
                            /* skip views */
                            if (strtolower($row['Table_type']) == 'view') {
                                continue;
                            }
                            $retval[] = $row[$tablename];
                        }
                    }
                }
            }

            $this->tables = [];
            $this->tables = $retval;
        } else {
            $this->tables = is_array($tables) ? $tables : explode(',', $tables);
        }

        ## if write file, check if we can
        if ($this->write_file) {
            /* make subdir */
            $path = $this->path . '/' . $this->file_name;

            if (!@is_dir($path)) {
                if (!@mkdir($path, DIR_WRITE_MODE, true)) {
                    return false;
                }

                @chmod($path, DIR_WRITE_MODE);
            }

            if (!is_dir($path) or !is_really_writable($path)) {
                $msg = "Unable to write backup per table file: " . $path;
                log_message('error', $msg);

                return;
            }

            //$file_path = $path . '/001_create_' . $table . '.php';
            $file_path = $path . '/001_create_base.php';
            $file = fopen($file_path, 'w+');

            if (!$file) {
                $msg = 'No File';
                log_message('error', $msg);
                echo $msg;

                return false;
            }
        }


        $up = '';
        $down = '';
        //loop through tables
        foreach ($this->tables as $table) {
            log_message('debug', print_r($table, true));

            $q = $this->ci->db_master->query('describe ' . $this->ci->db_master->protect_identifiers($this->ci->db_master->database . '.' . $table));
            // No result means the table name was invalid
            if ($q === false) {
                continue;
            }

            $columns = $q->result_array();

            $q = $this->ci->db_master->query(' SHOW TABLE STATUS WHERE Name = \'' . $table . '\'');
            $engines = $q->row_array();

            $key = '';
            $up .= PHP_EOL."\n\t\t" . '## Create Table ' . $table . "\n";
            $up .= "\t\t" . '$this->dbforge->add_field(array(';

            foreach ($columns as $column) {
                $column_type = '';
                $column_constraint = '';
                $column_null = ($column['Null'] == 'NO' ? 'FALSE' : 'TRUE');
                $column_default = $column['Default'];
                $column_unsigned = false;
                $unsigned = 'unsigned';
                
                //si tiene constraint
                if (strpos($column['Type'], '(')) {
                    //verificar si tiene 'unsigned'
                    if (strpos($column['Type'], $unsigned)) {
                        $column_unsigned = $unsigned;
                        $column['Type'] = substr($column['Type'], 0, strpos($column['Type'], ')'));
                    }

                    $column_type = strtoupper(substr($column['Type'], 0, strpos($column['Type'], '(')));

                    //si tiene un valores enum o set
                    if ($column_type == 'ENUM' || $column_type == 'SET') {
                        //reemplazamos comilla simple por doole
                        $column['Type'] = str_replace('\'', '"', substr($column['Type'], strpos($column['Type'], '(')));
                        
                        //concadenamos
                        $column_type = $column_type.$column['Type'];
                        $column_constraint = false;
                    } else {
                        $column['Type'] = substr($column['Type'], strpos($column['Type'], '(') + 1);
                        $column_constraint = substr($column['Type'], 0, -1);
                    }
                } else {
                    $column_type = strtoupper($column['Type']);
                    $column_constraint = false;
                }
                
                //si tiene DEAFAULT generar  sql texto plano para escapar el strin en caso e.g. CURRENT_TIMESTAMP
                if ($column_default == 'CURRENT_TIMESTAMP') {
                    $up .= PHP_EOL."\t\t\t"."'`$column[Field]` $column[Type] " . ($column['Null'] == 'NO' ? 'NOT NULL' : 'NULL') .
                    (
                        #  if its timestamp column, don't '' around default value .... crap way, but should work for now
                        $column['Default'] ? ' DEFAULT ' . ($column['Type'] == 'timestamp' ? $column['Default'] : '' . $column['Default'] . '') : ''
                    )
                    . " $column[Extra]',";
                } else {
                    $up .= PHP_EOL.
                    "\t\t\t".'\'' . $column['Field'] . '\' => array('.PHP_EOL.
                    "\t\t\t\t".'\'type\' => \'' . $column_type . '\','.PHP_EOL.
                    (
                        $column_constraint ?
                        "\t\t\t\t".'\'constraint\' => ' . $column_constraint . ','.PHP_EOL :
                        ''
                    ).
                    (
                        $column_unsigned ?
                        "\t\t\t\t".'\''.$unsigned.'\' => TRUE,'.PHP_EOL :
                        ''
                    ).
                    "\t\t\t\t".'\'null\' => ' . $column_null . ','.PHP_EOL.
                    (
                        $column_default != null ?
                        "\t\t\t\t".'\'default\' => \'' . $column_default . '\','.PHP_EOL :
                        ''
                    ).
                    (
                        $column['Extra'] ?
                    "\t\t\t\t".'\''.$column['Extra'].'\' => TRUE'.PHP_EOL :
                    ''.PHP_EOL
                    ).
                    "\t\t\t".'),';
                }

                if ($column['Key'] == 'PRI') {
                    $key = "\t\t" . '$this->dbforge->add_key("' . $column['Field'] . '",true);';
                }
            }

            $up .= PHP_EOL."\t\t));". PHP_EOL.$key.PHP_EOL."\t\t" . '$this->dbforge->create_table("' . $table . '", TRUE);' . PHP_EOL;

            if (isset($engines['Engine']) and $engines['Engine']) {
                $up .= "\t\t" . '$this->db->query(\'ALTER TABLE  ' . $this->ci->db_master->protect_identifiers($table) .
                        ' ENGINE = ' . $engines['Engine']. '\');';
            }


            $down .= "\t\t" . '### Drop table ' . $table . ' ##' . "\n";
            $down .= "\t\t" . '$this->dbforge->drop_table("' . $table . '", TRUE);' . "\n";

            /* clear some mem */
            $q->free_result();
        }

        ### generate the text ##
        $return .= '<?php ';
        $return .= 'defined(\'BASEPATH\') OR exit(\'No direct script access allowed\');' . "\n\n";
        $return .= 'class Migration_create_base extends CI_Migration {' . "\n";
        $return .= "\n\t" . 'public function up() {';

        $return .= $up;
        $return .= "\n\t" . ' }' . "\n";

        $return .= "\n\t" . 'public function down()';
        $return .= "\t" . '{' . "\n";
        $return .= $down . "\n";
        $return .= "\t" . '}' . "\n" . '}';

        ## write the file, or simply return if write_file false
        if ($this->write_file) {
            fwrite($file, $return);
            fclose($file);
            echo "Create file migration with success!";
            return true;
        } else {
            return $return;
        }
    }
}

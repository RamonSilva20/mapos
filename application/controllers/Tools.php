<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Tools extends CI_Controller
{
    /** @var \Faker\Generator */
    public $faker;

    /** @var Seeder */
    public $seeder;

    public function __construct()
    {
        parent::__construct();

        // can only be called from the command line
        if (!$this->input->is_cli_request()) {
            exit('Direct access is not allowed. This is a command line tool, use the terminal');
        }

        $this->load->dbforge();

        $this->load->library('Seeder');

        // initiate faker
        $this->faker = Faker\Factory::create();

        // initiate seeder
        $this->seeder = Seeder::create();
    }

    public function index()
    {
        $this->help();
    }

    public function message($to = 'World')
    {
        echo "Hello {$to}!" . PHP_EOL;
    }

    public function help()
    {
        $result = "The following are the available command line interface commands\n\n";
        $result .= "php index.php tools migration \"file_name\"         Create new migration file\n";
        $result .= "php index.php tools migrate [\"version_number\"]    Run all migrations. The version number is optional.\n";
        $result .= "php index.php tools seeder \"file_name\"            Creates a new seed file.\n";
        $result .= "php index.php tools seed \"file_name\"              Run the specified seed file.\n";

        echo $result . PHP_EOL;
    }

    public function migration($name)
    {
        $this->make_migration_file($name);
    }

    public function migrate($version = null)
    {
        $this->load->library('migration');

        if ($version != null) {
            if ($this->migration->version($version) === false) {
                show_error($this->migration->error_string());
            } else {
                echo "Migrations run successfully" . PHP_EOL;
            }

            return;
        }

        if ($this->migration->latest() === false) {
            show_error($this->migration->error_string());
        } else {
            echo "Migrations run successfully" . PHP_EOL;
        }
    }

    public function seeder($name)
    {
        $this->make_seed_file($name);
    }

    public function seed($name = null)
    {
        if ($name) {
            $this->seeder->call($name);

            echo "Seeds run successfully" . PHP_EOL;

            return;
        }


        $seeds = [
            'Permissoes',
            'Usuarios',
            'Configuracoes',
        ];

        foreach ($seeds as $seed) {
            $this->seeder->call($seed);
        }

        echo "Seeds run successfully" . PHP_EOL;
    }

    protected function make_migration_file($name)
    {
        $date = new DateTime();
        $timestamp = $date->format('YmdHis');

        $path = APPPATH . "database/migrations/$timestamp" . "_" . "$name.php";

        $my_migration = fopen($path, "w") or die("Unable to create migration file!");

        $migration_stub_path = APPPATH . "database/stubs/migration.stub";

        $migration_stub = file_get_contents($migration_stub_path) or die("Unable to open migration stub!");

        $migration_stub = preg_replace("/{name}/", $name, $migration_stub);

        fwrite($my_migration, $migration_stub);

        fclose($my_migration);

        echo "$path migration has successfully been created." . PHP_EOL;
    }

    protected function make_seed_file($name)
    {
        $className = ucfirst($name);

        $path = APPPATH . "database/seeds/$className.php";

        $my_seed = fopen($path, "w") or die("Unable to create seed file!");

        $seed_stub_path = APPPATH . "database/stubs/seed.stub";

        $seed_stub = file_get_contents($seed_stub_path) or die("Unable to open seed stub!");

        $seed_stub = preg_replace("/{name}/", $className, $seed_stub);

        fwrite($my_seed, $seed_stub);

        fclose($my_seed);

        echo "$path seeder has successfully been created." . PHP_EOL;
    }
}

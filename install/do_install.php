<?php

ini_set('max_execution_time', 300); //300 seconds

$settings_file = __DIR__ . DIRECTORY_SEPARATOR . 'settings.json';

if (! file_exists($settings_file)) {
    exit('Arquivo de configuração não encontrado!');
} else {
    $contents = file_get_contents($settings_file);
    $settings = json_decode($contents, true);
}

if (! empty($_POST)) {
    $host = $_POST['host'];
    $dbuser = $_POST['dbuser'];
    $dbpassword = $_POST['dbpassword'];
    $dbname = $_POST['dbname'];

    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $login_password = $_POST['password'] ? $_POST['password'] : '';
    $base_url = $_POST['base_url'];

    //check required fields
    if (! ($host && $dbuser && $dbname && $full_name && $email && $login_password && $base_url)) {
        echo json_encode(['success' => false, 'message' => 'Por favor insira todos os campos.']);
        exit();
    }

    //check for valid email
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        echo json_encode(['success' => false, 'message' => 'Por favor insira um email válido.']);
        exit();
    }

    //check for valid database connection
    try {
        $mysqli = @new mysqli($host, $dbuser, $dbpassword, $dbname);

        if (mysqli_connect_errno()) {
            echo json_encode(['success' => false, 'message' => $mysqli->connect_error]);
            exit();
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }

    //all input seems to be ok. check required fiels
    if (! is_file($settings['database_file'])) {
        echo json_encode(['success' => false, 'message' => 'O arquivo ../banco.sql não foi encontrado na pasta de instalação!']);
        exit();
    }

    /*
     * check the db config file
     * if db already configured, we'll assume that the installation has completed
     */
    $is_installed = file_exists('..' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . '.env');

    if ($is_installed) {
        echo json_encode(['success' => false, 'message' => 'Parece que este aplicativo já está instalado! Você não pode reinstalá-lo novamente.']);
        exit();
    }

    //start installation
    $sql = file_get_contents($settings['database_file']);

    //set admin information to database
    $now = date('Y-m-d H:i:s');
    $sql = str_replace('admin_name', $full_name, $sql);
    $sql = str_replace('admin_email', $email, $sql);
    $sql = str_replace('admin_password', password_hash($login_password, PASSWORD_DEFAULT), $sql);
    $sql = str_replace('admin_created_at', $now, $sql);

    //create tables in datbase
    $mysqli->multi_query($sql);
    do {
    } while (mysqli_more_results($mysqli) && mysqli_next_result($mysqli));
    $mysqli->close();
    // database created

    $env_file_path = '..' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . '.env.example';
    $env_file = file_get_contents($env_file_path);

    // set the database config file
    $env_file = str_replace('enter_db_hostname', $host, $env_file);
    $env_file = str_replace('enter_db_username', $dbuser, $env_file);
    $env_file = str_replace('enter_db_password', $dbpassword, $env_file);
    $env_file = str_replace('enter_db_name', $dbname, $env_file);

    // set random enter_encryption_key
    $encryption_key = substr(md5(rand()), 0, 15);
    $env_file = str_replace('enter_encryption_key', $encryption_key, $env_file);
    $env_file = str_replace('enter_baseurl', $base_url, $env_file);

    // set random enter_jwt_key
    $env_file = str_replace('enter_jwt_key', base64_encode(openssl_random_pseudo_bytes(32)), $env_file);
    $env_file = str_replace('enter_token_expire_time', $_POST['enter_token_expire_time'], $env_file);
    $env_file = str_replace('enter_api_enabled', (string) $_POST['enter_api_enabled'], $env_file);

    // set the environment = production
    $env_file = str_replace('pre_installation', 'production', $env_file);

    if (file_put_contents('..' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . '.env', $env_file)) {
        echo json_encode(['success' => true, 'message' => 'Instalação bem sucedida.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao criar arquivo env.']);
    }

    exit();
}

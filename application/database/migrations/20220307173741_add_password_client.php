<?php

class Migration_add_password_client extends CI_Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE `clientes` ADD `senha` VARCHAR(200) NOT NULL;");
        $this->db->query("CREATE TABLE `resets_de_senha` ( 
                `id` INT NOT NULL AUTO_INCREMENT,
                `email` VARCHAR(200) NOT NULL , 
                `token` VARCHAR(255) NOT NULL , 
                `data_expiracao` DATETIME NOT NULL, 
                `token_utilizado` TINYINT NOT NULL,
                PRIMARY KEY (`id`))
              ENGINE = InnoDB
              DEFAULT CHARACTER SET = latin1;
        ");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `clientes` DROP `senha`;");
        $this->db->query("DROP TABLE `resets_de_senha`;");
    }
}

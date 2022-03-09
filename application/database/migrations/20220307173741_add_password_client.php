<?php

class Migration_add_password_client extends CI_Migration
{
    public function up()
    {
        $sql = "
            ALTER TABLE `clientes` ADD `senha` VARCHAR(200) NOT NULL ;
            CREATE TABLE `resets_de_senha` ( `id` INT(11) NOT NULL AUTO_INCREMENT = 1, add PRIMARY KEY (`id`)  , `email` VARCHAR(200) NOT NULL , `token` VARCHAR(255) NOT NULL , `data_expiracao` DATETIME NOT NULL, `token_utilizado` TINYINT NOT NULL ) ENGINE = InnoDB; 
        ";
        $this->db->query($sql);
    }

    public function down()
    {
        $sql = "
            ALTER TABLE `clientes` DROP COLUMN `senha`;
            DROP TABLE IF EXISTS `resets_de_senha`;
        ";
        $this->db->query($sql);
    }
}

<?php

class Migration_upload_image_user extends CI_Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE `usuarios` ADD `url_image_user` varchar(255) NULL");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `usuarios` DROP `url_image_user`;");
    }
}

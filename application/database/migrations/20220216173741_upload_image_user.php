<?php

class Migration_upload_image_user extends CI_Migration
{
    public function up()
    {
        $sql = "
            ALTER TABLE usuarios ADD url_image_user varchar(255) NULL;
        ";
        $this->db->query($sql);
    }

    public function down()
    {
        $sql = "
            ALTER TABLE usuarios DROP COLUMN url_image_user;
        ";
        $this->db->query($sql);
    }
}

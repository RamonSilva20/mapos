<?php

class Migration_Alter_Datetime_Os extends CI_Migration
{
    public function up()
    {
        $sql = "ALTER TABLE os 
                MODIFY COLUMN dataInicial DATETIME,
                MODIFY COLUMN dataFinal DATETIME;";
                
        $this->db->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE os 
                MODIFY COLUMN dataInicial DATE,
                MODIFY COLUMN dataFinal DATE;";
                
        $this->db->query($sql);
    }
} 
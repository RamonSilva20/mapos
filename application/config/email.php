<?php

$config['protocol']         = 'smtp';
$config['smtp_host']        = 'smtp.gmail.com';
$config['smtp_crypto']      = 'tls'; // tls or ssl
$config['smtp_port']        = 587;
$config['smtp_user']        = 'seuemail@gmail.com';
$config['smtp_pass']        = 'senhadoemail';
$config['validate']         = true; // validar email
$config['mailtype']         = 'html'; // text ou html
$config['charset']          = 'utf-8';
$config['newline']          = "\r\n";
$config['bcc_batch_mode']   = false;
$config['wordwrap']         = false;
$config['priority']         = 3; // 1, 2, 3, 4, 5 | Email Priority. 1 = highest. 5 = lowest. 3 = normal.

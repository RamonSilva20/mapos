<?php

$config['protocol'] = $_ENV['EMAIL_PROTOCOL'] ?? 'smtp';
$config['smtp_host'] = $_ENV['EMAIL_SMTP_HOST'] ?? 'smtp.gmail.com';
$config['smtp_crypto'] = $_ENV['EMAIL_SMTP_CRYPTO'] ?? 'tls'; // tls or ssl
$config['smtp_port'] = $_ENV['EMAIL_SMTP_PORT'] ?? 587;
$config['smtp_user'] = $_ENV['EMAIL_SMTP_USER'] ?? 'seuemail@gmail.com';
$config['smtp_pass'] = $_ENV['EMAIL_SMTP_PASS'] ?? 'senhadoemail';
$config['validate'] = isset($_ENV['EMAIL_VALIDATE']) ? filter_var($_ENV['EMAIL_VALIDATE'], FILTER_VALIDATE_BOOLEAN) : true; // validar email
$config['mailtype'] = $_ENV['EMAIL_MAILTYPE'] ?? 'html'; // text ou html
$config['charset'] = $_ENV['EMAIL_CHARSET'] ?? 'utf-8';
$config['newline'] = $_ENV['EMAIL_NEWLINE'] ?? "\r\n";
$config['bcc_batch_mode'] = isset($_ENV['EMAIL_BCC_BATCH_MODE']) ? filter_var($_ENV['EMAIL_BCC_BATCH_MODE'], FILTER_VALIDATE_BOOLEAN) : false;
$config['wordwrap'] = isset($_ENV['EMAIL_WORDWRAP']) ? filter_var($_ENV['EMAIL_WORDWRAP'], FILTER_VALIDATE_BOOLEAN) : false;
$config['priority'] = $_ENV['EMAIL_PRIORITY'] ?? 3; // 1, 2, 3, 4, 5 | Email Priority. 1 = highest. 5 = lowest. 3 = normal.

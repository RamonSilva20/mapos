<head>
  <title><?php echo lang('login_heading') ?></title>
  <style>
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }
  body {
    background-color: rgb(20, 20, 20);
    color: rgb(150, 150, 150);
    font-family: sans-serif;
    line-height: 1.4;
  }
  .flexbox {
    align-content: center;
    display: flex;
    flex-flow: column wrap;
    justify-content: center;
  }
  form {
    background-color: rgb(25, 25, 25);
    border-radius: 02px;
    height: 60%;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    margin: auto;
    padding: 20px;
    width: 300px;
  }
  form p {
    margin-top: 10px;
  }
  input {
    background-color: rgb(30, 30, 30);
    border: 0;
    border-radius: 02px;
    color: inherit;
    font-style: italic;
    padding: 06px;
    width: 100%;
  }
  input[type='submit'] {
    background-color: rgb(20, 255, 20);
    color: white;
    padding: 10px;
  }
  input[type='checkbox'] {
    background: red;
  }
  .Message {
    height: auto;
    position: absolute;
    left: 0;
    right: 0;
    margin: auto;
    padding: 10px;
    width: 100%;
  }
  .Message p {
    display: inline;
    background-color: rgb(225, 20, 20);
    color: white; 
    border-radius: 02px;
    margin-top: 02px;
    padding: 10px;
  }
  .reset-login {
    bottom: 10%;
  }
  </style>
</head>

<div class="flexbox message" style="top:20px"><?php echo $message;?></div>
<?php echo form_open("auth/login");?>
<form class="flexbox">
  <h1><?php echo lang('login_heading');?></h1>
  <p><?php echo lang('login_subheading');?></p>

  <p>
    <?php echo lang('login_identity_label', 'identity');?>
    <?php echo form_input($identity);?>
  </p>

  <p>
    <?php echo lang('login_password_label', 'password');?>
    <?php echo form_input($password);?>
  </p>

  <p>
    <?php echo lang('login_remember_label', 'remember');?>
    <?php echo form_checkbox('remember', '1', FALSE, 'id="remember"');?>
  </p>


  <p><?php echo form_submit('submit', lang('login_submit_btn'));?></p>

  <?php echo form_close();?>

  <div class="flexbox message reset-login">
    <p style="background: rgb(30, 30, 30)">
      <a href="forgot_password"><?php echo lang('login_forgot_password');?></a>
    </p>  
  </div>
</form>
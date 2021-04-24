<form name="config-form" id="config-form" action="do_install.php" method="post">

  <div class="section clearfix">
    <p>1. Por favor, insira as informações da sua conexão de <strong>banco de dados</strong>.</p>
    <hr />
    <div>
      <div class="form-group clearfix">
        <label for="host" class=" col-md-3">Host</label>
        <div class="col-md-9">
          <input type="text" value="" id="host" name="host" class="form-control" placeholder="Host de Banco de Dados (geralmente localhost)" />
        </div>
      </div>
      <div class="form-group clearfix">
        <label for="dbuser" class=" col-md-3">Usuário</label>
        <div class=" col-md-9">
          <input type="text" value="" id="dbuser" name="dbuser" class="form-control" autocomplete="off"
          placeholder="Nome de usuário do banco de dados" />
        </div>
      </div>
      <div class="form-group clearfix">
        <label for="dbpassword" class=" col-md-3">Senha</label>
        <div class=" col-md-9">
          <input type="password" value="" id="dbpassword" name="dbpassword" class="form-control"
          autocomplete="off" placeholder="Senha do usuário do banco de dados" />
        </div>
      </div>
      <div class="form-group clearfix">
        <label for="dbname" class=" col-md-3">Banco de Dados</label>
        <div class=" col-md-9">
          <input type="text" value="" id="dbname" name="dbname" class="form-control" placeholder="Nome do banco de dados" />
        </div>
      </div>
    </div>
  </div>

  <div class="section clearfix">
    <p>2. Por favor, insira as informações para sua conta de <strong>administrador</strong>.</p>
    <hr />
    <div>
      <div class="form-group clearfix">
        <label for="full_name" class=" col-md-3">Nome</label>
        <div class="col-md-9">
          <input type="text" value="" id="full_name" name="full_name" class="form-control" placeholder="Nome completo" />
        </div>
      </div>
      <div class="form-group clearfix">
        <label for="email" class=" col-md-3">Email</label>
        <div class=" col-md-9">
          <input type="text" value="" id="email" name="email" class="form-control" placeholder="Seu email" />
        </div>
      </div>
      <div class="form-group clearfix">
        <label for="password" class=" col-md-3">Senha</label>
        <div class=" col-md-9">
          <input type="password" value="" id="password" name="password" class="form-control" placeholder="Senha de login" />
        </div>
      </div>
    </div>
  </div>

  <div class="section clearfix">
    <p>3. Por favor, insira a URL.</p>
    <hr />
    <div>
      <div class="form-group clearfix">
        <div class="form-group clearfix">
          <label for="base_url" class=" col-md-3">URL</label>
          <div class="col-md-9">
            <input type="text" value="" id="base_url" name="base_url" class="form-control"
            placeholder="URL do sistema" />
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="panel-footer">
    <button type="submit" class="btn btn-info form-next">
      <span class="loader hide"> Por favor, espere...</span>
      <span class="button-text"><i class='fa fa-chevron-right'></i> Instalar</span>
    </button>
  </div>

</form>

<div class="section">
  <p>1. Por favor, verifique se as configurações do seu <strong>PHP</strong> atende aos seguintes requisitos:</p>
  <hr />
  <div>
    <table>
      <thead>
        <tr>
          <th width="25%">Configurações</th>
          <th width="25%">Atuais</th>
          <th>Requeridas</th>
          <th width="15%" class="text-center">Status</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>PHP Version</td>
          <td>
            <?php echo $current_php_version; ?>
          </td>
          <td>
            <?php echo $php_version_required; ?> >=</td>
            <td class="text-center">
              <?php if ($php_version_success) { ?>
                <i class="status fa fa-check-circle-o"></i>
              <?php } else { ?>
                <i class="status fa fa-times-circle-o"></i>
              <?php } ?>
            </td>
          </tr>
          <tr>
            <td>allow_url_fopen</td>
            <td>
              <?php if ($allow_url_fopen_success) { ?>
                On
              <?php } else { ?>
                Off
              <?php } ?>
            </td>
            <td>On</td>
            <td class="text-center">
              <?php if ($allow_url_fopen_success) { ?>
                <i class="status fa fa-check-circle-o"></i>
              <?php } else { ?>
                <i class="status fa fa-times-circle-o"></i>
              <?php } ?>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="section">
    <p>2. Por favor, verifique se as extensões listadas abaixo estão <strong>instaladas/habilitadas</strong> no seu servidor:</p>
    <hr />
    <div>
      <table>
        <thead>
          <tr>
            <th width="25%">Extensões</th>
            <th width="25%">Atuais</th>
            <th>Requeridas</th>
            <th width="15%" class="text-center">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($settings["extensions"] as $value) { ?>
            <tr>
              <td>
                <?php echo($value); ?>
              </td>
              <td>
                <?php if (extension_loaded($value)) { ?>
                  On
                <?php } else { ?>
                  Off
                <?php } ?>
              </td>
              <td>On</td>
              <td class="text-center">
                <?php if (extension_loaded($value)) { ?>
                  <i class="status fa fa-check-circle-o"></i>
                <?php } else { ?>
                  <i class="status fa fa-times-circle-o"></i>
                <?php } ?>
              </td>
            </tr>
          <?php }; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="section">
    <p>3. Por favor, certifique-se de ter definido a permissão para <strong>leitura e escrita</strong> nos seguintes
    diretórios e arquivos:</p>
    <hr />
    <div>
      <table>
        <thead>
          <tr>
            <th>Arquivos</th>
            <th width="15%" class="text-center">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($settings["writeable_directories"] as $value) { ?>
            <tr>
              <td>
                <?php echo $value; ?>
              </td>
              <td class="text-center">
                <?php if (is_writeable(".." . $value)) { ?>
                  <i class="status fa fa-check-circle-o"></i>
                  <?php
                } else {
                    $all_requirement_success = false; ?>
                  <i class="status fa fa-times-circle-o"></i>
                <?php
                } ?>
              </td>
            </tr>
          <?php }; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="panel-footer">
    <button <?php if (!$all_requirement_success) {
        echo "disabled=disabled" ;
    } ?> class="btn btn-info
      form-next"><i class='fa fa-chevron-right'></i> Próximo</button>
    </div>

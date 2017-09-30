<div class="row-fluid" style="margin-top:0">
    <div class="col-md-12">
        <?php if ($custom_error != '') {
          echo '<div class="alert alert-danger">' . $custom_error . '</div>';
        } ?>
      <div class="panel panel-info">
        <div class="panel-heading"><h4>Crear proveedor</h4>
        </div>
        <div class="panel-body">
                <form action="<?php echo current_url(); ?>" id="formProveedor" method="post">
                  
                  <div class="form-row">

                    <div class="form-group col-md-8">
                      <label for="txtRazonSocial" class="col-form-label">Razón Social</label>
                      <input type="text" class="form-control" id="txtRazonSocial" name="txtRazonSocial" value="<?php echo set_value('txtRazonSocial'); ?>" required>
                    </div>

                    <div class="form-group col-md-4">
                      <label for="txtRUC" class="col-form-label">Cédula/R.U.C</label>
                      <input type="text" class="form-control" id="txtRUC" name="txtRUC" onkeypress="return numeros(event)" value="<?php echo set_value('txtRUC'); ?>" required>
                    </div>
                  </div>

                  <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="txtDireccion" class="col-form-label">Dirección</label>
                    <input type="text" class="form-control" id="txtDireccion" name="txtDireccion" placeholder="10 de Agosto - Quito" value="<?php echo set_value('txtDireccion'); ?>">
                  </div>   

                  <div class="form-group col-md-6">
                      <label for="txtEmail" class="col-form-label">Email</label>
                      <input type="email" class="form-control" id="txtEmail" name="txtEmail" placeholder="Email" value="<?php echo set_value('txtEmail'); ?>" required>
                    </div>
                  </div>
                  <div class="form-row">
                  <div class="form-group col-md-3">
                    <label for="txtTelefono" class="col-form-label">Telefono</label>
                    <input type="text" class="form-control" id="txtTelefono" name="txtTelefono" onkeypress="return numeros(event)" value="<?php echo set_value('txtTelefono'); ?>">
                  </div>

                  <div class="form-group col-md-3">
                    <label for="txtCelular" class="col-form-label">Celular</label>
                    <input type="text" class="form-control" id="txtCelular" name="txtCelular" onkeypress="return numeros(event)" value="<?php echo set_value('txtCelular'); ?>">
                  </div>

                  <div class="form-group col-md-6">
                    <label for="txtContacto" class="col-form-label">Contacto</label>
                    <input type="text" class="form-control" id="txtContacto" name="txtContacto" value="<?php echo set_value('txtContacto'); ?>">
                  </div>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-2">
                      <label for="txtCP" class="col-form-label">Codigo Postal</label>
                      <input type="text" class="form-control" id="txtCP" name="txtCP" value="<?php echo set_value('txtCP'); ?>">
                    </div>
                    <div class="form-group col-md-3">
                      <label for="txtSector" class="col-form-label">Sector</label>
                      <input type="text" class="form-control" id="txtSector" name="txtSector" value="<?php echo set_value('txtSector'); ?>">
                    </div>

                    <div class="form-group col-md-3">
                      <label for="txtCiudad" class="col-form-label">Ciudad</label>
                      <input type="text" class="form-control" id="txtCiudad" name="txtCiudad" value="<?php echo set_value('txtCiudad'); ?>">
                    </div>
                    <div class="form-group col-md-4">
                      <label for="txtPais" class="col-form-label">Pais</label>
                      <select id="txtPais" name="txtPais" class="form-control" value="<?php echo set_value('txtPais'); ?>">
                        <option value="">Seleccione</option>
                        <option value="Ecuador">Ecuador</option>
                      </select>
                    </div>
                  </div>
                   <div class="form-row">
                    <div class="form-group col-md-12">
                     <label for="txtNotas" class="col-form-label">Observaciones</label>
                      <textarea id="txtNotas" name="txtNotas" class="form-control" rows="5" cols="50" value="<?php echo set_value('txtNotas'); ?>"></textarea>
                   </div>
                   </div>

                  <div class="form-group col-md-12">
                    <button type="submit" class="btn btn-success"><i class="icon-plus icon-white"></i> Agregar</button>
                    <button type="reset" class="btn btn-danger">Cancelar</button>
                  </div>

                </form>
        </div>
        <div class="panel-footer">
            <?php
              $error = $this->session->flashdata('error');
                if ($error) 
                {
                   echo '<span'.$error.'</span>';
                }
            ?>
        Crear proveedor</div>
      </div>
    </div>
</div>


<script src="<?php echo site_url()?>js/jquery.validate.js"></script>
<script type="text/javascript">
      $(document).ready(function(){
           $('#formProveedor').validate({
            rules :{
                  txtRazonSocial:{ required: true},
                  txtRUC:{ required: true, maxlength: 13},
                  txtTelefono: { required: true},
                  txtEmail: {required: true, email: true}
                  
            },
            messages:{
                  txtRazonSocial :{ required: 'Campo Requerido.'},
                  txtRUC :{ required: 'Campo Requerido.'},
                  txtTelefono :{ required: 'Campo Requerido.'},
                  txtEmail: { required: 'Campo Requerido.'}
                  

            },

            errorClass: "help-inline",
            errorElement: "span",
            highlight:function(element, errorClass, validClass) {
                $(element).parents('.control-group').addClass('error');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents('.control-group').removeClass('error');
                $(element).parents('.control-group').addClass('success');
            }
           });
      });
</script>
<script type="text/javascript">
	function numeros(e){
    key = e.keyCode || e.which;
    tecla = String.fromCharCode(key).toLowerCase();
    letras = " +0123456789";
    especiales = [8,37,39,46];
 
    tecla_especial = false
    for(var i in especiales){
 if(key == especiales[i]){
     tecla_especial = true;
     break;
        } 
    }
 
    if(letras.indexOf(tecla)==-1 && !tecla_especial)
        return false;
}
</script>

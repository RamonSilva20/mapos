
<section class="panel panel-default">
    <header class="panel-heading">
        <div class="row">
            <div class="col-md-8 col-xs-3"> 
                <?= anchor(site_url('brands/create'),'<i class="fa fa-plus"></i> '.$this->lang->line('app_create'), 'class="btn btn-success"'); ?>    
                <button class="btn btn-info" id="reload"><i class="fa fa-refresh"></i> <?= $this->lang->line('app_reload') ?></button>
            </div>
        </div>
    </header>
    
    <div class="panel-body">

        <hr>
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-lg-12">

              <form id="form_delete" method="post" >
                <table id="table" class="table table-bordered" style="margin-bottom: 10px">
                    
                    <thead>  
                        <tr>  
                           <th><input type="checkbox" id="remove-all"> &nbsp &nbsp &nbsp <button class="btn btn-danger btn-sm hide" id="delete" title="<?= $this->lang->line('app_delete') ?>"><i class="fa fa-trash"></i></button></th>
                           <th>#</th>
		                        <th><?= $this->lang->line('brand_name');  ?></th>
		                        <th><?= $this->lang->line('app_active');  ?></th>
		                        <th><?= $this->lang->line('app_created');  ?></th>
		                        <th><?= $this->lang->line('app_updated');  ?></th>
		                        <th><?= $this->lang->line('app_actions') ?></th>  
                        </tr>  
                    </thead>  

                </table>
              </form>
              
            </div>
        </div>
    </div>
</section>


 <script type="text/javascript">  
 $(document).ready(function(){  
      
      var datatable = $('#table').DataTable({  
           "processing":true,  
           "serverSide":true,  
           "order":[],  
           "ajax":{  
                url:"<?= site_url('brands/datatable'); ?>",  
                type:"POST"  
           },  
           "columnDefs":[  
                {  
                     "targets":[0, 3,6],  
                     "orderable":false,  
                },  
           ],
           "language": {
                "search": "<?= $this->lang->line('app_search'); ?>",
                "lengthMenu": "<?= $this->lang->line('app_per_page'); ?>",
                "zeroRecords": "<?= $this->lang->line('app_zero_records'); ?>",
                "info": "<?= $this->lang->line('app_showing'); ?>",
                "infoEmpty": "<?= $this->lang->line('app_empty'); ?>",
                "infoFiltered": "<?= $this->lang->line('app_filtered'); ?>",
                "oPaginate": {
                    "sNext": "<?= $this->lang->line('app_next'); ?>",
                    "sPrevious": "<?= $this->lang->line('app_previous'); ?>",
                    "sFirst": "<?= $this->lang->line('app_first'); ?>",
                    "sLast": "<?= $this->lang->line('app_last'); ?>"
                },
                "sLoadingRecords": "<?= $this->lang->line('app_loading'); ?>",
                "sProcessing": "<?= $this->lang->line('app_processing'); ?>",
            }  
      }); 

      // check if delete button must appear
      function check_delete_button() {
        $('table').find('.remove').each(function(index, val) {

           if($(val)[0].checked){
              $('#delete').removeClass('hide');
              return false;
           }
           $('#delete').addClass('hide');
        });
      }


      // mark all checkboxes
      $(document).on('click', '#remove-all', function () {
         
          var checkbox = $(this);
          if(checkbox[0].checked){

            $('table').find('.remove').each(function(index, val) {
               $(val).prop('checked', true);
               $(val).closest('tr').addClass('danger');
               $('#delete').removeClass('hide');
            });

          }else{

            $('table').find('.remove').each(function(index, val) {
               $(val).prop('checked', false);
               $(val).closest('tr').removeClass('danger');
               $('#delete').addClass('hide');

            });
          }
      }); 

      // reload datatable
      $(document).on('click', '#reload', function () {

        $('#delete').addClass('hide');
        datatable.ajax.reload();

        $.notify({ message: '<?= $this->lang->line('app_list_updated'); ?>' },{ type: 'info' });

      });

      // check item and highlight row
      $(document).on('click', '.remove', function () {
         
          var checkbox = $(this);
          if(checkbox[0].checked){
            checkbox.closest('tr').addClass('danger');
          }else{
            checkbox.closest('tr').removeClass('danger');
          }

          check_delete_button();

      }); 

      // delete many items form
      $('#form_delete').submit(function(event){
        event.preventDefault();
        data = $(this).serialize();

        $.ajax({
          url: '<?= site_url('brands/delete_many'); ?>',
          type: 'POST',
          dataType: 'json',
          data: data,
        })
        .done(function(response) {
          
          if(response.result == true){
             $('#reload').trigger('click');
             $.notify({ message: response.message },{ type: 'success' });
          }else{
             $.notify({ message: response.message },{ type: 'danger' });
          }
        })
        .fail(function(XMLHttpRequest, textStatus, errorThrown) {
           $.notify({ message: '<?= $this->lang->line('app_error'); ?>' },{ type: 'danger' });
        });
      });


      // remove single item
      $(document).on('click', '.delete', function (event) {

         event.preventDefault();
         var url = $(this).attr('href');
         
         var ask = confirm('<?= $this->lang->line('app_sure_delete'); ?>');

         if(ask){

           $.ajax({
             url: url+'?ajax=true',
             type: 'GET',
             dataType: 'json',
           })
           .done(function(response) {
              if(response.result == true){
                 $('#reload').trigger('click');
                 $.notify({ message: response.message },{ type: 'success' });
              }else{
                 $.notify({ message: response.message },{ type: 'danger' });
              }
           })
           .fail(function() {
              $.notify({ message: '<?= $this->lang->line('app_error'); ?>' },{ type: 'danger' });
           });
           
         }

      }); 


 });  
 </script> 
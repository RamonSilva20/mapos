
<section class="panel panel-default">
    <header class="panel-heading">
        <div class="row">
            <div class="col-md-8 col-xs-3"> 
                <?php echo anchor(site_url('Services/create'),'<i class="fa fa-plus"></i> '.$this->lang->line('app_create'), 'class="btn btn-success"'); ?>    
                <button class="btn btn-info" id="reload"><i class="fa fa-refresh"></i> <?php echo $this->lang->line('app_reload') ?></button>
            </div>
        </div>
    </header>
    
    <div class="panel-body">

        <hr>
        <div class="row">
            <div class="col-md-12">

              <form id="form_delete" method="post" >
                <table id="table" class="table table-bordered" style="margin-bottom: 10px">
                    
                    <thead>  
                        <tr>  
                           <th><input type="checkbox" id="remove-all"> &nbsp &nbsp &nbsp <button class="btn btn-danger btn-sm hide" id="delete" title="<?php echo $this->lang->line('app_delete') ?>"><i class="fa fa-trash"></i></button></th>
                           <th>#</th>
		                        <th>Service Name</th>
		                        <th>Price</th>
		                        <th>Active</th>
		                        <th>Created At</th>
		                        <th>Updated At</th>
		                        <th><?php echo $this->lang->line('app_actions') ?></th>  
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
                url:"<?php echo site_url('services/datatable'); ?>",  
                type:"POST"  
           },  
           "columnDefs":[  
                {  
                     "targets":[0, 2, 3],  
                     "orderable":false,  
                },  
           ],
           "language": {
                "search": "<?php echo $this->lang->line('app_search'); ?>",
                "lengthMenu": "<?php echo $this->lang->line('app_per_page'); ?>",
                "zeroRecords": "<?php echo $this->lang->line('app_zero_records'); ?>",
                "info": "<?php echo $this->lang->line('app_showing'); ?>",
                "infoEmpty": "<?php echo $this->lang->line('app_empty'); ?>",
                "infoFiltered": "<?php echo $this->lang->line('app_filtered'); ?>",
                "oPaginate": {
                    "sNext": "<?php echo $this->lang->line('app_next'); ?>",
                    "sPrevious": "<?php echo $this->lang->line('app_previous'); ?>",
                    "sFirst": "<?php echo $this->lang->line('app_first'); ?>",
                    "sLast": "<?php echo $this->lang->line('app_last'); ?>"
                },
                "sLoadingRecords": "<?php echo $this->lang->line('app_loading'); ?>",
                "sProcessing": "<?php echo $this->lang->line('app_processing'); ?>",
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

        $.notify({ message: '<?php echo $this->lang->line('app_list_updated'); ?>' },{ type: 'info' });

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
          url: '<?php echo site_url('services/delete_many'); ?>',
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
           $.notify({ message: '<?php echo $this->lang->line('app_error'); ?>' },{ type: 'danger' });
        });
      });


      // remove single item
      $(document).on('click', '.delete', function (event) {

         event.preventDefault();
         var url = $(this).attr('href');
         
         var ask = confirm('<?php echo $this->lang->line('app_sure_delete'); ?>');

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
              $.notify({ message: '<?php echo $this->lang->line('app_error'); ?>' },{ type: 'danger' });
           });
           
         }

      }); 


 });  
 </script> 
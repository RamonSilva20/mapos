@extends('layouts.app')

@section('content')

    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
            <h2>Brands <small> brands list</small></h2>
          
            <div class="clearfix"></div>
            </div>
            <div class="x_content">
            <br>
                
                <table class="table table-bordered" id="brands-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Brand</th>
                        <th>Active</th>
                        <th>created_at</th>
                        <th>updated_at</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                   
                </table>
            
            </div>
        </div>
    </div>


@endsection


@section('js')
    <script>
        $(document).ready(function($) {
            $('#brands-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{route('brands.list')}}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'brand', name: 'brand'},
                    {data: 'active', name: 'active'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'updated_at', name: 'updated_at'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                language: {
                    "search": "Pesquisar",
                    "lengthMenu": "Mostrar _MENU_ registros por página",
                    "zeroRecords": "Nenhum registro encontrado.",
                    "info": "Mostrando página _PAGE_ de _PAGES_",
                    "infoEmpty": "Nenhum registro foi cadastrado.",
                    "infoFiltered": "(filtrado de _MAX_ registros)",
                    "oPaginate": {
                        "sNext": "Próximo",
                        "sPrevious": "Anterior",
                        "sFirst": "Primeiro",
                        "sLast": "Último"
                    },
                    "sLoadingRecords": "Carregando...",
                    "sProcessing": "Processando...",
                }
            });
        });
    </script>
@endsection
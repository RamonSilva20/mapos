@extends('layouts.app') 

@section('content')

<div class="">

    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2> Cadastrar marcas</h2>

                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <br>
                    @include('partials.error')
                    <form id="form-brand" action="{{ route('brands.store')}}" method="post"  class="form-horizontal form-label-left">

                        {{ csrf_field() }}
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Brand Name <span class="required">*</span></label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" id="brand-name" name="brand" required="required" class="form-control col-md-7 col-xs-12">
                            </div>
                        </div>
                      
                        
                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                <a href="{{route('brands')}}" class="btn btn-default">Cancelar</a>
                                <button class="btn btn-warning" type="reset">Limpar</button>
                                <button type="submit" class="btn btn-primary">Salvar</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection()
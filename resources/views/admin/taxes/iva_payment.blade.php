@extends('admin.layouts.dashboard')

@section('content')



    {{-- VALIDACIONES-RESPUESTA--}}
    @include('admin.layouts.success')   {{-- SAVE --}}
    @include('admin.layouts.danger')    {{-- EDITAR --}}
    @include('admin.layouts.delete')    {{-- DELELTE --}}
    {{-- VALIDACIONES-RESPUESTA --}}
    
@if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{$error}}</li>
                @endforeach
            </ul>
        </div>
    @endif
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header text-center font-weight-bold h3">Retiros / Ordenes de Pago</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('bankmovements.storeretirement') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <input id="id_account_iva" type="hidden" class="form-control @error('id_account') is-invalid @enderror" name="id_account_iva" value="{{ $account_iva->id }}" required autocomplete="id_account_iva" autofocus>
                        <input id="user_id" type="hidden" class="form-control @error('user_id') is-invalid @enderror" name="user_id" value="{{ Auth::user()->id }}" required autocomplete="user_id">
                        
                        <div class="form-group row">
                            <label id="coinlabel" for="type" class="col-md-2 col-form-label text-md-right">Tipo:</label>
                            <div class="col-md-2">
                                <select class="form-control" name="type_contado" id="type_contado">
                                    <option selected value="contado">Contado</option>
                                    <option value="credito">Credito</option>
                                </select>
                            </div>
                        </div>
                       
                        <div class="form-group row">
                            <label for="account" class="col-md-2 col-form-label text-md-right">Retirar Desde:</label>

                            <div class="col-md-4">
                                <select  id="account"  name="account" class="form-control">
                                    <option value="">Seleccionar</option>
                                </select>

                                @if ($errors->has('account_id'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('account_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <label for="date_begin" class="col-md-3 col-form-label text-md-right">Fecha del Retiro:</label>

                            <div class="col-md-3">
                                <input id="date_begin" type="date" class="form-control @error('date_begin') is-invalid @enderror" name="date" value="{{ $datenow ?? old('date_begin') }}" required autocomplete="date_begin">

                                @error('date_begin')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                       
                        <div class="form-group row">
                            
                            <label for="description" class="col-md-2 col-form-label text-md-right">Descripción</label>

                            <div class="col-md-4">
                                <input id="description" type="text" class="form-control @error('description') is-invalid @enderror" name="description" value="{{ old('description') }}" required autocomplete="description">

                                @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <label for="reference" class="col-md-3 col-form-label text-md-right">Número de Referencia:</label>

                            <div class="col-md-3">
                                <input id="reference" type="text" class="form-control @error('reference') is-invalid @enderror" name="reference" value="{{ old('reference') }}" autocomplete="reference">

                                @error('reference')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label id="coinlabel" for="coin" class="col-md-2 col-form-label text-md-right">Moneda:</label>

                            <div class="col-md-2">
                                <select class="form-control" name="coin" id="coin">
                                    <option selected value="bolivares">Bolívares</option>
                                    <option value="dolares">Dolares</option>
                                </select>
                            </div>
                            <label for="rate" class="col-md-1 col-form-label text-md-right">Tasa:</label>
                            <div class="col-md-3">
                                <input id="rate" type="text" class="form-control @error('rate') is-invalid @enderror" name="rate" value="{{ $bcv }}" required autocomplete="rate">
                                @error('rate')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            
                            <label for="amount" class="col-md-2 col-form-label text-md-right">Monto:</label>

                            <div class="col-md-4">
                                <input id="amount" type="text" class="form-control @error('amount') is-invalid @enderror" placeholder="0,00" name="amount" value="{{ old('amount') }}" required autocomplete="amount">

                                @error('amount')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            
                        </div>
                       
                        
                      
                        <br>
                        <div class="form-group row mb-0">
                            <div class="col-md-3 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                   Guardar Depósito
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('bankmovements') }}" id="btnfacturar" name="btnfacturar" class="btn btn-danger" title="facturar">Volver</a>  
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('validacion_usuario')
    <script>
        
    

    </script>
@endsection
@section('javascript')
    
    <script>
        $(document).ready(function () {
            $("#amount").mask('00.000.000.000.000,00', { reverse: true });
            
        });
        $(document).ready(function () {
            $("#reference").mask('0000000000000000', { reverse: true });
            
        });
        $(document).ready(function () {
            $("#rate").mask('000.000.000.000.000,00', { reverse: true });
            
        });

        $("#type_contado").on('change',function(){
            
            var type = $(this).val();
            
            getAccount(type);
        });

        function getAccount(type){
            
            $.ajax({
                url:"{{ route('taxes.list_account','') }}" + '/' + type,
                beforSend:()=>{
                    alert('consultando datos');
                },
                success:(response)=>{
                    let subbeneficiario = $("#account");
                    let htmlOptions = `<option value='' >Seleccione..</option>`;
                    // console.clear();
                    if(response.length > 0){
                        response.forEach((item, index, object)=>{
                            let {id,description} = item;
                            htmlOptions += `<option value='${id}' {{ old('Subbeneficiario') == '${id}' ? 'selected' : '' }}>${description}</option>`

                        });
                    }
                    //console.clear();
                    // console.log(htmlOptions);
                    subbeneficiario.html('');
                    subbeneficiario.html(htmlOptions);
                
                    
                
                },
                error:(xhr)=>{
                    alert('Presentamos inconvenientes al consultar los datos');
                }
            })
        }
    </script> 

@endsection    

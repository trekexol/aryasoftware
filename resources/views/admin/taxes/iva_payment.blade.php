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
                <div class="card-header text-center font-weight-bold h3">Debito Fiscal IVA por Pagar</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('bankmovements.storeretirement') }}" enctype="multipart/form-data">
                        @csrf
                        <input id="id_account_iva" type="hidden" class="form-control @error('id_account') is-invalid @enderror" name="id_account_iva" value="{{ $account_iva->id }}" required autocomplete="id_account_iva" autofocus>
                        <input id="user_id" type="hidden" class="form-control @error('user_id') is-invalid @enderror" name="user_id" value="{{ Auth::user()->id }}" required autocomplete="user_id">
                        <div class="form-group row">
                            <div class="col-sm-2">
                                <label id="date_begin" for="type" >Fecha del Retiro Mes:</label>
                            </div>
                            <div class="col-sm-4">


                                <input type="text" class="form-control @error('description') is-invalid @enderror" value="{{$mes_nombre}}"  readonly>
                            </div>
                            <div class="col-sm-2">
                                <label id="date_begin" for="type" >Fecha del Retiro Año:</label>
                            </div>
                            <div class="col-sm-4">
                                <input type="text" class="form-control @error('description') is-invalid @enderror" name="Fecha_Year"  readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2">
                                <label id="Tipo" for="type" >Tipo:</label>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control" name="Tipo" id="type_contado">
                                    <option selected value="">Seleccione..</option>
                                    <option value="contado">Contado</option>
                                    <option value="credito">Credito</option>
                                </select>
                            </div>
                            <div class="col-sm-2" >
                                <label for="Filtro">Retirar / Pagar:</label>
                            </div>
                            <div class="col-sm-4 ">
                                <select  id="account"  name="Filtro" class="form-control">
                                    <option value="">Seleccionar</option>
                                </select>

                                @if ($errors->has('account_id'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('account_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2" >
                                <label for="Filtro">Descripción:</label>
                            </div>
                            <div class="col-sm-10 ">
                                <input id="description" type="text" class="form-control @error('description') is-invalid @enderror" name="description" value="{{ old('Descripcion') }}" required autocomplete="description">
                                @error('description')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2" >
                                <label for="money">Moneda:</label>
                            </div>
                            <div class="col-sm-4 ">
                                <select class="form-control" name="Moneda" id="coin">
                                    <option selected value="bolivares">Bolívares</option>
                                    <option value="dolares">Dolares</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <label for="rate">Tasa:</label>
                            </div>
                            <div class="col-sm-4">
                                <input id="rate" type="text" class="form-control @error('rate') is-invalid @enderror" name="rate" value="{{ number_format($bcv,2,",",".")}}" required autocomplete="rate">
                                @error('rate')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2">
                                <label for="beneficiario">Beneficiario:</label>
                            </div>
                            <div class="col-sm-4">
                                <input id="nro_ref" type="text" class="form-control @error('beneficiario') is-invalid @enderror"  name="Beneficiario" value="SENIAT" readonly autocomplete="beneficiario">
                                @error('beneficiario')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-sm-2">
                                <label for="amount">Debito Fiscal:</label>
                            </div>
                            <div class="col-sm-4">
                                <input id="amount" type="text" class="form-control @error('amount') is-invalid @enderror"  name="amount" value="{{ number_format($debito_fiscal_total,2,",",".") }}" required readonly autocomplete="amount">
                                @error('amount')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <div class="col-sm-2">
                                <label for="amount">Crédito Fiscal:</label>
                            </div>
                            <div class="col-sm-4">
                                <input id="amount" type="text" class="form-control @error('amount') is-invalid @enderror" placeholder="0,00" name="amount" value="{{ number_format($iva_credito_fiscal_total,2,",",".") }}" required autocomplete="amount">
                                @error('amount')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-sm-2">
                                <label for="amount">Excedente:</label>
                            </div>
                            <div class="col-sm-4">
                                <input id="amount" type="text" class="form-control @error('amount') is-invalid @enderror" placeholder="0,00" name="amount" value="{{ old('amount') }}" required autocomplete="amount">
                                @error('amount')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2">
                                <label for="amount">Monto:</label>
                            </div>
                            <div class="col-sm-4">
                                <input id="amount" type="text" class="form-control @error('amount') is-invalid @enderror" placeholder="0,00" name="amount" value="{{ old('amount') }}" required autocomplete="amount">
                                @error('amount')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-sm-2">
                                <label for="amount">Retenciónes del Perido:</label>
                            </div>
                            <div class="col-sm-4">
                                <input id="amount" type="text" class="form-control @error('amount') is-invalid @enderror" placeholder="0,00" name="amount" value="{{ old('amount') }}" required autocomplete="amount">
                                @error('amount')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2">
                                <label for="amount">Total a Pagar:</label>
                            </div>
                            <div class="col-sm-4">
                                <input id="amount" type="text" class="form-control @error('amount') is-invalid @enderror" placeholder="0,00" name="amount" value="{{ old('amount') }}" required autocomplete="amount">
                                @error('amount')
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-sm-2">
                                <label for="amount">Contrapartida:</label>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control" id="exampleFormControlSelect1">
                                    <option>Seleccione..</option>
                                    @foreach($account_impuestos as $index => $value)
                                        <option value="{{ $index }}" {{ old('Botones') == $index ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-2">
                                <label for="amount">Centro de costo:</label>
                            </div>
                            <div class="col-sm-4">
                                    <select class="form-control" id="exampleFormControlSelect1">
                                        <option>Seleccione..</option>
                                        @foreach($branches as $index => $value)
                                            <option value="{{ $index }}" {{ old('Botones') == $index ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                            </div>
                            <div class="col-sm-2">
                                <label for="amount">Emitir Cheque:</label>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control" id="exampleFormControlSelect1">
                                    <option>NO</option>
                                    <option>SI</option>
                                </select>
                            </div>
                        </div>
                        <div id="div_nro" style="display: none">
                            <div class="form-group row">
                                <div class="col-sm-2">
                                    <label for="nro_ref">Nro Referencia:</label>
                                </div>
                                <div class="col-sm-4">
                                    <input id="nro_ref" type="text" class="form-control @error('nro_ref') is-invalid @enderror" placeholder="0000" name="Nro_Ref" value="{{ old('Nro_Ref') }}" required autocomplete="nro_ref">
                                    @error('nro_ref')
                                    <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group row mb-0">
                            <div class="col-sm-3 offset-sm-2">
                                <button type="submit" class="btn btn-primary">
                                   Pagar Impuesto
                                </button>
                            </div>
                            <div class="col-sm-2">
                                <a href="{{ route('taxes.iva_paymentindex') }}" id="btnvolver" name="btnvolver" class="btn btn-danger" title="volver">Volver</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('javascript_iva_payment')

    <script>

            var fecha = new Date();
            var ano = fecha.getFullYear();
            document.getElementsByName("Fecha_Year")[0].value = ano ;

            $("#filtro_mount").on('change',function(){
                var mes_id = $(this).val();
                var fecha   = new Date();
                var ano     = fecha.getFullYear();
                $fe

                if(metodo_id == 1){
                    document.getElementById("pp-button").style.display   = "none";
                    document.getElementById("div_pagar").style.display   = "block";
                }

                if(metodo_id == 2){
                    document.getElementById("pp-button").style.display   = "block";
                    document.getElementById("div_pagar").style.display   = "none";
                }

            });

            $("#type_contado").on('change',function(){
            var type_id = $(this).val();

            if(type_id == 'contado'){
                document.getElementById("div_nro").style.display   = "block";
            }

            if(type_id == 'credito'){
                document.getElementById("div_nro").style.display   = "none";
            }

        });

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

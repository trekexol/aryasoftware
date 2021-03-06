@extends('admin.layouts.dashboard')

@section('content')



<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-12">
            <div class="card">
                <form method="POST" action="{{ route('reports.store_accounts_receivable_note') }}">
                    @csrf

                <input type="hidden" name="id_client" value="{{$client->id ?? null}}" readonly>
                <input type="hidden" name="id_vendor" value="{{$vendor->id ?? null}}" readonly>

                <div class="card-header text-center h4">
                      Notas de Entrega
                </div>

                <div class="card-body">
                        <div class="form-group row">
                            <label for="date_begin" class="col-sm-1 col-form-label text-md-right">Desde:</label>

                            <div class="col-sm-3">
                                <input id="date_begin" type="date" class="form-control @error('date_begin') is-invalid @enderror" name="date_begin" value="{{  date('Y-m-d', strtotime($date_begin ?? $fecha_frist)) }}" required autocomplete="date_begin">
                            </div>
                            <div class="col-sm-2">
                                <select class="form-control" name="type" id="type">
                                    @if (isset($client))
                                        <option value="todo">Todos</option>
                                        <option selected value="cliente">Por Cliente</option>
                                        <option value="vendor">Por Vendedor</option>
                                    @elseif (isset($vendor))
                                        <option value="todo">Todos</option>
                                        <option value="cliente">Por Cliente</option>
                                        <option selected value="vendor">Por Vendedor</option>
                                    @else
                                        <option selected value="todo">Todos</option>
                                        <option value="cliente">Por Cliente</option>
                                        <option value="vendor">Por Vendedor</option>
                                    @endif
                                </select>
                            </div>
                            @if (isset($client))
                            <label id="client_label1" for="clients" class="col-sm-2">Cliente:</label>
                            <label id="client_label2" name="id_client" value="{{ $client->id }}" for="clients" class="col-sm-1">{{ $client->name }}</label>
                            @endif
                            @if (isset($vendor))
                            <label id="client_label1" for="clients" class="col-sm-2">Vendedor:</label>
                                <label id="vendor_label2" name="id_vendor" value="{{ $vendor->id }}" for="vendors" class="col-sm-1">{{ $vendor->name }}</label>
                            @endif
                            
                            <div id="client_label3" class="form-group col-sm-1 text-md-left">
                                <a id="route_select" href="{{ route('reports.select_client_ne') }}" title="Seleccionar Cliente"><i class="fa fa-eye"></i></a>  
                            </div>
                            
                        </div>

                        <div class="form-group row">
                            <label for="date_end" class="col-sm-1 col-form-label text-md-right">Hasta:</label>

                            <div class="col-sm-3">
                                <input id="date_end" type="date" class="form-control @error('date_end') is-invalid @enderror" name="date_end" value="{{ date('Y-m-d', strtotime($date_end ?? $datenow))}}" required autocomplete="date_end">

                                @error('date_end')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                       



                            <div class="col-sm-2">
                                <select class="form-control" name="coin" id="coin">
                                    @if(isset($coin))
                                        <option disabled selected value="{{ $coin }}">{{ $coin }}</option>
                                        <option disabled  value="{{ $coin }}">-----------</option>
                                    @else
                                        <option disabled selected value="bolivares">Moneda</option>
                                    @endif
                                    
                                    <option  value="bolivares">Bol??vares</option>
                                    <option value="dolares">D??lares</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <select class="form-control" name="typeinvoice" id="typeinvoice">
                                    @if (isset($typeinvoice))
                                        @if ($typeinvoice == 'notas')
                                            <option selected value="notas">Notas de Entrega</option>
                                        @elseif($typeinvoice == 'facturas')
                                            <option selected value="facturas">NE Facturadas</option>
                                        @else
                                            <option selected value="todo">Todo</option>
                                        @endif
                                        <option disabled value="todo">-----------------</option>
                                        <option value="todo">Todo</option>
                                        <option value="notas">Notas de Entrega</option>
                                        <option value="facturas">NE Facturadas</option>
                                    @else
                                        <option selected value="todo">Todo</option>
                                        <option value="notas">Notas de Entrega</option>
                                        <option value="facturas">NE Facturadas</option>
                                    @endif
                                </select>
                            </div>

                            <div class="col-sm-1">
                            <button type="submit" class="btn btn-primary ">
                                Buscar
                             </button>
                            </div>
                        </div>
                    </form>
                        <div class="embed-responsive embed-responsive-16by9">
                            <iframe class="embed-responsive-item" src="{{ route('reports.accounts_receivable_note_pdf',[$coin ?? 'bolivares',$date_end,$fecha_frist,$typeinvoice,$typeperson])}}" allowfullscreen></iframe>
                                                                                                                       
     
                          </div>
                        
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
@section('javascript')

    <script>
    $('#dataTable').DataTable({
        "ordering": false,
        "order": [],
        'aLengthMenu': [[-1, 50, 100, 150, 200], ["Todo",50, 100, 150, 200]]
    });

    $("body").toggleClass("sidebar-toggled");
    $(".sidebar").toggleClass("toggled");
    if ($(".sidebar").hasClass("toggled")) {
        $('.sidebar .collapse').collapse('hide');
    };

    let client  = "<?php echo $client->name ?? 0 ?>";  
    let vendor  = "<?php echo $vendor->name ?? 0 ?>"; 

    if(client != 0){
        
        $("#client_label1").show();
        $("#client_label1").html('Cliente:');
        $("#vendor_label2").html('');
        $("#client_label2").show();
        $("#client_label3").show();
        document.getElementById("route_select").href = "{{ route('reports.select_client_ne') }}";
    }else if(vendor != 0){
        
        $("#client_label1").show();
        $("#client_label1").html('Vendedor:');
        $("#client_label2").show();
        $("#client_label3").show();
        document.getElementById("route_select").href = "{{ route('reports.select_vendor_ne') }}";
    }else{
        $("#client_label2").html('');
        $("#client_label2").val('');
        $("#vendor_label2").html('');
        $("#client_label1").hide();
        $("#client_label2").hide();
        $("#client_label3").hide();
    }
    

    $("#type").on('change',function(){
            type = $(this).val();
            
            if(type == 'todo'){
                $("#client_label2").html('');
                $("#client_label2").val('');
                $("#vendor_label2").html('');
                $("#client_label1").hide();
                $("#client_label2").hide();
                $("#client_label3").hide();
            } 
            
            if(type == 'vendor'){
                $("#client_label2").html('');
                $("#client_label2").val('');
                $("#client_label1").show();
                $("#client_label1").html('Vendedor:');
                $("#client_label2").show();
                $("#client_label3").show();
                document.getElementById("route_select").href = "{{ route('reports.select_vendor_ne') }}";
            }
            
            if(type == 'cliente'){
                $("#client_label2").html('');
                $("#client_label2").val('');
                $("#vendor_label2").html('');
                $("#client_label1").show();
                $("#client_label1").html('Cliente:');
                $("#client_label2").show();
                $("#client_label3").show();
                document.getElementById("route_select").href = "{{ route('reports.select_client_ne') }}";
            }
        });

    </script> 

@endsection
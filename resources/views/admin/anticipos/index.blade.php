@extends('admin.layouts.dashboard')

@section('content')

   
<ul class="nav nav-tabs justify-content-center" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
      <a class="nav-link  font-weight-bold" style="color: black;" id="home-tab"  href="{{ route('quotations') }}" role="tab" aria-controls="home" aria-selected="true">Cotizaciones</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link font-weight-bold" style="color: black;" id="profile-tab"  href="{{ route('invoices') }}" role="tab" aria-controls="profile" aria-selected="false">Facturas</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link font-weight-bold" style="color: black;" id="contact-tab"  href="{{ route('quotations.indexdeliverynote') }}" role="tab" aria-controls="contact" aria-selected="false">Notas De Entrega</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link font-weight-bold" style="color: black;" id="profile-tab"  href="{{ route('sales') }}" role="tab" aria-controls="profile" aria-selected="false">Ventas</a>
      </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link active font-weight-bold" style="color: black;" id="contact-tab"  href="{{ route('anticipos') }}" role="tab" aria-controls="contact" aria-selected="false">Anticipos Clientes</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link font-weight-bold" style="color: black;" id="profile-tab"  href="{{ route('clients') }}" role="tab" aria-controls="profile" aria-selected="false">Clientes</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link font-weight-bold" style="color: black;" id="contact-tab"  href="{{ route('vendors') }}" role="tab" aria-controls="contact" aria-selected="false">Vendedores</a>
    </li>
  </ul>

<!-- container-fluid -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="row py-lg-2">
        <div class="col-md-3 h4">
            Anticipos de Clientes
        </div>
        <div class="col-md-2">
            <a href="{{ route('anticipos')}}" class="btn btn-info btn-icon-split">
                <span class="icon text-white-50">
                    <i class="fas fa-hand-holding-usd"></i>
                </span>
                <span class="text">Anticipos</span>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('anticipos.historic')}}" class="btn btn-success btn-icon-split">
                <span class="icon text-white-50">
                    <i class="fas fa-book"></i>
                </span>
                <span class="text">Ver Historico de Anticipos</span>
            </a>
        </div>
       
        @if (Auth::user()->role_id  == '1' || Auth::user()->role_id  == '2' )
        <div class="col-md-3">
            <a href="{{ route('anticipos.create')}}" class="btn btn-primary" role="button" aria-pressed="true">Registrar un Anticipo</a>
         
        </div>
        @endif
       
            
       
    </div>

  </div>

  {{-- VALIDACIONES-RESPUESTA--}}
@include('admin.layouts.success')   {{-- SAVE --}}
@include('admin.layouts.danger')    {{-- EDITAR --}}
@include('admin.layouts.delete')    {{-- DELELTE --}}
{{-- VALIDACIONES-RESPUESTA --}}

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-light2 table-bordered" id="dataTable" width="100%" cellspacing="0">
            <thead>
            <tr>
                <th class="text-center">Cliente</th>
                <th class="text-center">Caja/Banco</th>
                <th class="text-center">Fecha del Anticipo</th>
                <th class="text-center">Referencia</th>
                <th class="text-center">Monto</th>
                <th class="text-center">Moneda</th>
                @if (isset($control) && ($control == 'index'))
                    <th class="text-center"></th>
                @endif
            </tr>
            </thead>
            
            <tbody>
                @if (empty($anticipos))
                @else
                    @foreach ($anticipos as $key => $anticipo)
                    <?php 
                        if($anticipo->coin != 'bolivares'){
                            $anticipo->amount = $anticipo->amount / $anticipo->rate;
                        }
                    ?>
                    <tr>
                        @if (isset($anticipo->quotations['serie']))
                            <td class="text-center">{{$anticipo->clients['name'] ?? ''}} , fact({{$anticipo->quotations['serie'] ?? ''}})</td>
                        @else
                            <td class="text-center">{{$anticipo->clients['name'] ?? ''}}</td>
                        @endif
                    
                    <td class="text-center">{{$anticipo->accounts['description'] ?? ''}}</td>
                    <td class="text-center">{{$anticipo->date ?? ''}}</td>
                    <td class="text-center">{{$anticipo->reference ?? ''}}</td>
                    <td class="text-right">{{number_format($anticipo->amount ?? 0, 2, ',', '.')}}</td>
                    <td class="text-center">{{$anticipo->coin ?? ''}}</td>
                   
                    @if (Auth::user()->role_id  == '1')
                        
                        @if (isset($control) && ($control == 'index'))
                            <td>
                                <a href="{{ route('anticipos.edit',$anticipo->id) }}"  title="Editar"><i class="fa fa-edit"></i></a>
                            </td>
                        @endif
                        
                    @endif
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        </div>
    </div>
</div>

@endsection
@section('javascript')

    <script>
    $('#dataTable').DataTable({
        "ordering": false,
        "order": [],
        'aLengthMenu': [[50, 100, 150, -1], [50, 100, 150, "All"]]
    });
    </script> 
@endsection
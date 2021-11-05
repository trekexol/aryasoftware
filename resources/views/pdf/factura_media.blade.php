<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 
<title>Factura</title>
<style>
  table, td, th {
    border: 1px solid black;
    font-size: x-small;
  }
  
  table {
    border-collapse: collapse;
    width: 50%;
  }
  
  th {
   
    text-align: left;
  }

  .condicion {
    border: none;
    border-width: 0px; 
  }

  </style>
</head>

<body>

  <br><br><br><br><br><br>


 
<table style="width: 100%;">
  @if (isset($company->franqueo_postal))
  <tr>
    <th style="font-weight: normal; width: 10%;">Concesión Postal:</th>
    <th style="font-weight: normal; width: 10%; border: rgb(17, 9, 9);">Nº {{ $company->franqueo_postal ?? ''}}</th>
    <th style="font-weight: normal; width: 10%; border: rgb(255, 255, 255);"></th>
  </tr>
  @endif
    <tr>
     @if (isset($quotation->credit_days))
      <td style="width: 10%;">Fecha de Emisión:</td>
      <td style="width: 10%;">{{ date_format(date_create($quotation->date_billing),"d-m-Y") }} | Dias de Crédito: {{ $quotation->credit_days }}</td>
    
    @else
      <td style="width: 10%;">Fecha de Emisión:</td>
      <td style="width: 10%;">{{ date_format(date_create($quotation->date_billing),"d-m-Y") }}</td>
    @endif
      <td  style="font-size: 11pt; width: 40%; color: black; font-weight: bold; text-align: right; border-top-color: white; border-right-color: white;">FACTURA NRO: {{ str_pad($quotation->number_invoice ?? $quotation->id, 6, "0", STR_PAD_LEFT)}}</td>
     

  </tr>
  
</table>

<table style="width: 100%;">
  <tr>
    <th style="font-weight: normal;">Nombre / Razón Social: &nbsp;  {{ $quotation->clients['name'] ?? ''}} </th>
    <th style="font-weight: normal;">Vendedor: {{ $quotation->vendors['name'] ?? 'No aplica' }} {{ $quotation->vendors['surname'] ?? ''}} </th>
    
  </tr>
</table>
<table style="width: 100%;">
  <tr>
    <th style="font-weight: normal;">Domicilio Fiscal: &nbsp;  {{ $quotation->clients['direction'] ?? ''}}</th>
  </tr>
</table>

<table style="width: 100%;">
  <tr>
    <th style="text-align: center; " >Teléfono</th>
    <th style="text-align: center; ">RIF/CI</th>
    <th style="text-align: center; ">N° Control / Serie</th>
    <th style="text-align: center; ">Nota de Entrega</th>
    <th style="text-align: center; ">Transp./Tipo de Entrega</th>
   
  </tr>
  <tr>
    <td style="text-align: center; ">{{ $quotation->clients['phone1'] }}</td>
    <td style="text-align: center; ">{{ $quotation->clients['type_code'] ?? ''}} {{ $quotation->clients['cedula_rif'] ?? '' }}</td>
    <td style="text-align: center; ">{{ $quotation->serie }}</td>
    <td style="text-align: center; ">{{ $quotation->number_delivery_note }}</td>
    <td style="text-align: center; ">{{ $quotation->transports['placa'] ?? '' }}</td>
    
    
  </tr>
  
</table>

<table style="width: 100%;">
  <tr>
  <th style="font-weight: normal; ">Observaciones: &nbsp; {{ $quotation->observation }} </th>
</tr>
  
</table>
  @if (!empty($payment_quotations))
      

      <table class="condicion" style="width: 100%;">
        <tr>
          <th class="condicion" style="text-align: center; width: 100%;">Condiciones de Pago</th>
        </tr> 
      </table>

      <table style="width: 100%;">
        <tr>
          <th style="text-align: center; ">Tipo de Pago</th>
          <th style="text-align: center; ">Cuenta</th>
          <th style="text-align: center; ">Referencia</th>
          <th style="text-align: center; ">Dias de Credito</th>
          <th style="text-align: center; ">Monto</th>
        </tr>

        @foreach ($payment_quotations as $var)
        <tr>
          <th style="text-align: center; font-weight: normal;">{{ $var->payment_type }}</th>
          @if (isset($var->accounts['description']))
            <th style="text-align: center; font-weight: normal;">{{ $var->accounts['description'] }}</th>
          @else    
            <th style="text-align: center; font-weight: normal;"></th>
          @endif
          <th style="text-align: center; font-weight: normal;">{{ $var->reference }}</th>
          <th style="text-align: center; font-weight: normal;">{{ $var->credit_days }}</th>
          <th style="text-align: center; font-weight: normal;">{{ number_format($var->amount, 2, ',', '.')}}</th>
        </tr> 
        @endforeach 
        
      </table>
  @endif

<table class="condicion" style="width: 100%;">
  <tr>
    <th class="condicion" style="text-align: center; width: 100%;">Productos</th>
  </tr> 
</table>
<table style="width: 100%;">
  <tr>
    <th style="text-align: center; ">Código</th>
    <th style="text-align: center; ">Descripción</th>
    <th style="text-align: center; ">Cantidad</th>
    <th style="text-align: center; ">P.V.J.</th>
    <th style="text-align: center; ">Desc</th>
    <th style="text-align: center; ">Total</th>
  </tr> 
  @foreach ($inventories_quotations as $var)
      <?php
      $percentage = (($var->price * $var->amount_quotation) * $var->discount)/100;

      $total_less_percentage = ($var->price * $var->amount_quotation) - $percentage;

      $total_less_percentage = $total_less_percentage / ($bcv ?? 1);
      ?>
    <tr>
      <th style="text-align: center; font-weight: normal;">{{ $var->code_comercial }}</th>
      <th style="text-align: center; font-weight: normal;">{{ $var->description }}</th>
      <th style="text-align: center; font-weight: normal;">{{ number_format($var->amount_quotation, 0, '', '.') }}</th>
      <th style="text-align: center; font-weight: normal;">{{ number_format($var->price / ($bcv ?? 1), 2, ',', '.')  }}</th>
      <th style="text-align: center; font-weight: normal;">{{ $var->discount }}%</th>
      <th style="text-align: right; font-weight: normal;">{{ number_format($total_less_percentage, 2, ',', '.') }}</th>
    </tr> 
  @endforeach 
</table>


<?php
  $monto_m = 0;
  $iva = ($quotation->base_imponible * $quotation->iva_percentage)/100;
  
  //$total = $quotation->sub_total_factura + $iva - $quotation->anticipo;

  $total = $quotation->amount_with_iva;

  $total_petro = ($total - $quotation->anticipo) / $company->rate_petro;

  $iva = $iva / ($bcv ?? 1);
  $total = $total / ($bcv ?? 1);

  if($coin == 'bolivares'){
    $monto_m = $quotation->amount_with_iva / ($quotation->bcv ?? 1);
    $signo_moneda = '$';
  }
  if($coin == 'dolares'){
    $monto_m = $quotation->amount_with_iva;  
    $signo_moneda = 'Bs.';
  }
   $texto_taza = ' Taza de cambio a la fecha '.($quotation->bcv ?? 1).' Bs.';

?>

<table style="width: 100%;">
  <tr>
    <th style="text-align: left; width: 38%; border-bottom-color: white; border-right-color: white;" ></th>
    <th style="text-align: right; font-weight: normal; width: 21%; border-bottom-color: white;">Sub Total</th>
    <th style="text-align: right; font-weight: normal; width: 21%;">{{ number_format($quotation->amount / ($bcv ?? 1), 2, ',', '.') }}</th>
  </tr> 
  <tr>
    <th style="text-align: left; width: 38%; border-bottom-color: white; border-right-color: white;" ></th>
    <th style="text-align: right; font-weight: normal; width: 21%; border-bottom-color: white;">Base Imponible</th>
    <th style="text-align: right; font-weight: normal; width: 21%;">{{ number_format($quotation->base_imponible / ($bcv ?? 1), 2, ',', '.') }}</th>
  </tr> 
  @if ($quotation->retencion_iva != 0)
    <tr>
      <th style="text-align: left; width: 38%; border-bottom-color: white; border-right-color: white;" ></th>
      <th style="text-align: right; font-weight: normal; width: 21%; border-bottom-color: white;">Retención de Iva</th>
      <th style="text-align: right; font-weight: normal; width: 21%;">{{ number_format($quotation->retencion_iva / ($bcv ?? 1), 2, ',', '.') }}</th>
    </tr> 
  @endif 
  @if ($quotation->retencion_islr != 0)
    <tr>
      <th style="text-align: left; width: 38%; border-bottom-color: white; border-right-color: white;" ></th>
      <th style="text-align: right; font-weight: normal; width: 21%; border-bottom-color: white;">Retención de ISLR</th>
      <th style="text-align: right; font-weight: normal; width: 21%;">{{ number_format($quotation->retencion_islr / ($bcv ?? 1), 2, ',', '.') }}</th>
    </tr> 
  @endif 
  <tr>
    <th style="text-align: left; width: 38%; border-bottom-color: white; border-right-color: white;" ></th>
    <th style="text-align: right; font-weight: normal; width: 21%; border-bottom-color: white;">I.V.A.{{ $quotation->iva_percentage }}%</th>
    <th style="text-align: right; font-weight: normal; width: 21%;">{{ number_format($iva, 2, ',', '.') }}</th>
  </tr> 
  @if ($quotation->anticipo != 0)
  <tr>
    <th style="text-align: left; width: 38%; border-bottom-color: white; border-right-color: white;" ></th>
    <th style="text-align: right; font-weight: normal; width: 21%; border-bottom-color: white;">Anticipo</th>
    <th style="text-align: right; font-weight: normal; width: 21%;">{{ number_format($quotation->anticipo / ($bcv ?? 1), 2, ',', '.') }}</th>
  </tr> 
  @endif
 
  <tr>
    <th style="text-align: left; width: 38%; border-bottom-color: white; border-right-color: white;" ></th>
    <th style="text-align: right; font-weight: normal; width: 21%; border-bottom-color: white;">MONTO TOTAL</th>
    <th style="text-align: right; font-weight: normal; width: 21%;">{{ number_format($total, 2, ',', '.') }}</th>
  </tr> 
  
  @if (isset($company->pie_pagina))
  <tr>
    <th style="text-align: left; width: 38%; border-bottom-color: white; border-right-color: white;" ></th>
    <th style="text-align: right; font-weight: normal; width: 21%; border-bottom-color: white; font-size: small;">MONTO TOTAL Petro</th>
    <th style="text-align: right; font-weight: normal; width: 21%;">{{ number_format($total_petro, 6, ',', '.') }}</th>
  </tr> 
  <tr>
    <th style="text-align: left; font-weight: normal; width: 79%; border-top-color: rgb(17, 9, 9); border-right-color: white; font-size: small;"><pre>ESTA FACTURA VA SIN TACHADURAS NI ENMIENDAS</pre></th>
    <th style="text-align: right; font-weight: normal; width: 21%; "></th>
  </tr> 
@else
  <tr>
    <th style="text-align: left; width: 38%; border-bottom-color: white; border-right-color: white; font-weight: normal; padding-left: 5px;" >{{$texto_taza}}</th>
    <th style="text-align: right; font-weight: normal; width: 21%; border-bottom-color: white;">MONTO TOTAL {{$signo_moneda}}</th>
    <th style="text-align: right; font-weight: normal; width: 21%;">{{ number_format($monto_m, 2, ',', '.') }}</th>
  </tr> 
  <tr>
    <th style="text-align: left; width: 38%; border-bottom-color: black; border-right-color: white;" ></th>
    <th style="text-align: left; font-weight: normal; width: 21%; border-top-color: rgb(17, 9, 9); border-right-color: white; font-size: small;"></th>
    <th style="text-align: right; font-weight: normal; width: 21%; "></th>
  </tr> 
@endif
  
  
  
</table>

</body>
</html>

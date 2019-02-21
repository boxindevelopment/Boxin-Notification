<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Boxin | Invoice</title>
    
    <style>
    .invoice-box {
        max-width: 800px;
        margin: auto;
        padding: 30px;
        border: 1px solid #eee;
        box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        font-size: 16px;
        line-height: 24px;
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        color: #555;
    }
    
    .invoice-box table {
        width: 100%;
        line-height: inherit;
        text-align: left;
    }
    
    .invoice-box table td {
        padding: 5px;
        vertical-align: top;
    }
    
    /* .invoice-box table tr td:nth-child(2) {
        text-align: right;
    } */
    
    .invoice-box table tr.top table td {
        padding-bottom: 20px;
    }
    
    .invoice-box table tr.top table td.title {
        font-size: 45px;
        line-height: 45px;
        color: #333;
    }
    
    .invoice-box table tr.information table td {
        padding-bottom: 40px;
    }
    
    .invoice-box table tr.heading td {
        background: #eee;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
    }
    
    .invoice-box table tr.details td {
        padding-bottom: 20px;
    }
    
    .invoice-box table tr.item td{
        border-bottom: 1px solid #eee;
    }
    
    /* .invoice-box table tr.item.last td {
        border-bottom: none;
    } */
    
    .invoice-box table tr.total td:nth-child(2) {
        /* border-top: 2px solid #eee; */
        font-weight: bold;
    }
    
    @media only screen and (max-width: 600px) {
        .invoice-box table tr.top table td {
            width: 100%;
            display: block;
            text-align: center;
        }
        
        .invoice-box table tr.information table td {
            width: 100%;
            display: block;
            text-align: center;
        }
    }
    
    /** RTL **/
    .rtl {
        direction: rtl;
        font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
    }
    
    .rtl table {
        text-align: right;
    }
    
    .rtl table tr td:nth-child(2) {
        text-align: left;
    }
    </style>
</head>

<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="4">
                    <table>
                        <tr>
                            <td class="title">
                              <div style="display: inline-flex; flex-basis: auto;">
                                <div style="margin-right: 8px;">
                                  <img src="{{ asset('images/boxin.png') }}" style="width:100%; max-width:65px;">
                                </div>
                                <a href="http://box-in.com/" style="text-decoration: none; color: #59aaef;">
                                <h2 style="margin-top: 15px;margin-bottom: 0px;font-size: 50px; color: #59aaef;">
                                    Box-in
                                </h2>
                                </a>
                              </div>
                            </td>
                            {{-- nomor dan tanggal invoice --}}
                            <td style="text-align: right;">
                                Invoice #: {{ $order->order_detail->first()->id_name }}<br>
                                {{ date('d F Y', strtotime($order->created_at)) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="information">
                <td colspan="4">
                    <table>
                        <tr>
                            {{-- Alamat boxin --}}
                            <td>
                                Komplek Pergudangan Central<br> 
                                Cakung Blok F No. 28<br>
                                Jalan Cakung Cilincing,<br>
                                Jakarta Timur
                            </td>
                            
                            {{-- detail user --}}
                            <td style="text-align:right;">
                                {{ $order->user->name }}<br>
                                {{ $order->user->email }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="heading">
                <td>
                    Order ID
                </td>
                <td style="text-align:center;">
                    Qty
                </td>
                <td style="text-align:center;">
                    Fee
                </td>
                <td style="text-align:center;">
                    Total
                </td>
            </tr>
            
            <tr class="details">
                <td>
                    #{{ $order->order_detail->first()->id_name }}
                </td>
                <td style="text-align:center;">
                    {{ $order->qty }}
                </td>
                <td style="text-align:center;">
                  {{ number_format($order->deliver_fee ,0,',','.') }}
                </td>
                <td style="text-align:center;">
                  {{ number_format($order->total ,0,',','.') }}
                </td>
            </tr>
            <tr>
              <td colspan="4">
                <hr>
              </td>
            </tr>
            <tr class="heading">
                <td>
                    Box
                </td>
                <td style="text-align:center;">
                    Duration
                </td>
                <td style="text-align:center;">
                    Price
                </td>                
                <td style="text-align:center;">
                    Total
                </td>
            </tr>
            
            @php
              $totals = 0;   
            @endphp
            @foreach ($order->order_detail as $key => $value)
            <tr class="item">
              <td>
                @if ($value->box)
                {{ $value->box->code_box . ' (' . $value->box->name . ')' }}  
                @else
                
                @endif
                  {{-- 010101B1010101 (small box 1) --}}
              </td>
              <td style="text-align:center;">
                  {{ $value->duration . ' ' . $value->type_duration->name }}
              </td>
              <td style="text-align:center;">
                  @php
                    $pricing = $value->amount / $value->duration;
                  @endphp
                  {{ number_format($pricing ,0,',','.') }}
              </td>
              <td style="text-align:center;">
                  {{ number_format($value->amount ,0,',','.') }}
                  @php
                      $totals += $value->amount;
                  @endphp
              </td>
            </tr>
            @endforeach
            {{-- <tr class="item last">
                <td colspan="3" style="text-align:right;">
                    Fee
                </td>
                <td style="text-align:center;">
                    1000
                </td>
            </tr> --}}
            <tr class="total">
                <td colspan="3" style="text-align:right; font-weight:bold;">Total:</td>
                <td style="text-align:center;">
                  {{ number_format($totals ,0,',','.') }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
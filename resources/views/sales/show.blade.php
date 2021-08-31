@extends('layouts.app', ['page' => 'Manage Sale', 'pageSlug' => 'sales', 'section' => 'transactions'])

@section('styles')
<link href="{{ asset('assets') }}/css/invoice.css" rel="stylesheet" />
@endsection

@section('content')
@include('alerts.success')
@include('alerts.error')
<div class="row">
    <div class="col-md-12">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-8">
                        <h4 class="card-title">Sale Summary</h4>
                    </div>
                    @if (!$sale->finalized_at)
                    <div class="col-4 text-right">
                        @if ($sale->products->count() == 0)
                        <form action="{{ route('sales.destroy', $sale) }}" method="post" class="d-inline">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-sm btn-primary">
                                Delete Sale
                            </button>
                        </form>
                        @else
                        <button type="button" class="btn btn-sm btn-primary" onclick="confirm('ATTENTION: The transactions of this sale do not seem to coincide with the cost of the products, do you want to finalize it? Your records cannot be modified from now on.') ? window.location.replace('{{ route('sales.finalize', $sale) }}') : ''">
                            Finalize Sale
                        </button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <th>ID</th>
                        <th>Date</th>
                        <th>User</th>
                        <th>Client</th>
                        <th>products</th>
                        <th>Total Stock</th>
                        <th>Total Cost</th>
                        <th>Status</th>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $sale->id }}</td>
                            <td>{{ date('d-m-y', strtotime($sale->created_at)) }}</td>
                            <td>{{ $sale->user->name }}</td>
                            <td><a href="{{ route('clients.show', $sale->client) }}">{{ $sale->client->name }}<br>{{ $sale->client->document_type }}-{{ $sale->client->document_id }}</a></td>
                            <td>{{ $sale->products->count() }}</td>
                            <td>{{ $sale->products->sum('qty') }}</td>
                            <td>{{ format_money($sale->products->sum('total_amount')) }}</td>
                            <td>{!! $sale->finalized_at ? 'Completed at<br>'.date('d-m-y', strtotime($sale->finalized_at)) : (($sale->products->count() > 0) ? 'TO FINALIZE' : 'ON HOLD') !!}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@if (!$sale->finalized_at)
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-8">
                        <h4 class="card-title">products: {{ $sale->products->sum('qty') }}</h4>
                    </div>
                    
                    <div class="col-4 text-right">
                        <a href="{{ route('sales.product.add', ['sale' => $sale->id]) }}" class="btn btn-sm btn-primary">Add</a>
                    </div>
                    
                </div>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price C/U</th>
                        <th>Total</th>
                        <th></th>
                    </thead>
                    <tbody>
                        @foreach ($sale->products as $sold_product)
                        <tr>
                            <td>{{ $sold_product->product->id }}</td>
                            <td><a href="{{ route('categories.show', $sold_product->product->category) }}">{{ $sold_product->product->category->name }}</a></td>
                            <td><a href="{{ route('products.show', $sold_product->product) }}">{{ $sold_product->product->name }}</a></td>
                            <td>{{ $sold_product->qty }}</td>
                            <td>{{ format_money($sold_product->price) }}</td>
                            <td>{{ format_money($sold_product->total_amount) }}</td>
                            <td class="td-actions text-right">
                                @if(!$sale->finalized_at)
                                <a href="{{ route('sales.product.edit', ['sale' => $sale, 'soldproduct' => $sold_product]) }}" class="btn btn-link" data-toggle="tooltip" data-placement="bottom" title="Edit Pedido">
                                    <i class="tim-icons icon-pencil"></i>
                                </a>
                                <form action="{{ route('sales.product.destroy', ['sale' => $sale, 'soldproduct' => $sold_product]) }}" method="post" class="d-inline">
                                    @csrf
                                    @method('delete')
                                    <button type="button" class="btn btn-link" data-toggle="tooltip" data-placement="bottom" title="Delete Pedido" onclick="confirm('Estás seguro que quieres eliminar este pedido de producto/s? Su registro será eliminado de esta venta.') ? this.parentElement.submit() : ''">
                                        <i class="tim-icons icon-simple-remove"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif
@if ($sale->finalized_at)
<div>
    <div class="row">
        <div class="col-lg-12 equel-grid card">
            <div class="grid">
                <p class="grid-header">Print Invoice</p>
                <div class="grid-body">
                    <div class="item-wrapper">
                        <div class="page-container">
                            Page
                            <span class="page"></span>
                            of
                            <span class="pages"></span>
                        </div>

                        <div class="logo-container">
                            <img style="height: 18px" src="https://app.useanvil.com/img/email-logo-black.png">
                        </div>

                        <table class="invoice-info-container">
                            <tr>
                                <td rowspan="2" class="client-name">
                                    {{$sale->client->name}}
                                </td>
                                <td>
                                    Anvil Co
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    123 Main Street
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Invoice Date: <strong>{{date('Y-m-d h:ia', strtotime($sale->created_at))}}</strong>
                                </td>
                                <td>
                                    San Francisco CA, 94103
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Invoice No: <strong>12345</strong>
                                </td>
                                <td>
                                    hello@useanvil.com
                                </td>
                            </tr>
                        </table>


                        <table class="table">
                            <thead>
                                <th>ID</th>
                                <th>Category</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price C/U</th>
                                <th>Total</th>
                                <th></th>
                            </thead>
                            <tbody>
                                @foreach ($sale->products as $sold_product)
                                <tr>
                                    <td>{{ $sold_product->product->id }}</td>
                                    <td><a href="{{ route('categories.show', $sold_product->product->category) }}">{{ $sold_product->product->category->name }}</a></td>
                                    <td><a href="{{ route('products.show', $sold_product->product) }}">{{ $sold_product->product->name }}</a></td>
                                    <td>{{ $sold_product->qty }}</td>
                                    <td>{{ format_money($sold_product->price) }}</td>
                                    <td>{{ format_money($sold_product->total_amount) }}</td>
                                    <td class="td-actions text-right">
                                        @if(!$sale->finalized_at)
                                        <a href="{{ route('sales.product.edit', ['sale' => $sale, 'soldproduct' => $sold_product]) }}" class="btn btn-link" data-toggle="tooltip" data-placement="bottom" title="Edit Pedido">
                                            <i class="tim-icons icon-pencil"></i>
                                        </a>
                                        <form action="{{ route('sales.product.destroy', ['sale' => $sale, 'soldproduct' => $sold_product]) }}" method="post" class="d-inline">
                                            @csrf
                                            @method('delete')
                                            <button type="button" class="btn btn-link" data-toggle="tooltip" data-placement="bottom" title="Delete Pedido" onclick="confirm('Estás seguro que quieres eliminar este pedido de producto/s? Su registro será eliminado de esta venta.') ? this.parentElement.submit() : ''">
                                                <i class="tim-icons icon-simple-remove"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>


                        <table class="line-items-container has-bottom-border">
                            <thead>
                                <tr>
                                    <th>Payment Info</th>
                                  
                                    <th>Total Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="payment-info">
                                        <div>
                                            Account No: <strong>123567744</strong>
                                        </div>
                                        <div>
                                            Routing No: <strong>120000547</strong>
                                        </div>
                                    </td>
                                   
                                    <td class="large total">{{ format_money($sale->products->sum('total_amount')) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="footer">
                            <div class="footer-info">
                                <span>hello@useanvil.com</span> |
                                <span>555 444 6666</span> |
                                <span>useanvil.com</span>
                            </div>
                            <div class="footer-thanks">
                                <img src="https://github.com/anvilco/html-pdf-invoice-template/raw/main/img/heart.png" alt="heart">
                                <span>Thank you!</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('js')
<script src="{{ asset('assets') }}/js/sweetalerts2.js"></script>
@endpush

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html
    lang="{{ $locale = app()->getLocale() }}"
    dir="{{ in_array($locale, ['fa', 'ar']) ? 'rtl' : 'ltr' }}"
>
    <head>
        <!-- meta tags -->
        <meta
            http-equiv="Cache-control"
            content="no-cache"
        >

        <meta
            http-equiv="Content-Type"
            content="text/html; charset=utf-8"
        />

        @php
            if ($locale == 'en') {
                $fontFamily = [
                    'regular' => 'DejaVu Sans',
                    'bold'    => 'DejaVu Sans',
                ];
            }  else {
                $fontFamily = [
                    'regular' => 'Arial, sans-serif',
                    'bold'    => 'Arial, sans-serif',
                ];
            }

            if (in_array($locale, ['ar', 'fa', 'tr'])) {
                $fontFamily = [
                    'regular' => 'DejaVu Sans',
                    'bold'    => 'DejaVu Sans',
                ];
            }
        @endphp

        <!-- lang supports inclusion -->
        <style type="text/css">
            * {
                box-sizing: border-box;
            }

            body {
                font-size: 12px;
                color: #333;
                font-family: {{ $fontFamily['regular'] }};
                line-height: 1.4;
            }

            b, th {
                font-family: {{ $fontFamily['bold'] }};
                font-weight: bold;
            }

            .page-content {
                padding: 0;
            }

            .page-header {
                padding-bottom: 20px;
                border-bottom: 2px solid #eee;
                margin-bottom: 20px;
            }

            .logo-container {
                text-align: left;
                margin-bottom: 10px;
            }

            .logo-container img {
                max-height: 60px;
                max-width: 200px;
            }

            .quote-title {
                text-align: right;
                font-size: 28px;
                color: #333;
                text-transform: uppercase;
                float: right;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            
            table thead th {
                background-color: #f8f9fa;
                color: #555;
                padding: 10px;
                text-align: left;
                border-bottom: 1px solid #ddd;
                text-transform: uppercase;
                font-size: 10px;
            }

            table.rtl thead tr th {
                text-align: right;
            }

            table tbody td {
                padding: 10px;
                border-bottom: 1px solid #eee;
                vertical-align: top;
            }

            table.rtl tbody tr td {
                text-align: right;
            }

            .summary-container {
                width: 100%;
                margin-top: 20px;
            }

            .summary-table {
                width: 40%;
                float: right;
            }

            .summary-table td {
                padding: 5px 10px;
                border-bottom: 1px solid #eee;
            }

            .summary-table td:last-child {
                text-align: right;
            }
            
            .text-right {
                text-align: right;
            }

            .text-center {
                text-align: center;
            }

            .align-top {
                vertical-align: top;
            }

            /* Clearfix */
            .clearfix::after {
                content: "";
                clear: both;
                display: table;
            }
        </style>
    </head>

    <body dir="{{ $locale }}">
        <div class="page">
            <!-- Header -->
            <div class="page-header clearfix">
                <div class="logo-container" style="float: left;">
                    @if (core()->getConfigData('general.design.admin_logo.logo_image'))
                        <img src="{{ Storage::url(core()->getConfigData('general.design.admin_logo.logo_image')) }}" alt="{{ config('app.name') }}"/>
                    @else
                        <img src="{{ asset('vendor/webkul/admin/assets/images/logo.png') }}" alt="{{ config('app.name') }}"/>
                    @endif
                </div>

                <div class="quote-title">
                    <b>@lang('admin::app.quotes.index.pdf.title')</b>
                </div>
            </div>

            <div class="page-content">
                <!-- Invoice Information -->
                <table class="table-info">
                    <tbody>
                        <tr>
                            <td style="width: 50%;">
                                <div class="label">@lang('admin::app.quotes.index.pdf.quote-id')</div>
                                <div class="value">#{{ $quote->id }}</div>
                            </td>

                            <td style="width: 50%;">
                                <div class="label">@lang('admin::app.quotes.index.pdf.date')</div>
                                <div class="value">{{ core()->formatDate($quote->created_at, 'd-m-Y') }}</div>
                            </td>
                        </tr>

                        <tr>
                            <td style="width: 50%;">
                                <div class="label">@lang('admin::app.quotes.index.pdf.subject')</div>
                                <div class="value">{{ $quote->subject }}</div>
                            </td>

                            <td style="width: 50%;">
                                <div class="label">@lang('admin::app.quotes.index.pdf.expired-at')</div>
                                <div class="value">{{ core()->formatDate($quote->expired_at, 'd-m-Y') }}</div>
                            </td>
                        </tr>

                        <tr>
                            <td style="width: 50%;">
                                <div class="label">@lang('admin::app.quotes.index.pdf.person')</div>
                                <div class="value">{{ $quote->person->name }}</div>
                            </td>

                            <td style="width: 50%;">
                                <div class="label">@lang('admin::app.quotes.index.pdf.sales-person')</div>
                                <div class="value">{{ $quote->user->name }}</div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Billing & Shipping Address -->
                <div style="margin-top: 20px;">
                    <table class="table-address">
                        <thead>
                            <tr>
                                @if ($quote->billing_address)
                                    <th style="width: 50%;">
                                        @lang('admin::app.quotes.index.pdf.billing-address')
                                    </th>
                                @endif

                                @if ($quote->shipping_address)
                                    <th style="width: 50%">
                                        @lang('admin::app.quotes.index.pdf.shipping-address')
                                    </th>
                                @endif
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                @if ($quote->billing_address)
                                    <td>
                                        <div>{{ $quote->billing_address['address'] ?? '' }}</div>
                                        <div>{{ $quote->billing_address['postcode'] ?? '' . ' ' .$quote->billing_address['city'] ?? '' }} </div>
                                        <div>{{ $quote->billing_address['state'] ?? '' }}</div>
                                        <div>{{ core()->country_name($quote->billing_address['country'] ?? '') }}</div>
                                    </td>
                                @endif
                                
                                @if ($quote->shipping_address)
                                    <td>
                                        <div>{{ $quote->shipping_address['address'] ?? ''}}</div>
                                        <div>{{ $quote->shipping_address['postcode'] ?? '' . ' ' .$quote->shipping_address['city'] ?? '' }} </div>
                                        <div>{{ $quote->shipping_address['state'] ?? '' }}</div>
                                        <div>{{ core()->country_name($quote->shipping_address['country'] ?? '') }}</div>
                                    </td>
                                @endif
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Items -->
                <div class="items" style="margin-top: 20px;">
                    <table>
                        <thead>
                            <tr>
                                <th>@lang('admin::app.quotes.index.pdf.sku')</th>
                                <th>@lang('admin::app.quotes.index.pdf.product-name')</th>
                                <th class="text-right">@lang('admin::app.quotes.index.pdf.price')</th>
                                <th class="text-center">@lang('admin::app.quotes.index.pdf.quantity')</th>
                                <th class="text-right">@lang('admin::app.quotes.index.pdf.amount')</th>
                                <th class="text-right">@lang('admin::app.quotes.index.pdf.discount')</th>
                                <th class="text-right">@lang('admin::app.quotes.index.pdf.tax')</th>
                                <th class="text-right">@lang('admin::app.quotes.index.pdf.grand-total')</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($quote->items as $item)
                                <tr>
                                    <td>{{ $item->sku }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td class="text-right">{!! core()->formatBasePrice($item->price, true) !!}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-right">{!! core()->formatBasePrice($item->total, true) !!}</td>
                                    <td class="text-right">{!! core()->formatBasePrice($item->discount_amount, true) !!}</td>
                                    <td class="text-right">{!! core()->formatBasePrice($item->tax_amount, true) !!}</td>
                                    <td class="text-right">{!! core()->formatBasePrice($item->total + $item->tax_amount - $item->discount_amount, true) !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Summary Table -->
                <div class="summary-container clearfix">
                    <table class="summary-table">
                        <tbody>
                            <tr>
                                <td>@lang('admin::app.quotes.index.pdf.sub-total')</td>
                                <td>{!! core()->formatBasePrice($quote->sub_total, true) !!}</td>
                            </tr>
        
                            <tr>
                                <td>@lang('admin::app.quotes.index.pdf.tax')</td>
                                <td>{!! core()->formatBasePrice($quote->tax_amount, true) !!}</td>
                            </tr>
        
                            <tr>
                                <td>@lang('admin::app.quotes.index.pdf.discount')</td>
                                <td>{!! core()->formatBasePrice($quote->discount_amount, true) !!}</td>
                            </tr>
        
                            <tr>
                                <td>@lang('admin::app.quotes.index.pdf.adjustment')</td>
                                <td>{!! core()->formatBasePrice($quote->adjustment_amount, true) !!}</td>
                            </tr>
        
                            <tr>
                                <td><strong>@lang('admin::app.quotes.index.pdf.grand-total')</strong></td>
                                <td><strong>{!! core()->formatBasePrice($quote->grand_total, true) !!}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>

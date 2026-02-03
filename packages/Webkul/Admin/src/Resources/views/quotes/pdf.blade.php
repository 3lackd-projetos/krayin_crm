<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="{{ $locale = app()->getLocale() }}" dir="{{ in_array($locale, ['fa', 'ar']) ? 'rtl' : 'ltr' }}">

<head>
    <meta http-equiv="Cache-control" content="no-cache">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@lang('admin::app.quotes.index.pdf.title')</title>
    @php
        $fontFamily = in_array($locale, ['ar', 'fa', 'tr']) ? 'DejaVu Sans, sans-serif' : 'Arial, sans-serif';
    @endphp
    <style type="text/css">
        /* Reset & Base */
        body {
            font-family:
                {{ $fontFamily }}
            ;
            font-size: 12px;
            color: #0d141b;
            /* Dark Text */
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }

        /* Colors */
        .text-primary {
            color: #137fec;
        }

        .text-dark {
            color: #0d141b;
        }

        .text-muted {
            color: #4c739a;
        }

        .text-white {
            color: #ffffff;
        }

        .bg-light {
            background-color: #f6f7f8;
        }

        .bg-blue-light {
            background-color: rgba(19, 127, 236, 0.05);
        }

        /* Typography */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin: 0;
            font-weight: bold;
        }

        .font-bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .text-xs {
            font-size: 10px;
        }

        .text-sm {
            font-size: 12px;
        }

        .text-lg {
            font-size: 16px;
        }

        .text-xl {
            font-size: 24px;
        }

        .text-4xl {
            font-size: 36px;
            line-height: 1;
        }

        /* Layout Helpers (Table-based Grid) */
        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        td {
            vertical-align: top;
        }

        .w-half {
            width: 50%;
        }

        .w-full {
            width: 100%;
        }

        .p-4 {
            padding: 15px;
        }

        .pb-2 {
            padding-bottom: 8px;
        }

        .mb-4 {
            margin-bottom: 20px;
        }

        /* Header */
        .header {
            padding: 20px 40px;
            border-bottom: 1px solid #eee;
        }

        .logo-img {
            max-height: 50px;
        }

        /* Hero Section */
        .hero {
            padding: 30px 40px;
            border-bottom: 1px solid #f0f0f0;
        }

        /* Grid Section */
        .grid-info td {
            padding: 15px;
            border-right: 1px solid #f0f0f0;
        }

        .grid-info td:last-child {
            border-right: none;
        }

        /* Cards (Stakeholders) */
        .card-table {
            border: 1px solid #eee;
            border-radius: 8px;
            /* Note: dompdf support for radius is limited */
            background-color: #fcfcfc;
            margin-bottom: 15px;
        }

        .card-icon {
            width: 40px;
            text-align: center;
            vertical-align: middle;
        }

        .card-content {
            padding: 15px;
        }

        /* Addresses */
        .address-section {
            padding: 20px 40px;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
        }

        /* Items Table */
        .items-container {
            padding: 20px 40px;
        }

        .items-table thead th {
            text-align: left;
            padding: 12px;
            color: #4c739a;
            font-size: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #eee;
        }

        .items-table tbody td {
            padding: 15px 12px;
            border-bottom: 1px solid #f9f9f9;
            color: #0d141b;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Summary */
        .summary-section {
            padding: 0 40px 40px 40px;
        }

        .summary-table td {
            padding: 5px 0;
        }

        .summary-table .label {
            color: #4c739a;
            font-size: 12px;
        }

        .summary-table .value {
            color: #0d141b;
            font-weight: bold;
            text-align: right;
        }

        .total-row td {
            border-top: 1px solid #eee;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 16px;
        }

        .grand-total {
            color: #137fec;
            font-size: 18px;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #4c739a;
            font-size: 10px;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <table class="header">
        <tr>
            <td style="text-align: left;">
                @if (core()->getConfigData('general.design.admin_logo.logo_image'))
                    <img class="logo-img"
                        src="{{ public_path('storage/' . core()->getConfigData('general.design.admin_logo.logo_image')) }}"
                        alt="{{ config('app.name') }}" />
                @else
                    <img class="logo-img" src="{{ public_path('vendor/webkul/admin/assets/images/logo.png') }}"
                        alt="{{ config('app.name') }}" />
                @endif
            </td>
            <td style="text-align: right; vertical-align: middle;">
                <span class="text-primary font-bold text-lg">{{ config('app.name') }}</span>
            </td>
        </tr>
    </table>

    <!-- Hero Title -->
    <table class="hero">
        <tr>
            <td>
                <div class="text-primary text-xs font-bold uppercase" style="letter-spacing: 2px; margin-bottom: 5px;">
                    @lang('admin::app.quotes.index.pdf.subject')</div>
                <div class="text-dark text-4xl font-bold">@lang('admin::app.quotes.index.pdf.title')</div>
                <div class="text-muted font-bold">#{{ $quote->id }}</div>
            </td>
            <td style="text-align: right; vertical-align: bottom;">
                <div
                    style="background-color: #e6fffa; color: #047857; padding: 5px 15px; border-radius: 15px; display: inline-block; font-weight: bold; font-size: 10px; text-transform: uppercase;">
                    Ativo
                </div>
            </td>
        </tr>
    </table>

    <!-- Info Grid -->
    <table class="grid-info" style="width: 100%; border-bottom: 1px solid #f0f0f0;">
        <tr>
            <!-- Date -->
            <td>
                <div class="text-muted text-xs font-bold uppercase pb-2">@lang('admin::app.quotes.index.pdf.date')</div>
                <div class="text-dark font-bold">{{ core()->formatDate($quote->created_at, 'd-m-Y') }}</div>
            </td>
            <!-- Subject -->
            <td>
                <div class="text-muted text-xs font-bold uppercase pb-2">@lang('admin::app.quotes.index.pdf.subject')
                </div>
                <div class="text-dark font-bold">{{ $quote->subject }}</div>
            </td>
            <!-- Expiry -->
            <td>
                <div class="text-muted text-xs font-bold uppercase pb-2">@lang('admin::app.quotes.index.pdf.expired-at')
                </div>
                <div class="text-red-500 font-bold" style="color: #ef4444;">
                    {{ core()->formatDate($quote->expired_at, 'd-m-Y') }}</div>
            </td>
            <!-- User -->
            <td>
                <div class="text-muted text-xs font-bold uppercase pb-2">
                    @lang('admin::app.quotes.index.pdf.sales-person')</div>
                <div class="text-dark font-bold">{{ $quote->user->name }}</div>
            </td>
        </tr>
    </table>

    <!-- Stakeholders (Cards simulation) -->
    <div style="padding: 20px 40px;">
        <table cellspacing="0" cellpadding="0" style="width: 100%;">
            <tr>
                <!-- Client Card -->
                <td style="width: 48%; padding-right: 2%;">
                    <table class="card-table">
                        <tr>
                            <td class="card-content">
                                <div class="text-muted text-xs font-bold uppercase">
                                    @lang('admin::app.quotes.index.pdf.person')</div>
                                <div class="text-dark text-lg font-bold">{{ $quote->person->name }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <!-- Sales Person Card (Duplicate logic for layout balance, or Empty) -->
                <td style="width: 48%; padding-left: 2%;">
                    <!-- Optional: Could put Organization info here if available, currently just filler or mirrored logic -->
                    <table class="card-table">
                        <tr>
                            <td class="card-content">
                                <div class="text-muted text-xs font-bold uppercase">
                                    @lang('admin::app.quotes.index.pdf.sales-person')</div>
                                <div class="text-dark text-lg font-bold">{{ $quote->user->name }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- Addresses -->
    <div class="address-section">
        <table>
            <tr>
                @if ($quote->billing_address)
                    <td class="w-half">
                        <div style="margin-bottom: 10px;">
                            <span
                                class="text-primary font-bold uppercase text-xs">@lang('admin::app.quotes.index.pdf.billing-address')</span>
                        </div>
                        <div class="text-dark text-sm">
                            {{ $quote->billing_address['address'] ?? '' }}<br>
                            {{ $quote->billing_address['postcode'] ?? '' }} {{ $quote->billing_address['city'] ?? '' }}<br>
                            {{ $quote->billing_address['state'] ?? '' }}<br>
                            {{ core()->country_name($quote->billing_address['country'] ?? '') }}
                        </div>
                    </td>
                @endif

                @if ($quote->shipping_address)
                    <td class="w-half">
                        <div style="margin-bottom: 10px;">
                            <span
                                class="text-primary font-bold uppercase text-xs">@lang('admin::app.quotes.index.pdf.shipping-address')</span>
                        </div>
                        <div class="text-dark text-sm">
                            {{ $quote->shipping_address['address'] ?? ''}}<br>
                            {{ $quote->shipping_address['postcode'] ?? '' }}
                            {{ $quote->shipping_address['city'] ?? '' }}<br>
                            {{ $quote->shipping_address['state'] ?? '' }}<br>
                            {{ core()->country_name($quote->shipping_address['country'] ?? '') }}
                        </div>
                    </td>
                @endif
            </tr>
        </table>
    </div>

    <!-- Items -->
    <div class="items-container">
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 15%;">@lang('admin::app.quotes.index.pdf.sku')</th>
                    <th style="width: 35%;">@lang('admin::app.quotes.index.pdf.product-name')</th>
                    <th class="text-right">@lang('admin::app.quotes.index.pdf.price')</th>
                    <th class="text-center">@lang('admin::app.quotes.index.pdf.quantity')</th>
                    <th class="text-right">@lang('admin::app.quotes.index.pdf.amount')</th>
                    <th class="text-right">@lang('admin::app.quotes.index.pdf.tax')</th>
                    <th class="text-right">@lang('admin::app.quotes.index.pdf.grand-total')</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quote->items as $item)
                    <tr>
                        <td>{{ $item->sku }}</td>
                        <td>
                            <strong class="text-dark">{{ $item->name }}</strong>
                        </td>
                        <td class="text-right">{!! core()->formatBasePrice($item->price, true) !!}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{!! core()->formatBasePrice($item->total, true) !!}</td>
                        <td class="text-right">{!! core()->formatBasePrice($item->tax_amount, true) !!}</td>
                        <td class="text-right font-bold text-dark">
                            {!! core()->formatBasePrice($item->total + $item->tax_amount - $item->discount_amount, true) !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Summary -->
    <div class="summary-section">
        <table style="width: 100%;">
            <tr>
                <td style="width: 60%;"></td> <!-- Spacer -->
                <td style="width: 40%;">
                    <table class="summary-table">
                        <tr>
                            <td class="label">@lang('admin::app.quotes.index.pdf.sub-total')</td>
                            <td class="value">{!! core()->formatBasePrice($quote->sub_total, true) !!}</td>
                        </tr>
                        <tr>
                            <td class="label">@lang('admin::app.quotes.index.pdf.tax')</td>
                            <td class="value">{!! core()->formatBasePrice($quote->tax_amount, true) !!}</td>
                        </tr>
                        <tr>
                            <td class="label">@lang('admin::app.quotes.index.pdf.discount')</td>
                            <td class="value" style="color: #ef4444;">
                                {!! core()->formatBasePrice($quote->discount_amount, true) !!}</td>
                        </tr>
                        <tr>
                            <td class="label">@lang('admin::app.quotes.index.pdf.adjustment')</td>
                            <td class="value">{!! core()->formatBasePrice($quote->adjustment_amount, true) !!}</td>
                        </tr>
                        <tr class="total-row">
                            <td class="label" style="padding-top: 15px;">TOTAL GERAL</td>
                            <td class="grand-total" style="padding-top: 15px;">
                                {!! core()->formatBasePrice($quote->grand_total, true) !!}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Obrigado por sua preferência. Esta cotação é válida até {{ core()->formatDate($quote->expired_at, 'd-m-Y') }}
        </p>
        <p class="uppercase" style="margin-top: 5px; opacity: 0.6;">Gerado por {{ config('app.name') }}</p>
    </div>

</body>

</html>
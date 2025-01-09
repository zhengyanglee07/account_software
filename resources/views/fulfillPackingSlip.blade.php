<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Packing Slip - Hypershapes</title>

    <link rel="shortcut icon" href="{{ asset('images/hypershapes_icon_bg_new.png') }}" />
    <style type="text/css">
        @font-face {
            font-family: 'adobeheitistd';
            src: url('{{ base_path() . '/storage/' }}fonts/adobeheitistd-regular.otf') format('truetype');
            unicode-range: U+4E00-9FFF;
        }

        * {
            font-family: 'adobeheitistd';
        }

        li {
            list-style-type: none;
        }

        .packing-container {
            margin: 96px 48px;
        }

        .packing-details__container {
            display: flex;
            width: 100%;
            margin-bottom: 10px;
            flex-direction: column;
        }

        .packing-details__title {
            font-size: 14px;
            margin-bottom: 2px;
        }

        .packing-details__description {
            font-size: 12px;
            font-weight: 400;
            margin-bottom: 2px;
            overflow-wrap: break-word;
            word-wrap: break-word;
            vertical-align: text-top;
        }

        .packing-details__smallText {
            font-size: 12px;
            font-weight: 400;
        }

        .packing__footer {
            width: 100%;
            margin: auto;
            text-align: center;
            margin-top: 1.5cm;
            font-size: 10px;
        }

        .page-break {
            page-break-after: always;
        }

        td {
            padding-bottom: 10px;
            padding-top: 10px;
        }

        .flex-container {
            display: flex;
            flex-wrap: wrap;
            text-align: center;
        }

        th {
            text-align: left;
            width: 50%;
        }

        .billing-table th:first-child,
        .billing-table td:first-child {
            display: none;
        }

        .table td {
            padding-bottom: 0;
            padding-top: 0;
        }

        .table-wrapper {
            margin-top: 10px;
            padding: 10px 0;
            border-top: 1px solid lightgray !important;
            border-bottom: 1px solid lightgray !important;
        }

        p {
            line-height: 1;
            margin: 0px !important;
            padding: 0px !important;
        }
    </style>

</head>
@foreach ($fulfilledOrderDetails as $key => $values)

    <body style="width: 100%;">
        <!-- for page margin -->
        <div>
            <div class="packing-details__container">
                <div style="width:9.5cm">
                    {{-- <h1 style="overflow-wrap: break-word; word-wrap: break-word;">
                          {{ $account->company }}
					</h1> --}}
                    <img src="{{ $account->company_logo }}" height="50px">
                </div>
                <div style="text-align:right;">
                    <span style="font-size: 14px;">
                        Packing Slip {{ $key }}
                    </span>
                    <div style="width: 190px;text-align:left;margin-left: auto;">
                        <p class="packing-details__smallText">
                            Issue Date : {{ $orders->issueDate }}
                        </p>
                        <p class="packing-details__smallText">
                            Order Date : {{ $orders->convertTime }}
                        </p>
                    </div>
                </div>
            </div>
            <table style="width: 100%; margin-top: 20px"
                class="table {{ isset($orders->shipping_name) ? '' : 'billing-table' }}">
                <tr class="packing-details__title">
                    <th>Ship To</th>
                    <th>Bill To</th>
                </tr>
                <tr>
                    @foreach (['shipping', 'billing'] as $type)
                        <td class="packing-details__description">
                            <p>{{ $orders[$type . '_name'] }} </p>
                            <p>{{ $orders[$type . '_company_name'] }} </p>
                            <p>{{ $orders[$type . '_address'] }} </p>
                            <p>{{ $orders[$type . '_zipcode'] }}, {{ $orders[$type . '_city'] }}</p>
                            <p>{{ $orders[$type . '_state'] }} </p>
                            <p>{{ $orders[$type . '_country'] }} </p>
                            <p>{{ $orders[$type . '_phoneNumber'] }} </p>
                        </td>
                    @endforeach
                </tr>
            </table>

            <div class="table-wrapper">
                <table style="width: 100%">
                    <tr>
                        <th style="text-align: left;font-size:14px;">
                            Products
                        </th>
                        <th style="text-align: right;font-size:14px;">
                            Quantity
                        </th>
                    </tr>
                    @foreach ($values as $orderDetail)
                        @if ($values)
                            {{ $orderDetail }}
                            <tr style="margin-top:0px;padding-top:0px">
                                <td>
                                    <span>
                                        <p style="font-size:14px; font-weight: 600">{{ $orderDetail->product_name }}
                                        </p>
                                        @if ($orderDetail->SKU)
                                            <li class="packing-details__description">SKU : {{ $orderDetail->SKU }}
                                            </li>
                                        @endif
                                        @if (floatVal($orderDetail->weight) != 0)
                                            <li class="packing-details__description mb-0">
                                                Weight : {{ $orderDetail->weight }}(kg)
                                            </li>
                                        @endif
                                        @forelse(json_decode($orderDetail->variant) ?? [] as $variant)
                                        @if($variant->id !== 0)
                                                    <li class="packing-details__description mb-0">{{ $variant->label }} :
                                                        {{ $variant->value }}</li>
                                                @endif
                                        @empty
                                        @endforelse

                                        @forelse(json_decode($orderDetail->customization) ?? [] as $customization)
                                            @if ($customization != null)
                                                <li class="packing-details__description mb-0">
                                                    {{ $customization->label }}
                                                    :
                                                    @foreach ($customization->values as $value)
                                                        <span>{{ $value->value_label }}</span>
                                                        <span>{{ $loop->index + 1 < count($customization->values) ? ', ' : '' }}</span>
                                                    @endforeach
                                                </li>
                                            @endif
                                        @empty
                                        @endforelse

                                    </span>
                                </td>
                                <td style="text-align: right;font-size:14px;">x {{ floor($orderDetail->quantity) }}
                                </td>

                            </tr>
                        @endif
                    @endforeach
                </table>
            </div>

            <div class="packing-details__title" style="width:100%;padding-top:10px;">
                Remarks
                <p class="packing-details__description">{{ $orders->notes ?? '-' }}</p>
            </div>

            <div class="packing__footer">
                <div>
                    {{ $account->company }} <br />
                    {{ $account->address }}, <br />
                    {{ $account->city }},
                    {{ $account->state }},
                    {{ $account->zip }},
                    {{ $account->country }}
                </div>

                <div style="width: 100%; position:fixed; bottom: 96px;left: 50%; transform:translateX(-50%);">
                    <img src="https://media.hypershapes.com/images/web-affiliate-badge.png" height="55px"
                        style="margin-top: 10px">
                </div>
            </div>

        </div>
    </body>
@endforeach

@foreach ([$unfulfilledOrderDetails, $removedOrderDetails] as $ordersDetail)
    @if (count($ordersDetail) > 0)

        <body style="width: 100%">
            <!-- for page margin -->
            <div>
                <div class="packing-details__container">
                    <div style="width:9.5cm">
                        {{-- <h1 style="overflow-wrap: break-word; word-wrap: break-word;">
								{{ $account->company }}
						</h1> --}}
                        <img src="{{ $account->company_logo }}" height="50px">
                    </div>
                    <div style="text-align:right;">
                        <span style="font-size: 14px;">
                            Packing Slip #{{ $orders->order_number }}
                        </span>
                        <div style="width: 190px;text-align:left;margin-left: auto;">
                            <p class="packing-details__smallText">
                                Issue Date : {{ $orders->issueDate }}
                            </p>
                            <p class="packing-details__smallText">
                                Order Date : {{ $orders->convertTime }}
                            </p>
                        </div>
                    </div>
                </div>

                <table style="width: 100%" class="table {{ isset($orders->shipping_name) ? '' : 'billing-table' }}">
                    <tr class="packing-details__title">
                        <th>Ship To</th>
                        <th>Bill To</th>
                    </tr>
                    <tr>
                        @foreach (['shipping', 'billing'] as $type)
                            <td class="packing-details__description">
                                <p>{{ $orders[$type . '_name'] }} </p>
                                <p>{{ $orders[$type . '_company_name'] }} </p>
                                <p>{{ $orders[$type . '_address'] }} </p>
                                <p>{{ $orders[$type . '_zipcode'] }}, {{ $orders[$type . '_city'] }}</p>
                                <p>{{ $orders[$type . '_state'] }} </p>
                                <p>{{ $orders[$type . '_country'] }} </p>
                                <p>{{ $orders[$type . '_phoneNumber'] }} </p>
                            </td>
                        @endforeach
                    </tr>
                </table>

                <div class="table-wrapper">
                    <table style="width: 100%">
                        <tr>
                            <th style="text-align: left;font-size:14px;">
                                Products
                            </th>
                            <th style="text-align: right;font-size:14px;">
                                Quantity
                            </th>
                        </tr>
                        @foreach ($ordersDetail as $orderDetail)
                            @if ($orderDetail)
                                <tr style="margin-top:0px;padding-top:0px">
                                    <td style="width:100%">
                                        <span>
                                            <p style="font-size:12px; font-weight: 400">
                                                {{ $orderDetail->product_name }}</p>
                                            @if ($orderDetail->SKU)
                                                <li class="packing-details__description">SKU :
                                                    {{ $orderDetail->SKU }}
                                                </li>
                                            @endif
                                            @if (floatVal($orderDetail->weight) != 0)
                                                <li class="packing-details__description mb-0">
                                                    Weight : {{ $orderDetail->weight }}(kg)
                                                </li>
                                            @endif
                                            @forelse(json_decode($orderDetail->variant) ?? [] as $variant)
                                                @if($variant->id !== 0)
                                                    <li class="packing-details__description mb-0">{{ $variant->label }} :
                                                        {{ $variant->value }}</li>
                                                @endif
                                            @empty
                                            @endforelse

                                            @forelse(json_decode($orderDetail->customization) ?? [] as $customization)
                                                @if ($customization != null)
                                                    <li class="packing-details__description mb-0">
                                                        {{ $customization->label }} :
                                                        @foreach ($customization->values as $value)
                                                            <span>{{ $value->value_label }}</span>
                                                            <span>{{ $loop->index + 1 < count($customization->values) ? ', ' : '' }}</span>
                                                        @endforeach
                                                    </li>
                                                @endif
                                            @empty
                                            @endforelse

                                        </span>
                                    </td>
                                    <td style="text-align:right;font-size:12px;">x
                                        {{ floor($orderDetail->quantity) }}</td>

                                </tr>
                            @endif
                        @endforeach

                    </table>
                </div>

                <div class="packing-details__title" style="width:100%;padding-top:10px;">
                    Remarks
                    <p class="packing-details__description">{{ $orders->notes ?? '-' }}</p>
                </div>

                <div class="packing__footer">
                    <div>
                        {{ $account->company }} <br />
                        {{ $account->address }}, <br />
                        {{ $account->city }},
                        {{ $account->state }},
                        {{ $account->zip }},
                        {{ $account->country }}
                    </div>

                    <div style="width: 100%;">
                        <img src="https://media.hypershapes.com/images/web-affiliate-badge.png" height="55px"
                            style="margin-top: 30px">
                    </div>
                </div>
            </div>
        </body>
    @endif
@endforeach
</html>

@extends('shopify::layouts.app')
@section('content')
    <div class="container">
        <div class="col-12">
            <div class="row">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('shopify.dashboard') }}" onclick="showLoadingSpinner();"
                       class="btn btn-outline-success btn-lg me-2" role="button"
                       aria-pressed="true">REST API</a>
                    <a href="{{ route('shopify.dashboard2') }}" onclick="showLoadingSpinner();"
                       class="btn btn-outline-warning btn-lg ms-2"
                       role="button"
                       aria-pressed="true">GraphQL</a>
                </div>
            </div>
            <div class="row">
                <div class="d-flex">
                    <div>
                        @if( route('shopify.dashboard'))
                            <h1>Shopify REST Dashboard</h1>
                        @elseif( route('shopify.dashboard2'))
                            <h1>Shopify GraphQL Dashboard</h1>
                        @endif                    </div>
                    <div class="ms-auto p-2">
                        <form onsubmit="showLoadingSpinner()" action="{{ route('shopify.dashboard') }}" method="GET">
                            <div class="input-group mb-3">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">Vendors
                                </button>
                                <ul class="dropdown-menu">
                                    @foreach($vendors as $vendor)
                                        <li class="dropdown-item">
                                            <div class="input-group mb-3">
                                                <div class="input-group-text">
                                                    <input class="form-check-input mt-0" type="radio" name="vendor"
                                                           value="{{ $vendor['node'] }}"
                                                           aria-label="Radio button for following text input">
                                                </div>
                                                <input type="text" class="form-control"
                                                       aria-label="Text input with radio button"
                                                       placeholder="{{ $vendor['node'] }}" readonly>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                <input type="hidden" name="sortBy" value="{{ $sortBy }}">

                                <input type="text" class="form-control" name="title" aria-label="Search"
                                       placeholder="Product's title" style="min-width: 20em;">

                                <button type="submit" class="btn btn-outline-dark">Search</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="d-flex justify-content-end mb-2">
                    <button class="btn btn-outline-primary" id="discountButton" disabled>
                        Discount
                    </button>
                    <button class="btn btn-outline-warning" id="revertButton" disabled>
                        Revert
                    </button>
                </div>
                <!-- Modal -->
                <div id="myModal" class="modal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <!-- Modal header -->
                            <div class="modal-header">
                                <h5 class="modal-title">Selected Variants</h5>
                                <button type="button" class="close" id="closeButton">&times;</button>
                            </div>

                            <div class="modal-body" id="modalBody">

                            </div>
                            <form onsubmit="showLoadingSpinner()" method="post"
                                  action="{{ route('shopify.RestDiscount') }}" id="discountForm">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="variant_id" id="variantIdInput">
                                <div class="form-group">
                                    <label for="discountType">Discount Type:</label>
                                    <select class="form-control" id="discountType" name="option">
                                        <option value="percent">Percent (%)</option>
                                        <option value="fixed">Fixed Amount</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="discountValue">Discount Value:</label>
                                    <input type="number" class="form-control" id="discountValue" min="1" step="any"
                                           name="value" placeholder="Enter discount value">

                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-secondary" id="submitButton">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal" tabindex="-1" id="revertModal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Revert discount.</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                            </div>
                            <form onsubmit="showLoadingSpinner()" method="post"
                                  action="{{ route('shopify.RestRevert') }}" id="revertForm">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="variant_id" id="revertVariantIdInput">
                                <p>Are you sure to revert?</p>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                    <button type="submit" class="btn btn-primary" id="revertSubmitButton">Yes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <table class="table-design mb-2">
                <thead>
                <tr>
                    <th>
                        <div class="form-check">
                            <label for="selectAll"></label><input class="form-check-input" type="checkbox"
                                                                  id="selectAll">
                        </div>
                    </th>
                    <th style="width: 8em; max-width: 9em;">ID</th>
                    <th style="width: 4em; max-width: 6em;">Image</th>
                    <th>Title</th>
                    {{--                    <th>Tags</th>--}}
                    <th>Vendor</th>
                    <th style="width: 7em; min-width: 7em;">Price</th>
                    <th style="width: 9em; min-width: 8em;">Discount</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($products as $product)
                    @if (count($product['variants']) == 1)
                        <tr>
                            <td>
                                <div class="form-check">
                                    <label>
                                        <input class="form-check-input product-checkbox"
                                               name="variant_id[]" value="{{ $product['variants'][0]['id'] }}"
                                               type="checkbox" data-product-id="{{ $product['id'] }}"
                                               id="productCheckbox_{{ $product['id'] }}">
                                    </label>
                                </div>
                            </td>
                            <td>{{ $product['id'] }}</td>
                            <td>
                                @if($product['image'] == null)
                                    @php
                                        $no_img = "https://t3.ftcdn.net/jpg/04/62/93/66/360_F_462936689_BpEEcxfgMuYPfTaIAOC1tCDurmsno7Sp.jpg";
                                    @endphp
                                    <img alt="Image of {{ $product['title'] }}" src="{{ $no_img }}" width="40px"
                                         height="40px">
                                @else
                                    <img alt="Image of {{ $product['title'] }}" src="{{ $product['image']['src'] }}"
                                         width="40px" height="40px">
                                @endif
                            </td>
                            <td>{{ $product['title'] }}</td>
                            <td>{{ $product['vendor'] }}</td>
                            <td>
                                {{ $product['variants'][0]['price'] }} {{ $currency }}
                            </td>
                            <td>
                                    <?php
                                    $id = "{$product['variants'][0]['id']}";
                                    $local_variant = \Modules\Shopify\Entities\Price::where('product_id', $id)->first();
                                    if ($local_variant) {
                                        $price_local = $local_variant->price;
                                        $type = $local_variant->discount_type;
                                        $discount = $local_variant->discount_value;
                                        if ($type == 'fixed') {
                                            echo number_format($discount, 2, '.', '') . ' ' . $currency;
                                        } else {
                                            echo number_format($discount, 2, '.', '') . '%';
                                        }
                                    } else {
                                        echo 0;
                                    }
                                    ?>

                            </td>
                        </tr>
                    @else
                        <td>
                            <div class="form-check">
                                <label>
                                    <input class="form-check-input product-checkbox"
                                           type="checkbox"
                                           data-product-id="{{ $product['id'] }}"
                                           id="productCheckbox_{{ $product['id'] }}">
                                </label>
                            </div>
                        </td>
                        <td>{{ $product['id'] }}</td>
                        <td>
                            @if($product['image'] == null)
                                @php
                                    $no_img = "https://t3.ftcdn.net/jpg/04/62/93/66/360_F_462936689_BpEEcxfgMuYPfTaIAOC1tCDurmsno7Sp.jpg";
                                @endphp
                                <img alt="Image of {{ $product['title'] }}" src="{{ $no_img }}" width="40px"
                                     height="40px">
                            @else
                                <img alt="Image of {{ $product['title'] }}" src="{{ $product['image']['src'] }}"
                                     width="40px" height="40px">
                            @endif
                        </td>
                        <td>{{ $product['title'] }}</td>
                        <td>{{ $product['vendor'] }}</td>
                        <td>
                            <button class="btn btn-info btn-sm" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#view-{{ $product['id'] }}"
                                    aria-expanded="false" aria-controls="view-{{ $product['id'] }}">
                                Show more
                            </button>
                        </td>
                        <td></td>
                    @endif
                    @foreach ($product['variants'] as $variant)
                        <tr class="collapse custom-tr" id="view-{{ $product['id'] }}">
                            <td>
                                <div class="form-check">
                                    <label>
                                        <input class="form-check-input variant-checkbox" type="checkbox"
                                               name="variant_id[]" value="{{ $variant['id'] }}"
                                               data-product-id="{{ $product['id'] }}"
                                               id="variantCheckbox_{{ $variant['id'] }}">
                                    </label>
                                </div>
                            </td>
                            <td>{{ $variant['id'] }}</td>
                            <td>
                                @if($variant['image_id'] == null)
                                    @php
                                        $no_img = "https://t3.ftcdn.net/jpg/04/62/93/66/360_F_462936689_BpEEcxfgMuYPfTaIAOC1tCDurmsno7Sp.jpg";
                                    @endphp
                                    <img alt="Image of {{ $product['title'] }}" src="{{ $no_img }}" width="40px"
                                         height="40px">
                                @else
                                    @foreach($product['images'] as $img)
                                        @if($variant['image_id'] == $img['id'])
                                            <img alt="Image of {{ $product['title'] }}" src="{{ $img['src'] }}"
                                                 width="40px" height="40px">
                                        @endif
                                    @endforeach
                                @endif
                            </td>
                            <td>{{ $variant['title'] }}</td>
                            <td>{{ $product['vendor'] }}</td>
                            <td>{{ $variant['price'] }} {{ $currency }}</td>
                            <td>
                                @php
                                    $id = "{$variant['id']}";
                                    $variant_local = \Modules\Shopify\Entities\Price::where('product_id', $id)->first();
                                    if($variant_local){
                                        $price_local = $variant_local->price;
                                        $type = $variant_local->discount_type;
                                        $discount = $variant_local->discount_value;
                                        $discount_price = number_format($discount, 2, '.', '');
                                        if($type == 'fixed'){
                                            echo $discount_price . ' ' . $currency;
                                        } else {
                                            echo $discount_price . '%';
                                        }
                                    } else {
                                        echo 0;
                                    }
                                @endphp
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
            <div class="d-flex">
                <div class="justify-content-center input-group">
                    @if($link !== '')
                        @if($pageInfo['previous'])
                            <a class="btn btn-outline-dark" onclick="showLoadingSpinner();"
                               href="{{ route('shopify.dashboard', ['pageInfo' => $pageInfo['previous'], 'rel' => 'previous', 'sortBy' => $sortBy]) }}">Previous</a>
                        @endif
                        @if($pageInfo['next'])
                            <a class="btn btn-outline-dark" onclick="showLoadingSpinner();"
                               href="{{ route('shopify.dashboard', ['pageInfo' => $pageInfo['next'], 'rel' => 'next', 'sortBy' => $sortBy]) }}">Next</a>
                        @endif
                    @endif
                </div>
                @if($query === '')
                    <div class="justify-content-end">
                        <form onsubmit="showLoadingSpinner()" action="{{ route('shopify.dashboard') }}" method="GET">
                            <div class="input-group">
                                <input type="number" min="1" class="form-control" name="sortBy" aria-label="Sort"
                                       placeholder="{{ $sortBy }}" value="{{ $sortBy }}" style="max-width: 5rem">
                                <button class="btn btn-outline-secondary" type="submit">OK</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
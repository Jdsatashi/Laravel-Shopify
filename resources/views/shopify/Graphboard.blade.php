@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="col-12">
            <div class="row">
                <div class="d-flex justify-content-center">
                    <a href="{{ route('shopify.dashboard') }}" class="btn btn-outline-success btn-lg me-2" role="button" aria-pressed="true">REST API</a>
                    <a href="{{ route('shopify.dashboard2') }}" class="btn btn-outline-warning btn-lg ms-2" role="button" aria-pressed="true">GraphQL</a>
                </div>
            </div>
            <div class="row">
                <div class="d-flex">
                    <div>
                        @if(!route('shopify.dashboard'))
                            <h1>Shopify REST Dashboard</h1>
                        @else
                            <h1>Shopify GraphQL Dashboard</h1>
                        @endif
                    </div>
                    <div class="ms-auto p-2">
                        <form action="{{ route('shopify.dashboard2') }}" method="GET">
                            <div class="d-flex">

                            <div class="input-group mb-3 me-1" style="width: 40%;">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="checkbox" name="collection" value="collection" aria-label="Checkbox" id="collectionCheckbox">
                                </div>
                                <select class="form-select" name="collectionValue" aria-label="Default select example" id="selectCollection" disabled>
                                    @foreach($collection as $collect)
                                        <option value="{{ $collect['id'] }}">{{ $collect['title'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="input-group mb-3" id="normalSearch">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Vendors</button>
                                <ul class="dropdown-menu">
                                    @foreach($vendors as $vendor)
                                        <li class="dropdown-item">
                                            <div class="input-group mb-3">
                                                <div class="input-group-text">
                                                    <input class="form-check-input mt-0" type="radio" name="vendor" value="{{ $vendor['node'] }}" aria-label="Radio button for following text input">
                                                </div>
                                                <input type="text" class="form-control" aria-label="Text input with radio button" placeholder="{{ $vendor['node'] }}" readonly>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                <input type="text" class="form-control" name="title" aria-label="Search" placeholder="T-shirt, dress, etc." style="min-width: 20%; max-width: 60%;">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Tags</button>
                                <ul class="dropdown-menu">
                                @foreach($tags as $tag)
                                    <li class="dropdown-item" >
                                        <div class="input-group mb-3">
                                            <div class="input-group-text">
                                                <input class="form-check-input mt-0" type="checkbox" name="tag[]" value="{{ $tag['node'] }}" aria-label="Checkbox">
                                            </div>
                                            <input type="text" class="form-control" aria-label="Text input with checkbox" placeholder="{{ $tag['node'] }}" readonly>
                                        </div>
                                    </li>
                                @endforeach
                                </ul>
                            </div>
                            <button type="submit" class="btn btn-outline-dark mb-3">Search</button>
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
                            <form method="post" action="{{ route('shopify.GraphDiscount') }}" id="discountForm">
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
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="post" action="{{ route('shopify.GraphRevert') }}" id="revertForm">
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
                            <label for="selectAll"></label><input class="form-check-input" type="checkbox" id="selectAll">
                        </div>
                    </th>
                    <th style="width: 8em; max-width: 9em;">ID</th>
                    <th style="width: 4em; max-width: 6em;">Image</th>
                    <th>Title</th>
{{--                    <th>Tags</th>--}}
                    <th>Vendor</th>
                    <th style="width: 7em; min-width: 7em;">Current price</th>
                    <th style="width: 9em; min-width: 9em;">Discount</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($products as $product)
                    <tr>
                        @foreach($variants as $variant)
                            @if($variant['id'] == $product['id'])
                                @if(count($variant['variants']['edges']) == 1)
                                    <td>
                                        <div class="form-check">
                                            <label for="productCheckbox_{{ $product['id'] }}">
                                                <input class="form-check-input product-checkbox" type="checkbox"
                                                       name="variant_id[]" value="{{ $variant['variants']['edges'][0]['node']['id'] }}"
                                                       data-product-id="{{ $product['id'] }}"
                                                       id="productCheckbox_{{ $product['id'] }}">
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $id = $product['id'];
                                            $sort_id = str_replace('gid://shopify/Product/', '', $id);
                                        @endphp
                                        {{ $sort_id }}
                                    </td>
                                    <td>
                                        @forelse($product['images']['nodes'] as $image)
                                            <img  alt="Image of {{ $product['title'] }}" src="{{ $image['url'] }}" width="40px" height="40px">
                                        @empty
                                            @php
                                                $no_img = "https://t3.ftcdn.net/jpg/04/62/93/66/360_F_462936689_BpEEcxfgMuYPfTaIAOC1tCDurmsno7Sp.jpg";
                                            @endphp
                                            <img  alt="Image of {{ $product['title'] }}" src="{{ $no_img }}" width="40px" height="40px">
                                        @endforelse
                                    </td>
                                    <td>{{ $product['title'] }}</td>
                                    <td>{{ $product['vendor'] }}</td>
                                    <td>{{ $variant['variants']['edges'][0]['node']['price'] }} {{ $currency }}</td>
                                    <td>
                                        @if($variant['variants']['edges'][0]['node']['compareAtPrice'] == null or
                                            $variant['variants']['edges'][0]['node']['compareAtPrice'] == '')
                                            0
                                        @else
                                            @php
                                                $variant_id = $variant['variants']['edges'][0]['node']['id'];
                                                $svid = str_replace('gid://shopify/ProductVariant/', '', $variant_id);

                                                $variant_local = \App\Models\Price::where('product_id', $svid)->first();
                                                if(!$variant_local){
                                                    $price_local = $variant_local->price;
                                                    $type = $variant_local->discount_type;
                                                    $discount = $variant_local->discount_value;
                                                    $discount_price = number_format($discount, 2, '.', '');
                                                    if($type == 'fixed'){
                                                        echo $discount_price . ' ' . $currency;
                                                    } else if ($type == 'percent'){
                                                        echo $discount_price . '%';
                                                    }
                                                } else {
                                                    $discount = 0;
                                                    echo $discount;
                                                }
                                            @endphp
                                        @endif
                                    </td>
                                @else
                                    <td>
                                        <div class="form-check">
                                            <label for="productCheckbox_{{ $product['id'] }}"></label><input class="form-check-input product-checkbox" type="checkbox" data-product-id="{{ $product['id'] }}" id="productCheckbox_{{ $product['id'] }}">
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $pid = $product['id'];
                                            $sort_id = str_replace('gid://shopify/Product/', '', $pid);
                                        @endphp
                                        {{ $sort_id }}
                                    </td>
                                    <td>
                                        @forelse($product['images']['nodes'] as $image)
                                            <img  alt="Image of {{ $product['title'] }}" src="{{ $image['url'] }}" width="40px" height="40px">
                                        @empty
                                            @php
                                                $no_img = "https://t3.ftcdn.net/jpg/04/62/93/66/360_F_462936689_BpEEcxfgMuYPfTaIAOC1tCDurmsno7Sp.jpg";
                                            @endphp
                                            <img  alt="Image of {{ $product['title'] }}" src="{{ $no_img }}" width="40px" height="40px">
                                        @endforelse
                                    </td>
                                    <td>{{ $product['title'] }}</td>
                                    <td>{{ $product['vendor'] }}</td>
                                    <td>
                                        <button class="btn btn-info btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#view-{{ $sort_id }}"
                                                aria-expanded="false" aria-controls="{{ $product['id'] }}">
                                            Show more
                                        </button>
                                    </td>
                                    <td></td>
                                @endif
                            @endif
                        @endforeach
                    </tr>

                    @foreach ($variants as $variant)
                        @if ($variant['id'] == $product['id'])
                            @foreach ($variant['variants']['edges'] as $variantEdge)
                                <tr class="collapse custom-tr" id="view-{{ $sort_id }}">
                                    <td>
                                        <div class="form-check">
                                            <label for="variantCheckbox_{{ $variant['id'] }}">
                                                <input class="form-check-input variant-checkbox" type="checkbox"
                                                       name="variant_id[]" value="{{ $variantEdge['node']['id'] }}"
                                                       data-product-id="{{ $product['id'] }}"
                                                       id="variantCheckbox_{{ $variant['id'] }}">
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $v_id = $variantEdge['node']['id'];
                                            $sort_vid = str_replace('gid://shopify/ProductVariant/', '', $v_id);
                                        @endphp
                                        {{ $sort_vid }}
                                    </td>
                                    <td>
                                        @if($variantEdge['node']['image'] !== null)
                                            <img  alt="Image of {{ $product['title'] }}" src="{{ $variantEdge['node']['image']['url'] }}" width="40px" height="40px">
                                        @else
                                            @php
                                                $no_img = "https://t3.ftcdn.net/jpg/04/62/93/66/360_F_462936689_BpEEcxfgMuYPfTaIAOC1tCDurmsno7Sp.jpg";
                                            @endphp
                                            <img  alt="Image of {{ $product['title'] }}" src="{{ $no_img }}" width="40px" height="40px">
                                        @endif
                                    </td>
                                    <td>{{ $variantEdge['node']['title'] }}</td>
                                    <td>{{ $product['vendor'] }}</td>
                                    <td>{{ $variantEdge['node']['price'] }} {{ $currency }}</td>
                                    <td>
                                        @php
                                            $variant_local = \App\Models\Price::where('product_id', $sort_vid)->first();
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
                                                $discount = 0;
                                                echo $discount;
                                            }
                                        @endphp
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                @endforeach
                </tbody>
            </table>
                <div class="d-flex">
                    <div class="justify-content-center input-group">
                        @if($pageInfo['hasPreviousPage'])
                            <button class="btn btn-outline-dark" onclick="window.location.href='{{ route('shopify.dashboard2', ['hasPreviousPage' => $pageInfo['hasPreviousPage'], 'startCursor' => $pageInfo['startCursor'], 'sortBy' => $sortBy ]) }}'">Previous</button>
                        @endif
                        @if($pageInfo['hasNextPage'])
                            <button class="btn btn-outline-dark" onclick="window.location.href='{{ route('shopify.dashboard2', ['hasNextPage' => $pageInfo['hasNextPage'], 'endCursor' => $pageInfo['endCursor'], 'sortBy' => $sortBy]) }}'">Next</button>
                        @endif
                    </div>

                    <div class="justify-content-end">
                        <form action="{{ route('shopify.dashboard2') }}" method="GET">
                            <div class="input-group">
                                <input type="number" min="1" class="form-control" name="sortBy" aria-label="Sort" placeholder="{{ $sortBy }}" value="{{ $sortBy }}" style="max-width: 5rem">
                                <button class="btn btn-outline-secondary" type="submit">OK</button>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
    </div>

    <script>
        const selectAllCheckbox = document.getElementById('selectAll');
        const productCheckboxes = Array.from(document.getElementsByClassName('product-checkbox'));
        const variantCheckboxes = Array.from(document.getElementsByClassName('variant-checkbox'));

        const modal = document.getElementById('myModal');
        const modalBody = document.getElementById('modalBody');
        const discountButton = document.getElementById('discountButton');
        const revertButton = document.getElementById('revertButton')
        const closeButton = document.getElementById('closeButton');
        const submitButton = document.getElementById('submitButton');

        const discountTypeSelect = document.getElementById('discountType');
        const discountValueInput = document.getElementById('discountValue');

        const revertModal = document.getElementById('revertModal');
        const revertForm = document.getElementById('revertForm');
        const revertVariantIdInput = document.getElementById('revertVariantIdInput');
        const revertSubmitButton = document.getElementById('revertSubmitButton');

        // Xử lý sự kiện khi checkbox all thay đổi
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = selectAllCheckbox.checked;
            productCheckboxes.forEach(function(checkbox) {
                checkbox.checked = isChecked;
            });
            variantCheckboxes.forEach(function(checkbox) {
                checkbox.checked = isChecked;
            });
            updateButtonStatus();
        });

        // Xử lý sự kiện khi checkbox product thay đổi
        productCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const isChecked = checkbox.checked;
                updateVariantCheckboxes(checkbox, isChecked);
                updateButtonStatus();
                updateSelectAllCheckbox();
            });
        });

        // Xử lý sự kiện khi checkbox variant thay đổi
        variantCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const productId = checkbox.dataset.productId;
                const productCheckbox = document.getElementById(`productCheckbox_${productId}`);
                updateProductCheckbox(productCheckbox);
                updateButtonStatus();
                updateSelectAllCheckbox();
            });
        });

        // Cập nhật trạng thái của các checkbox variant dựa trên checkbox product tương ứng
        function updateVariantCheckboxes(productCheckbox, isChecked) {
            const productId = productCheckbox.dataset.productId;
            const variantCheckboxes = Array.from(document.querySelectorAll(`.variant-checkbox[data-product-id="${productId}"]`));
            variantCheckboxes.forEach(function(variantCheckbox) {
                variantCheckbox.checked = isChecked;
            });
        }

        // Cập nhật trạng thái của checkbox product dựa trên trạng thái của các checkbox variant
        function updateProductCheckbox(productCheckbox) {
            const productId = productCheckbox.dataset.productId;
            const variantCheckboxes = Array.from(document.querySelectorAll(`.variant-checkbox[data-product-id="${productId}"]`));
            const isProductChecked = variantCheckboxes.every(function(checkbox) {
                return checkbox.checked;
            });
            productCheckbox.checked = isProductChecked;
        }

        // Cập nhật trạng thái của checkbox all trên th dựa trên trạng thái của các checkbox product và variant
        function updateSelectAllCheckbox() {
            const isAllChecked = productCheckboxes.every(function(checkbox) {
                return checkbox.checked;
            });
            const isAnyVariantUnchecked = variantCheckboxes.some(function(checkbox) {
                return !checkbox.checked;
            });
            selectAllCheckbox.checked = isAllChecked && !isAnyVariantUnchecked;
        }

        // Cập nhật trạng thái của nút
        function updateButtonStatus() {
            const isAnyCheckboxChecked = productCheckboxes.some(function(checkbox) {
                return checkbox.checked;
            });

            const isAnyVariantChecked = variantCheckboxes.some(function(checkbox) {
                return checkbox.checked;
            });

            if (isAnyCheckboxChecked || isAnyVariantChecked) {
                discountButton.disabled = false;
                revertButton.disabled = false;
            } else {
                discountButton.disabled = true;
                revertButton.disabled = true;
            }
        }

        discountButton.addEventListener('click', function() {
            const selectedVariants = getSelectedVariants();
            updateModalContent(selectedVariants);
            showModal();
        });

        submitButton.addEventListener('click', function() {
            const selectedVariants = getSelectedVariants();
            hideModal();
        });

        // Lấy danh sách các variant đã chọn
        function getSelectedVariants() {
            const selectedVariants = [];
            variantCheckboxes.forEach(function(checkbox) {
                if (checkbox.checked) {
                    selectedVariants.push(checkbox.value);
                }
            });
            return selectedVariants;
        }

        function updateModalContent(selectedVariants) {
            const variantIdInput = document.getElementById('variantIdInput');
            variantIdInput.value = selectedVariants.join(',');
        }

        // Hiển thị modal
        function showModal() {
            const myModal = new bootstrap.Modal(document.getElementById('myModal'));
            myModal.show();
        }

        // Ẩn modal
        closeButton.addEventListener('click', function() {
            hideModal();
        });

        // Ẩn modal
        function hideModal() {
            const myModal = document.getElementById('myModal');
            const bootstrapModal = bootstrap.Modal.getInstance(myModal);
            bootstrapModal.hide();
        }

        discountTypeSelect.addEventListener('change', function() {
            const selectedOption = discountTypeSelect.value;

            if (selectedOption === 'percent') {
                discountValueInput.min = '';
                discountValueInput.max = 99;
                discountValueInput.step = '0.01';
            } else {
                discountValueInput.min = 1;
                discountValueInput.max = '';
                discountValueInput.step = '0.01';
            }
        });


        revertButton.addEventListener('click', function() {
            const selectedVariants = getSelectedVariants();
            updateRevertModalContent(selectedVariants);
            showRevertModal();
        });

        revertSubmitButton.addEventListener('click', function() {
            const selectedVariants = getSelectedVariants();
            updateModalContent(selectedVariants);
            hideRevertModal();
        });

        // Cập nhật nội dung modal "Revert"
        function updateRevertModalContent(selectedVariants) {
            revertVariantIdInput.value = selectedVariants.join(',');
        }

        // Hiển thị modal "Revert"
        function showRevertModal() {
            const modal = new bootstrap.Modal(revertModal);
            modal.show();
        }

        // Ẩn modal "Revert"
        function hideRevertModal() {
            const modal = bootstrap.Modal.getInstance(revertModal);
            modal.hide();
        }

        var disableCheckbox = document.getElementById('collectionCheckbox');
        var elementsToDisable = document.querySelectorAll('#normalSearch input');
        var selectToEnable = document.getElementById('selectCollection');

        disableCheckbox.addEventListener('change', function() {
            if (this.checked) {
                elementsToDisable.forEach(function(element) {
                    element.disabled = true;
                    element.value = '';
                });
                selectToEnable.disabled = false;
            } else {
                elementsToDisable.forEach(function(element) {
                    element.disabled = false;
                });
                selectToEnable.disabled = true;
            }
        });

    </script>
@endsection
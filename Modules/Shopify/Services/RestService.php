<?php
    namespace Modules\Shopify\Services;

    use Illuminate\Http\Request;
    use Modules\Shopify\Repositories\IPriceRepo;
    use Modules\Shopify\Repositories\IStoreRepo;
    use Modules\Shopify\Traits\CalcDiscountTrait;
    use Modules\Shopify\Traits\FunctionTrait;
    use Modules\Shopify\Traits\GraphqlQueryTrait;
    use Modules\Shopify\Traits\RequestTrait;

    class RestService{
        use functionTrait;
        use RequestTrait;
        use CalcDiscountTrait;
        use GraphqlQueryTrait;

        protected IStoreRepo $storeRepo;
        protected IPriceRepo $priceRepo;

        public function __construct(IStoreRepo $storeRepo, IPriceRepo $priceRepo)
        {
            $this->storeRepo = $storeRepo;
            $this->priceRepo = $priceRepo;
        }

        public function RestDashboard($request)
        {
            $sortBy = 10;;
            if($request->sortBy && $request->sortBy != 0){
                $sortBy = intval(abs($request->input('sortBy', 10)));
            }
            $limit = "limit={$sortBy}";

            $page_info = '';
            if($request->input('pageInfo')){
                $page_info = "&page_info={$request->input('pageInfo')}";
                $sortBy = $request->input('sortBy') ? $request->input('sortBy') : 10;
                $limit = "&limit={$sortBy}";
            }

            $query = "";
            if($request->title or $request->vendor){
                if($request->input('title') !== null){
                    $title = $request->input('title');
                    $query .= "title=$title&";
                }
                if($request->input('vendor') !== null){
                    $vendor = $request->input('vendor');
                    $query .= "vendor=$vendor&";
                }
                $sort_by = $request->sortBy;
                $limit = "limit={$sort_by}";
            }

            $store = $this->storeRepo->getFirst();
            $endpoint = getShopifyUrlForStore("products.json?{$query}{$page_info}{$limit}", ['myshopify_domain' => $store->myshopify_domain]);
            //dd($endpoint);
            $headers = getShopifyHeadersForStore(['access_token' => $store->access_token]);
            $response = $this->makeAPICallToShopify('GET', $endpoint, $headers);
            $products = $response['body']['products'];
            //dd($headers, $endpoint, $response['headers']);

            $link = '';
            $pageInfo = [];
            if(array_key_exists('Link', $response['headers'])){
                $link = $response['headers']['Link'][0];
                //dd($link);

                # Get page_info
                $pageInfoPrevious = $pageInfoNext = '';
                if (preg_match('/<.*?page_info=([^>]*)>[^<]*?rel="previous"/', $link, $matches)) {
                    $pageInfoPrevious = $matches[1];
                }
                if (preg_match('/<.*?page_info=([^>]*)>[^<]*?rel="next"/', $link, $matches)) {
                    $pageInfoNext = $matches[1];
                }
                $pageInfo = ["previous" => $pageInfoPrevious, "next" => $pageInfoNext];
                //dd($pageInfo, array_keys($pageInfo)[0]);
            }


            # Get all vendor by graphql
            $endpoint2 = getShopifyUrlForStore("graphql.json", ['myshopify_domain' => $store->myshopify_domain]);
            $requestVendorAndTag = $this->GetVendorsAngTags();
            $responseVendorsAndTags = $this->makeGrapqlCallToShopify('POST', $endpoint2, $headers, $requestVendorAndTag);

            $vendors = $responseVendorsAndTags['body']['data']['shop']['productVendors']['edges'];
            //dd($products);

            $endpoint3 = getShopifyUrlForStore("shop.json?fields=currency", ['myshopify_domain' => $store->myshopify_domain]);
            $response3 = $this->makeAPICallToShopify('GET', $endpoint3, $headers);
            $currency = $response3['body']['shop']['currency'];

            return [
                'products' => $products,
                'link' => $link,
                'pageInfo' => $pageInfo,
                'sortBy' => $sortBy,
                'vendors' => $vendors,
                'query' => $query,
                'currency' => $currency
            ];
        }

        public function RestRevert(Request $request){
            //dd($request->all());
            $variantId = $request->input('variant_id');

            $store = $this->storeRepo->getFirst();
            $getFields = 'id,title,compare_at_price,price';
            $headers = getShopifyHeadersForStore(['access_token' => $store->access_token]);
            $successMessages = [];
            $warningMessages = [];
            if(strpos($variantId, ',')){
                $variantId = explode(',', $variantId);
                foreach ($variantId as $id){
                    $this->HandleRevert($id, $getFields, $headers, $store, $successMessages, $warningMessages);
                }
            }
            else {
                $this->HandleRevert($variantId, $getFields, $headers, $store, $successMessages, $warningMessages);
            }

            if (!empty($successMessages)) {
                session()->flash('success', $successMessages);
            }

            if (!empty($warningMessages)) {
                session()->flash('warning', $warningMessages);
            }
        }

        public function HandleRevert($id, $getFields, $headers, $store, &$successMessages, &$warningMessages){
            {
                $endpoint = getShopifyUrlForStore("variants/{$id}.json?fields={$getFields}", ['myshopify_domain' => $store->myshopify_domain]);
                $response = $this->makeAPICallToShopify('GET', $endpoint, $headers);

                $price = $response['body']['variant']['price'];
                $cap = $response['body']['variant']['compare_at_price'];
                $title = $response['body']['variant']['title'];
                $local_variant = $this->priceRepo->getById($id);
                if (!$local_variant) {
                    $warningMessages[] = "Failed to revert discount \"{$title}\"";
                } else {
                    $revert_price = $local_variant->price;
                    $revert_cap = $local_variant->compare_price;

                    $data = [
                        'variant' => [
                            'compare_at_price' => $revert_cap,
                            'price' => $revert_price
                        ]
                    ];
                    $endpoint2 = getShopifyUrlForStore("variants/{$id}.json", ['myshopify_domain' => $store->myshopify_domain]);
                    $response2 = $this->makeAPICallToShopify('PUT', $endpoint2, $headers, $data);
                    $this->priceRepo->delete($id);
                    $successMessages[] = "Discount reverted successfully variant \"{$title}\".";
                }
            }
        }

        public function ProcessDiscount(string $id, string $getFields, $store, array $headers, mixed $option, mixed $value, &$successMessages, &$test): void
        {
            $endpoint = getShopifyUrlForStore("variants/{$id}.json?fields={$getFields}", ['myshopify_domain' => $store->myshopify_domain]);
            $response = $this->makeAPICallToShopify('GET', $endpoint, $headers);

            $price = $response['body']['variant']['price'];
            $title = $response['body']['variant']['title'];
            $cap = $response['body']['variant']['compare_at_price'];

            $variant_local = $this->priceRepo->getById($id);
            $test[] = $variant_local;
            if (!$variant_local) {
                $data_local = [
                    'product_id' => $id,
                    'price' => $price,
                    'compare_price' => $cap
                ];
                $this->priceRepo->create($data_local);
                $compare_at_price = $price;
                $discount_price = $this->DiscountCal($option, $value, $price);
            } else {
                $price_local = $variant_local->price;
                $compare_at_price = $price_local;
                $discount_price = $this->DiscountCal($option, $value, $price_local);
            }
            $update_discount_type = [
                'discount_type' => $option,
                'discount_value' => $value
            ];
            $this->priceRepo->update($update_discount_type, $id);

            $data = [
                'variant' => [
                    'compare_at_price' => $compare_at_price,
                    'price' => $discount_price
                ]
            ];

            $endpoint2 = getShopifyUrlForStore("variants/{$id}.json", ['myshopify_domain' => $store->myshopify_domain]);
            $response2 = $this->makeAPICallToShopify('PUT', $endpoint2, $headers, $data);
            $successMessages[] = "Successfully discounting variant \"{$title}\".";
        }

        public function RestDiscount(Request $request): \Illuminate\Http\RedirectResponse
        {
            $variantId = $request->input('variant_id');
            $option = $request->input('option');
            $value = $request->input('value');

            $store = $this->storeRepo->getFirst();
            $getFields = 'id,title,compare_at_price,price';
            $headers = getShopifyHeadersForStore(['access_token' => $store->access_token]);
            $successMessages = [];
            $warningMessages = [];
            $test = [];
            if(strpos($variantId, ',')){
                $variantId = explode(',', $variantId);

                foreach($variantId as $id){
                    $this->ProcessDiscount($id, $getFields, $store, $headers, $option, $value, $successMessages, $test);
                }
            }
            else {
                $this->ProcessDiscount($variantId, $getFields, $store, $headers, $option, $value, $successMessages, $test);
            }
            //dd($prices);
            if (!empty($successMessages)) {
                session()->flash('success', $successMessages);
            }

            if (!empty($warningMessages)) {
                session()->flash('warning', $warningMessages);
            }
            //dd($test);
            return redirect()->back();
        }

        public function Index(): array
        {
            $store = $this->storeRepo->getFirst();
            $getFields = 'id,title,body_html';
            $endpoint = getShopifyUrlForStore("products.json?fields={$getFields}", ['myshopify_domain' => $store->myshopify_domain]);
            $headers = getShopifyHeadersForStore(['access_token' => $store->access_token]);
            return $this->makeAPICallToShopify('GET', $endpoint, $headers);
        }

        public function Create($req): array
        {
            $store = $this->storeRepo->getFirst();
            $productData = [
                'product' => $req->all()
            ];
            $endpoint = getShopifyUrlForStore("products.json", ['myshopify_domain' => $store->myshopify_domain]);
            $headers = getShopifyHeadersForStore(['access_token' => $store->access_token]);
            return $this->makeAPICallToShopify('POST', $endpoint, $headers, $productData);
        }

        public function Show($id): array
        {
            $store = $this->storeRepo->getFirst();
            $getFields = 'id,title,body_html';
            $endpoint = getShopifyUrlForStore("products/{$id}.json?fields={$getFields}", ['myshopify_domain' => $store->myshopify_domain]);
            $headers = getShopifyHeadersForStore(['access_token' => $store->access_token]);
            return $this->makeAPICallToShopify('GET', $endpoint, $headers);
        }

        public function Update($id, $req): array
        {
            $store = $this->storeRepo->getFirst();
            $productData = [
                'product' => $req->all()
            ];
            $endpoint = getShopifyUrlForStore("products/{$id}.json", ['myshopify_domain' => $store->myshopify_domain]);
            $headers = getShopifyHeadersForStore(['access_token' => $store->access_token]);
            return $this->makeAPICallToShopify('PUT', $endpoint, $headers, $productData);
        }

        public function Delete($id): array
        {
            $store = $this->storeRepo->getFirst();
            $endpoint = getShopifyUrlForStore("products/{$id}.json", ['myshopify_domain' => $store->myshopify_domain]);
            $headers = getShopifyHeadersForStore(['access_token' => $store->access_token]);
            return $this->makeAPICallToShopify('DELETE', $endpoint, $headers);
        }
    }
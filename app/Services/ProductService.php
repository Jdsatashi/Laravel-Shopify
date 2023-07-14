<?php
    namespace App\Services;

    use App\Http\Repository\IPriceRepo;
    use App\Http\Repository\IStoreRepo;
    use App\Http\Requests\ProductRequest;
    use App\Traits\FunctionTrait;
    use App\Traits\RequestTrait;
    use Illuminate\Http\Request;

    class ProductService{
        use functionTrait;
        use RequestTrait;

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

        public function GraphDashboard($request){
            //dd($request->all());
            $store = $this->storeRepo->getFirst();
            $headers = getShopifyHeadersForStore(['access_token' => $store->access_token]);
            //dd($headers);
            $endpoint = getShopifyUrlForStore("graphql.json", ['myshopify_domain' => $store->myshopify_domain]);


            $first_last = 'first';
            $sortBy = intval(abs($request->input('sortBy', 10)));

            if($request->sortBy == 0){
                $sortBy = 10;
            }
            $cursor = 'after';
            $link = 'null';

            # Paginate function
            if($request->hasNextPage){
                $first_last = 'first';
                $sortBy = intval(abs($request->input('sortBy', 10)));
                $cursor = 'after';
                $link = "\"$request->endCursor\"";
            }
            elseif ($request->hasPreviousPage){
                $first_last = 'last';
                $sortBy = intval(abs($request->input('sortBy', 10)));
                $cursor = 'before';
                $link = "\"$request->startCursor\"";
            }

            //dd($request->all());
            if($request->collection && $request->collectionValue != null){
                $requestCollection = $this->GetCollectionProducts($request->collectionValue, $first_last, $sortBy, $cursor, $link);
                $requestBody = $requestCollection;
                $response = $this->makeGrapqlCallToShopify('POST', $endpoint, $headers, $requestBody);
                //dd($response);
                $product = $response['body']['data']['collection']['products']['nodes'];
                $pageInfo = $response['body']['data']['collection']['products']['pageInfo'];

                # Get variant
                $requestVariant = $this->GetCollectionProductVariants($request->collectionValue,$first_last, $sortBy, $cursor, $link);
                $responseVariant = $this->makeGrapqlCallToShopify('POST', $endpoint, $headers, $requestVariant);
                //dd($responseVariant);
                $variants = $responseVariant['body']['data']['collection']['products']['nodes'];
            } else {
                # Search function
                $search_query = "\"\"";
                if ($request->has('title') or $request->has('vendor') or $request->has('tag')) {
                    $sortBy = intval(abs($request->input('sortBy', 10)));
                    $title = $request->input('title');
                    $vendor = $request->input('vendor');
                    $tag = $request->input('tag');

                    $query = "";

                    if ($title != null) {
                        $query .= "title:*$title* ";
                    }
                    if ($vendor != null) {
                        $query .= "vendor:*$vendor* ";
                    }
                    if ($tag != null) {
                        $tags = implode(" ", $tag);
                        $query .= "tag:*$tags* ";
                    }

                    $search_query = "\"$query\"";
                }
                //dd($search_query);

                $requestBody = $this->GetProduct($first_last, $sortBy, $cursor, $link, $search_query);
                //dd($requestBody);
                $response = $this->makeGrapqlCallToShopify('POST', $endpoint, $headers, $requestBody);
                //dd($response);
                $product = $response['body']['data']['products']['nodes'];
                $pageInfo = $response['body']['data']['products']['pageInfo'];

                # Get variant
                $requestVariant = $this->GetVariants($first_last, $sortBy, $cursor, $link, $search_query);
                $responseVariant = $this->makeGrapqlCallToShopify('POST', $endpoint, $headers, $requestVariant);
                $variants = $responseVariant['body']['data']['products']['nodes'];
            }


            # Get vendors and tags
            $requestVendorsAndTags = $this->GetVendorsAngTags();
            $responseVendorsAndTags = $this->makeGrapqlCallToShopify('POST', $endpoint, $headers, $requestVendorsAndTags);


            $vendors = $responseVendorsAndTags['body']['data']['shop']['productVendors']['edges'];
            $tags = $responseVendorsAndTags['body']['data']['shop']['productTags']['edges'];

            # Get currency
            $endpoint3 = getShopifyUrlForStore("shop.json?fields=currency", ['myshopify_domain' => $store->myshopify_domain]);
            $response3 = $this->makeAPICallToShopify('GET', $endpoint3, $headers);
            $currency = $response3['body']['shop']['currency'];

            $endpoint4 = $this->GetCollectionId();
            $responseCid = $this->makeGrapqlCallToShopify('POST', $endpoint, $headers, $endpoint4);
            $collection = $responseCid['body']['data']['collections']['nodes'];
            //dd($variants);
            return [
                'products' => $product,
                'pageInfo' => $pageInfo,
                'variants' => $variants,
                'sortBy' => $request->input('sortBy', $sortBy),
                'vendors' => $vendors,
                'tags' => $tags,
                'currency' => $currency,
                'collection' => $collection,
                'findingCollection' => boolval($request->collectionValue)
            ];
        }

        public function GraphDiscount($request)
        {
            //dd($request->all());
            $variantId = $request->input('variant_id');
            $option = $request->input('option');
            $value = $request->input('value');

            $store = $this->storeRepo->getFirst();
            $headers = getShopifyHeadersForStore(['access_token' => $store->access_token]);
            $successMessages = [];
            $warningMessages = [];

            $test = [];

            if(strpos($variantId, ',')){
                $variantId = explode(',', $variantId);
                foreach($variantId as $id){
                    $this->GraphGetDiscount($store, $id, $headers, $option, $value, $successMessages, $test);
                }
            }
            else {
                $this->GraphGetDiscount($store, $variantId, $headers, $option, $value, $successMessages, $test);
            }
            //dd($prices);
            if (!empty($successMessages)) {
                session()->flash('success', $successMessages);
            }

            if (!empty($warningMessages)) {
                session()->flash('warning', $warningMessages);
            }
            //dd($test);
        }

        public function GraphGetDiscount($store, $id, array $headers, mixed $option, mixed $value, &$successMessages, &$test = null): void
        {
            $endpoint = getShopifyUrlForStore("graphql.json", ['myshopify_domain' => $store->myshopify_domain]);
            $requestVariant = $this->GetProductVariant($id);
            $responseVariant = $this->makeGrapqlCallToShopify('POST', $endpoint, $headers, $requestVariant);
            $price = $responseVariant['body']['data']['productVariant']['price'];
            $cap = $responseVariant['body']['data']['productVariant']['compareAtPrice'];
            $title = $responseVariant['body']['data']['productVariant']['title'];


            $v_id = $responseVariant['body']['data']['productVariant']['id'];
            $prefix = 'gid://shopify/ProductVariant/';
            $vid = str_replace($prefix, '', $v_id);

            //$test[] = $vid;
            $price_local = null;
            $variant_local = $this->priceRepo->getById($vid);
            if(!$variant_local){
                $data = [
                    'product_id' => $vid,
                    'price' => $price,
                    'compare_price' => $cap
                ];
                $this->priceRepo->create($data);
                $compare_at_price = $price;
                $discount_price = $this->DiscountCal($option, $value, $price);
            } else {
                $price_local = $variant_local->price;
                $compare_at_price = $price_local;
                $discount_price = $this->DiscountCal($option, $value, $price_local);
            }
            $test[] = [$discount_price, $option, $value, $price_local, $this->priceRepo->getById($vid)];

            $update_discount_type = [
                'discount_type' => $option,
                'discount_value' => $value,
            ];
            $this->priceRepo->update($update_discount_type, $vid);

            $requestVariant2 = $this->GetUpdateVariants($id, $discount_price, $compare_at_price);
            $this->makeGrapqlCallToShopify('POST', $endpoint, $headers, $requestVariant2);
            $successMessages[] = "Successfully discounting variant \"{$title}\".";
        }


        public function GraphRevert(Request $request){
            //dd($request->all());
            $variantId = $request->input('variant_id');


            $store = $this->storeRepo->getFirst();
            $headers = getShopifyHeadersForStore(['access_token' => $store->access_token]);
            $test = [];
            if(strpos($variantId, ',')){
                $variantId = explode(',', $variantId);
                foreach($variantId as $id){
                    $this->HandleGraphRevert($store, $id, $headers, $successMessages, $warningMessages, $test);
                }
            }
            else {
                $this->HandleGraphRevert($store, $variantId, $headers, $successMessages, $warningMessages, $test);
            }
//        dd($test);
            if (!empty($successMessages)) {
                session()->flash('success', $successMessages);
            }

            if (!empty($warningMessages)) {
                session()->flash('warning', $warningMessages);
            }
        }

        public function HandleGraphRevert($store, $id, $headers, &$successMessages, &$warningMessages, &$test = null): void
        {
            $endpoint = getShopifyUrlForStore("graphql.json", ['myshopify_domain' => $store->myshopify_domain]);
            $requestVariant = $this->GetProductVariant($id);
            $responseVariant = $this->makeGrapqlCallToShopify('POST', $endpoint, $headers, $requestVariant);
            $title = $responseVariant['body']['data']['productVariant']['title'];

            $v_id = $responseVariant['body']['data']['productVariant']['id'];
            $prefix = 'gid://shopify/ProductVariant/';
            $vid = str_replace($prefix, '', $v_id);
            $test[] = $this->priceRepo->getById($vid);

            if(!$this->priceRepo->getById($vid)){
                $warningMessages[] = "Failed to revert discount \"{$title}\"";
            } else {
                $variant_local = $this->priceRepo->getById($vid);
                $price_local = $variant_local->price;
                $compare_at_price = $variant_local->compare_price;
                $discount_price = $price_local;

                $requestVariant2 = $this->GetUpdateVariants($id, $discount_price, $compare_at_price);
                $responseVariant2 = $this->makeGrapqlCallToShopify('POST', $endpoint, $headers, $requestVariant2);
                $this->priceRepo->delete($vid);
                $successMessages[] = "Discount reverts successfully variant \"{$title}\".";
            }
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

        protected function DiscountCal($option, $value, $price): string
        {
            $discount_price = 1;
            if($option == 'percent'){
                $discount_price = $price - ($price * $value / 100);
            }
            elseif ($option == 'fixed'){
                $discount_price = $price - $value;
            }
            $discount_price = number_format($discount_price, 2, '.', '');
            if ($discount_price <= 0) {
                $discount_price = '1';
            }
            return $discount_price;
        }

        /*
        * Under is REST api function
        */

        public function Index()
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

        public function GraphqlIndex(): \Illuminate\Http\JsonResponse
        {
            $store = $this->storeRepo->getFirst();

            $first_last = 'first';
            $number = 10;
            $cursor = 'after';
            $link = 'null';
            $search_query = "\"\"";

            $headers = getShopifyHeadersForStore(['access_token' => $store->access_token]);
            //dd($headers);
            $endpoint = getShopifyUrlForStore("graphql.json", ['myshopify_domain' => $store->myshopify_domain]);
            $requestBody = $this->GetProduct($first_last, $number, $cursor, $link, $search_query);
            //dd($requestBody);
            $response = $this->makeGrapqlCallToShopify('POST', $endpoint, $headers, $requestBody);

            return response()->json($response);
        }

        protected function GetProduct($first_last, $sortBy, $cursor, $link, $query = "\"\""): array
        {
            $query = "{
                      products({$first_last}: {$sortBy}, {$cursor}: {$link}, query: $query, reverse: true) {
                        nodes {
                          id
                          title
                          tags
                          vendor
                          images(first: 1) {
                              nodes {
                                  id
                                  url
                                }
                          }
                        }
                        pageInfo {
                          hasNextPage
                          endCursor
                          hasPreviousPage
                          startCursor
                        }
                      }
                    }";
            return ["query" => $query];
        }
        protected function GetVariants($first_last, $sortBy, $cursor, $link, $query = "\"\""): array
        {
            $query = "{
                      products({$first_last}: {$sortBy}, {$cursor}: {$link}, query: $query, reverse: true) {
                        nodes{
                          id
                          variants(first: 20) {
                            edges {
                              node {
                                id
                                title
                                price
                                compareAtPrice
                                image{
                                  id
                                  url
                                }
                              }
                            }
                          }
                        }
                      }
                    }";
            return ["query" => $query];
        }

        protected function GetVendorsAngTags(): array
        {
            $query = "{
                shop {
                        productVendors(first: 25){
                          edges{
                            node
                          }
                        }
                        productTags(first: 250){
                          edges{
                            node
                          }
                        }
                    }
                }";
            return ["query" => $query];
        }

        protected function GetProductVariant($vid): array
        {
            $query = "
               {
                  productVariant(id: \"{$vid}\") {
                        id
                        title
                        price
                        compareAtPrice
                      }
                }
        ";
            return ["query" => $query];
        }

        protected function GetUpdateVariants($variant_id, $price, $cap)
        {
            if($cap != ''){
                $compare_at_price = "\"$cap\"";
            }else{
                $compare_at_price = "null";
            }
            $input = "input: {
                    id: \"{$variant_id}\"
                    price: \"{$price}\"
                    compareAtPrice: {$compare_at_price}
                  }";
            $query = "mutation {
                            productVariantUpdate({$input}) {
                                productVariant {
                                    id
                                    title
                                    price
                                    compareAtPrice
                                }
                                userErrors {
                                    field
                                    message
                                }
                            }
                     }";
            return ["query" => $query];
        }
        protected function GetCollectionId(){
            $query = "{
                      collections(first: 10) {
                          nodes {
                            id
                            title
                          }
                        }
                    }";
            return ["query" => $query];
        }
        protected function GetCollectionProducts($collection_id, $first_last, $sortBy, $cursor, $link){
            $query = "{
                        collection(id:\"{$collection_id}\"){
                            id
                            products ({$first_last}: {$sortBy}, {$cursor}: {$link}, reverse: true){
                                nodes{
                                    id
                                    title
                                    vendor
                                    images(first: 1) {
                                      nodes {
                                          id
                                          url
                                        }
                                    }
                               }
                                pageInfo {
                                  hasNextPage
                                  endCursor
                                  hasPreviousPage
                                  startCursor
                               }
                           }                    
                       }
                   }";
            return ["query" => $query];
        }
        protected function GetCollectionProductVariants($collection_id, $first_last, $sortBy, $cursor, $link){
            $query = "{
                        collection(id:\"{$collection_id}\"){
                            id
                            products ({$first_last}: {$sortBy}, {$cursor}: {$link}, reverse: true){
                                nodes{
                                    id
                                    variants(first: 20) {
                                        edges {
                                          node {
                                            id
                                            title
                                            price
                                            compareAtPrice
                                            image{
                                              id
                                              url
                                            }
                                          }
                                        }
                                    }
                                }    
                           }                    
                       }
                   }";
            return ["query" => $query];
        }
    }
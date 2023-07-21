<?php
namespace Modules\Shopify\Services;

use Illuminate\Http\Request;
use Modules\Shopify\Repositories\IPriceRepo;
use Modules\Shopify\Repositories\IStoreRepo;
use Modules\Shopify\Traits\CalcDiscountTrait;
use Modules\Shopify\Traits\FunctionTrait;
use Modules\Shopify\Traits\GraphqlQueryTrait;
use Modules\Shopify\Traits\RequestTrait;

class GraphqlServices
{
    use functionTrait;
    use RequestTrait;
    use GraphqlQueryTrait;
    use CalcDiscountTrait;

    protected IStoreRepo $storeRepo;
    protected IPriceRepo $priceRepo;

    public function __construct(IStoreRepo $storeRepo, IPriceRepo $priceRepo)
    {
        $this->storeRepo = $storeRepo;
        $this->priceRepo = $priceRepo;
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
}
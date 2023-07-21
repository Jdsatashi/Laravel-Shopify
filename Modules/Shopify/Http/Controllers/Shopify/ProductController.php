<?php

namespace Modules\Shopify\Http\Controllers\Shopify;

use Illuminate\Http\Request;
use Modules\Shopify\Http\Controllers\Controller;
use Modules\Shopify\Http\Requests\ProductRequest;
use Modules\Shopify\Repositories\IPriceRepo;
use Modules\Shopify\Repositories\IStoreRepo;
use Modules\Shopify\Services\GraphqlServices;
use Modules\Shopify\Services\RestService;
use Modules\Shopify\Traits\FunctionTrait;
use Modules\Shopify\Traits\RequestTrait;

class ProductController extends Controller
{
    use functionTrait, RequestTrait;
    protected IStoreRepo $storeRepo;
    protected IPriceRepo $priceRepo;
    protected RestService $restService;
    protected GraphqlServices $graphqlService;
    /**
     * @var false
     */
    private bool $loadingSpinner;

    public function __construct(IStoreRepo $storeRepo, IPriceRepo $priceRepo, RestService $restService, GraphqlServices $graphqlService)
    {
        $this->storeRepo = $storeRepo;
        $this->priceRepo = $priceRepo;
        $this->restService = $restService;
        $this->graphqlService = $graphqlService;
        $this->loadingSpinner = false;
    }

    public function RestDashboard(Request $request){
        $this->loadingSpinner = true; // Hiển thị loading spinner
        $data = $this->restService->RestDashboard($request);
        return view('shopify::shopify.dashboard', array_merge($data, ['loadingSpinner' => $this->loadingSpinner]));
    }

    public function GraphDashboard(Request $request){
        $this->loadingSpinner = true; // Hiển thị loading spinner
        $data = $this->graphqlService->GraphDashboard($request);
        return view('shopify::shopify.Graphboard', array_merge($data, ['loadingSpinner' => $this->loadingSpinner]));
    }

    public function GraphDiscount(Request $request){
        $this->loadingSpinner = true; // Hiển thị loading spinner
        $this->graphqlService->GraphDiscount($request);
        return redirect()->back();
    }

    public function GraphRevert(Request $request){
        $this->loadingSpinner = true; // Hiển thị loading spinner
        $this->graphqlService->GraphRevert($request);
        return redirect()->back();
    }

    public function RevertVariantPrice(Request $request){
        $this->loadingSpinner = true; // Hiển thị loading spinner
        $this->restService->RestRevert($request);
        return redirect()->back();
    }

    public function DiscountVariantPrice(Request $request){
        $this->loadingSpinner = true; // Hiển thị loading spinner
        $this->restService->RestDiscount($request);
        return redirect()->back();
    }

    public function create(ProductRequest $request)
    {
        $response = $this->restService->create($request);

        return response()->json($response);
    }

    public function show($id)
    {
        $response = $this->restService->show($id);

        return response()->json($response);
    }

    public function update($id, ProductRequest $request)
    {
        $response = $this->restService->update($id, $request);

        return response()->json($response);
    }

    public function delete($id)
    {
        $response = $this->restService->delete($id);

        return response()->json($response);
    }

    public function GraphqlIndex(){
        $data = $this->graphqlService->GraphqlIndex();
        return response()->json($data);
    }
}

<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Http\Repository\IStoreRepo;
use App\Http\Repository\IPriceRepo;
use App\Http\Requests\ProductRequest;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Http\Request;
use App\Services\ProductService;

class ProductController extends Controller
{
    use functionTrait, RequestTrait;
    protected IStoreRepo $storeRepo;
    protected IPriceRepo $priceRepo;
    protected $productService;
    public function __construct(IStoreRepo $storeRepo, IPriceRepo $priceRepo, ProductService $productService)
    {
        $this->storeRepo = $storeRepo;
        $this->priceRepo = $priceRepo;
        $this->productService = $productService;
    }
    public function Dashboard(Request $request){
        $data = $this->productService->RestDashboard($request);
        return view('shopify.dashboard', $data);
    }

    public function DashboardGraph(Request $request){
        $data = $this->productService->GraphDashboard($request);
        return view('shopify.Graphboard', $data);
    }

    public function GraphDiscount(Request $request){
        $this->productService->GraphDiscount($request);
        return redirect()->back();
    }

    public function GraphRevert(Request $request){
        $this->productService->GraphRevert($request);
        return redirect()->back();
    }

    public function RevertVariantPrice(Request $request){
        $this->productService->RestRevert($request);
        return redirect()->back();
    }

    public function DiscountVariantPrice(Request $request){
        $this->productService->RestDiscount($request);
        return redirect()->back();
    }

    public function create(ProductRequest $request)
    {
        $response = $this->productService->create($request);

        return response()->json($response);
    }

    public function show($id)
    {
        $response = $this->productService->show($id);

        return response()->json($response);
    }

    public function update($id, ProductRequest $request)
    {
        $response = $this->productService->update($id, $request);

        return response()->json($response);
    }

    public function delete($id)
    {
        $response = $this->productService->delete($id);

        return response()->json($response);
    }

    public function GraphqlIndex(){
        $data = $this->productService->GraphqlIndex();
        return response()->json($data);
    }
}

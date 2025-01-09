<?php

namespace App\Http\Controllers;
use Auth;
use App\Order;
use App\Account;
use Carbon\Carbon;
use App\OrderDetail;
use App\UsersProduct;
use App\ProductReview;
use App\EcommerceAccount;
use Illuminate\Http\Request;
use App\ProductReviewSetting;
use App\Models\Promotion\Promotion;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductReviewController extends Controller
{

    public function accountId(){
        return Auth::user()->currentAccountId;
    }

    public function user()
    {
        return Auth::guard('ecommerceUsers')->user();
    }

    public function check($ext)
    {
        switch ($ext) {
            case 'jpg': return 'image'; break;
            case 'jpeg':
                return 'image';
                break;
            case 'gif':
                return 'image';
                break;
            case 'webp':
                return 'image';
                break;
            case 'png':
                return 'image';
                break;
            default:
                return false;
		}

    }

    public function storeInS3Disk($imageName)
    {
        $s3 = Storage::disk('s3');
        $s3ImageName = 'reviews/' . $imageName;
        $s3->put($s3ImageName,
            file_get_contents(storage_path('/app/public/reviews/' . $imageName)), 'public'
        );
		$cdnImageLink = app()->environment() === 'local'
            ? $s3->url($s3ImageName)
            : 'https://' . config('filesystems.disks.s3.bucket') . '/reviews/' . $imageName;

        return $cdnImageLink;
    }

    public function storeReviewImage(Request $request){
        if($request->file===null) {
            return response()->json([
                'images' => '',
            ]);
        };

        $validImage = $this->check($request->file->getClientOriginalExtension());
        if($validImage!=='image'){
             return response()->json([
                'status' => 'fail',
            ]);
        };
        $name = time() . "--" . $request->file->getClientOriginalName();
        $save = $request->file->storeAs("public/reviews",  $name);
        if(app()->environment(['local'])) {
            $path = Storage::url($save);
        }
        $path = $this->storeInS3Disk($name);
        $images = array();

        $images[] = $path;

        return response()->json([
            'status' => 'success',
            'message' => 'File is successfully uploaded',
            'images' => $images,
        ]);
    }

    public function removeReviewImage(Request $request){
        foreach ($request->images as $image) {
            $file = storage_path() . '/app/public/reviews/' . $image;
            if (file_exists($file)) {
                unlink($file);
            } else {
                // File not found.
            }
        }
        return response()->json([
            'status' => 'removed',
        ]);
    }

    public function createProductReview(Request $request, $accountId)
    {
        // $reviewed =  ProductReview::where(['account_id'=>$accountId, 'ecommerce_account_id'=>$this->user()->id, 'product_id'=>$request->productId])->first();
        // if( $reviewed!==null){
        //     return response()->json([
        //         'message' => 'reviewed',
        //     ]);
        // };
        $orders = $this->user()->processedContact->orders;
        $orderProduct = [];
        foreach($orders as $order){
            $orderDetail = OrderDetail
            ::with('usersProduct')->firstwhere(['order_id'=>$order->id, 'users_product_id'=>$request->productId,]);
            if($orderDetail) $orderProduct[]= $orderDetail;
        }
        if( count($orderProduct)<1){
            return response()->json([
                'message' => 'fail',
            ]);
        };
        ProductReview::create([
            'account_id' => $accountId,
            'product_id' => $request->productId,
            'ecommerce_account_id' => $request->ecommerceAccountId,
            'status' => $request->status,
            'star_rating' => $request->rating,
            'name' => $request->name,
            'review' => json_encode($request->review),
            'image_collection' => json_encode($request->images),
        ]);
        $settings = ProductReviewSetting::firstwhere([
            'account_id' => $accountId,
         ]);
        $promotionID = null;
        if($settings!==null){
            $promotionID = $settings->discount_type===1 ? $settings->promotion_id : null;
        }
        $promotionCode = $promotionID!==null ? Promotion::where([ 'account_id'=>$accountId, 'id'=>$promotionID])->first()?->discount_code : '';
        return response()->json([
			'promotion_code' => $promotionCode,
            'message' => 'success',
		]);
    }

    public function deleteProductReview($id)
    {
        $review = ProductReview::where([
            'id' => $id,
            'account_id' => $this->accountId(),
        ])->firstOrFail();

        $review->delete();

        $accountTimeZone = Account::find($this->accountId())->timeZone;

        $allReviews = ProductReview::where([
            'account_id' => $this->accountId(),
         ])->get();

         foreach($allReviews as $review){
             $reviewTime = new Carbon($review->created_at);
             $review->convertTime = $reviewTime->timezone($accountTimeZone)->isoFormat('MMMM D, YYYY \\at h:mm a');
             $review->productTitle = UsersProduct::where(['account_id'=>$this->accountId(), 'id'=>$review->product_id])->first()->productTitle;
             $review->image_collection = 'https://cdn.hypershapes.com/assets/product-default-image.png';
         }

        return response()->json([
            'message' => "Selected review deleted successfully!",
            'allReviews' => $allReviews,
        ]);
    }

    public function getCustomerInformation()
    {
        $user = Auth::guard('ecommerceUsers')->user();
        if($user!==null) $user->name = $user->processedContact->fname;

        return response()->json([
            'user' => $user,
        ]);
    }

    public function getProductReviews(){
        $reviews = ProductReview::where(['account_id'=>$this->accountId()])->get();
        $accountTimeZone = Account::find($this->accountId())->timeZone;
        foreach($reviews as $review){
            $product = UsersProduct::where(['account_id'=>$this->accountId(), 'id'=>$review->product_id])->first();
            $review->productSaleChannels = $product->saleChannels->map(function($e){return $e->type;});
            $reviewTime = new Carbon($review->created_at);
            $review->convertTime = $reviewTime->timezone($accountTimeZone)->isoFormat('MMMM D, YYYY \\at h:mm a');
            $review->productTitle = $product->productTitle;
            $review->productPath = $product->path;
            $review->review = json_decode($review->review);
            $review->image_collection = json_decode($review->image_collection);
        }
        return Inertia::render('product/pages/ProductReviewDashboard', compact('reviews'));

    }

    public function changeReviewStatus(Request $request, $reviewId){
        $review = ProductReview::where(['id'=>$reviewId, 'account_id'=>$this->accountId()])->first();
        $review->update([
            'status' => $request->status,
        ]);
        return response()->json([
            'status' => $review->status
        ]);
    }

    public function getCustomerPurchaseInfo($productId){
        $user = $this->user();
        $orderProduct = [];
        if($user!==null ) {
            $orders = $this->user()->processedContact->orders;
            foreach($orders as $order){
                $orderDetail = OrderDetail
                ::with('usersProduct')
                ->where(['order_id'=> $order->id, 'users_product_id'=> $productId,])
                ->first();
                if($orderDetail!==null) $orderProduct[]= $orderDetail;
            }
            // if(count($orderProduct)>0)
            //     $writeReview = ProductReview::where(['account_id'=>$accountId, 'ecommerce_account_id'=>$this->user()->id, 'product_id'=>$productId])->first()!==null;
        }
        return response()->json([
            'purchased'=> count($orderProduct)>0,
        ]);
    }

    public function getProductReviewContents($productId){
        $product = UsersProduct::find($productId);
        $accountId = $product->account_id;
        $user = $this->user();
        $writeReview=true;
        $orderProduct = [];
        if($user!==null ) {
            $user->name = $user->processedContact->fname;
            $orders = $this->user()->processedContact->orders;
            foreach($orders as $order){
                $orderDetail = OrderDetail
                ::with('usersProduct')
                ->where(['order_id'=> $order->id, 'users_product_id'=> $productId,])
                ->first();
                if($orderDetail!==null) $orderProduct[]= $orderDetail;
            }
            // if(count($orderProduct)>0)
            //     $writeReview = ProductReview::where(['account_id'=>$accountId, 'ecommerce_account_id'=>$this->user()->id, 'product_id'=>$productId])->first()!==null;
        }
        $accountTimeZone = Account::find($accountId)->timeZone;
        $paginateReviews
            = tap(ProductReview::ignoreAccountIdScope()->where(['account_id'=>$accountId, 'status'=>'published', 'product_id'=>$productId])
                ->orderBy('created_at', 'desc')->paginate(20),
                function($paginatedInstance) use($accountId, $accountTimeZone) {
                    return $paginatedInstance->getCollection()->transform(function ($review) use($accountId, $accountTimeZone) {
                        $review->review = json_decode($review->review);
                        $review->image_collection = json_decode($review->image_collection);
                        $review->productTitle = UsersProduct::where(['account_id'=>$accountId, 'id'=>$review->product_id])->first()->productTitle;
                        $reviewTime = new Carbon($review->created_at);
                        $review->createdAt = $reviewTime->timezone($accountTimeZone)->isoFormat('DD/MM/YYYY');
                        return $review;
                    });
                });
        $starRating = $this->starRating($accountId, $productId);

        return response()->json([
            'reviews' => $paginateReviews,
            'starRating' => $starRating,
            'purchased'=> count($orderProduct)>0,
            'writeReview'=> $writeReview,
            'user' => $user,
        ]);

    }

    public function starRating($accountId, $productId){
        $reviews = ProductReview::ignoreAccountIdScope()->where(['account_id'=>$accountId, 'status'=>'published', 'product_id'=>$productId])->get();
        $totalRate = 0;
        $avgRate = 0;
        foreach($reviews as $review){
            $totalRate += $review->star_rating;
            $review->productTitle = UsersProduct::where(['account_id'=>$accountId, 'id'=>$review->product_id])->first()->productTitle;
        }
        if($totalRate!==0) $avgRate = $totalRate/count($reviews);
        $starWidth = intval(($avgRate - intval($avgRate))* 100);
        $reviews = [
            'avgRate'=> $avgRate,
            'starWidth' => $starWidth,
            'reviewCount' => count($reviews),
        ];
        return $reviews;
    }

    public function getProductReviewSetting(){
        $settings = ProductReviewSetting::firstwhere([
            'account_id' => $this->accountId(),
         ]);
        $promotions = Promotion::where(['account_id'=>$this->accountId()])->where('promotion_status', '!=', 'expired') ->get();
        $selectedPromotion = null;
        if($settings)
        {
            $selectedPromotion = Promotion::where(['account_id'=>$this->accountId(), 'id'=>$settings->promotion_id,])
                ->where( 'promotion_status','!=','expired')->first();
        }
        if(!$selectedPromotion&&$settings){
            $selected = Promotion::where(['account_id'=>$this->accountId(), 'id'=>$settings->promotion_id,])->first();
            if($selected) {
                $selected['discount_code'] =  $selected['discount_code'] . ' (expired)' ;
                $promotions[] =  $selected;
            }
        };
        return Inertia::render('setting/pages/ProductReviewSettings', compact('settings', 'promotions'));
    }

    public function editProductReviewAppearance(Request $request){
        $settings = ProductReviewSetting::firstwhere([
            'account_id' => $this->accountId(),
         ]);

         if($settings!== null){
             $settings->update([
                 'layout_type' => $request->layoutType,
                 'display_review' => $request->displayReview,
             ]);
         } else{
             $settings = ProductReviewSetting::create([
                 'account_id' => $this->accountId(),
                 'layout_type' => $request->layoutType,
                 'display_review' => $request->displayReview,
            ]);
         }
        return response()->json(['message' => 'Successfully updated']);
     }

    public function editProductReviewSetting(Request $request){
        $settings = ProductReviewSetting::firstwhere([
            'account_id' => $this->accountId(),
         ]);

         if($settings!== null){
             $settings->update([
                 'auto_approve_type' => $request->autoApproveType,
                 'discount_type' => $request->discountType,
                 'promotion_id' => $request->promotionId,
                 'image_option' => $request->imageOption,
             ]);
         } else{
             $settings = ProductReviewSetting::create([
                 'account_id' => $this->accountId(),
                 'auto_approve_type' => $request->autoApproveType,
                 'discount_type' => $request->discountType,
                 'promotion_id' => $request->promotionId,
                 'image_option' => $request->imageOption,
            ]);
         }
        return response()->json(['message' => 'Successfully updated']);
     }
}


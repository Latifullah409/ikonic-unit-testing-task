<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        $order = Order::where('order_id',$data['order_id'])->first();
        if ($data['order_id'] == $order) 
        {
            return;
        }

        // Retrieve a merchant by domain
        $merchant = Merchant::merchantByDomain($data['merchant_domain'])->first();

        if (!$merchant->user->affiliate) 
        {
           $customer_email = $data['customer_email'];
           $customer_name = $data['customer_name'];
            $userAffiliate = $this->affiliateService->register($merchant,$customer_email,$customer_name,0.1);
        }
        // Retrieve affiliate by merchant's ID
        $affiliate = Affiliate::where('merchant_id',$merchant->id)->first();
    
        if (Order::whereExternalOrderId($data['order_id'])->exists()) 
        {
            return;
        }
        
        $data = [
            'subtotal' => $data['subtotal_price'],
            'affiliate_id' => $affiliate->id,
            'merchant_id' => $merchant->id,
            'commission_owed' => $data['subtotal_price'] * $affiliate->commission_rate,
            'external_order_id' => $data['order_id'],
        ];
       $order = Order::create($data);
    }
}

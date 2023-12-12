<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
{
    $merchantUser = User::where('email',$email)->first();

    if ($merchantUser?->merchant()->exists()) {
        throw new AffiliateCreateException('This email is already linked with a merchant.');
    }

    $existingAffiliate = Affiliate::whereUserAndMerchant(optional($merchantUser)?->id, $merchant->id)->first();
    if ($existingAffiliate) {
        throw new AffiliateCreateException('This email is already linked to another affiliate within the same merchant.');
    }

    $data = [
        'email' => $email,
        'name' => $name,
        'type' => 'default_type_value'
    ];
    $user = $merchantUser ?? User::create($data);

    $affiliate = Affiliate::updateOrCreate(
        [
            'user_id' => $user->id,
            'merchant_id' => $merchant->id
        ],
        [
            'commission_rate' => $commissionRate,
            'discount_code' => $this->apiService->createDiscountCode($merchant)['code']
        ]
    );

    Mail::send(new AffiliateCreated($affiliate));
    return $affiliate;
}

}

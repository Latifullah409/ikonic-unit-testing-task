<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // create user
        $user_data = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['api_key'],
            'type' => User::TYPE_MERCHANT,
        ];
        $user = User::create($user_data);

        // create merchant
        $merchant_data = [
            'domain' => $data['domain'],
            'display_name' => $data['name'],
            'user_id' => $user->id,
        ];
        $merchant = Merchant::create($merchant_data);

        return $merchant;
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
          $user ?? $user->update([
                'email' => $data['email'],
                'password' => $data['api_key']
            ]);
        
            $user?->merchant?->update([
                'domain' => $data['domain'],
                'display_name' => $data['name'],
            ]);
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // Retrieve a user record by its email
        $user = User::where('email',$email)->first();
        if (!$user) 
        {
            return null;
        }
        
        // Retrieve a merchant record by the user's ID
        $merchant = Merchant::whereUserId($user->id)->first();
        if (!$merchant) 
        {
            return null;
        }

        return $merchant;
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        $unpaid = Order::STATUS_UNPAID;
        foreach ($affiliate->orders as $order) 
        {
            if($order->payout_status == $unpaid)
            {
                dispatch(new PayoutOrderJob($order));
            }
        }
    }
}

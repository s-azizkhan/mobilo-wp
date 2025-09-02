<?php
namespace Mobilo\WpTheme\Services;


use Mobilo\WpTheme\Feature\NewPlansUserMeta;
use const Mobilo\WpTheme\Utils\CACHE_TYPE_TRANSIENT;

defined('ABSPATH') || exit;

class BackendSubscriptionService extends BackendApiManager
{
    public function __construct($gateway_id = 'APK')
    {
        parent::__construct($gateway_id);
        $this->set_cache_type(CACHE_TYPE_TRANSIENT);
    }

    public function getPrimarySubIdByUserId(string $user_id)
    {
        try {
            if (!$user_id) {
                return null;
            }
            $organization_id = NewPlansUserMeta::getOrgId($user_id);
            if (!$organization_id) {
                return null;
            }

            return $this->getPrimarySubIdByOrgId($organization_id);

        } catch (\Throwable $e) {
            mobilo_log(__METHOD__, $e->getMessage());
            return null;
        }
    }

    private function parseGetResult($result)
    {
        $id = 0;
        if (isset($result->id)) {

            $id = $result->id;
        } else {

            $id = $result['id'];
        }

        return $id;

    }

    public function getPrimarySubIdByOrgId(string $organization_id)
    {
        try {
            if (!$organization_id) {
                return null;
            }

            // Check cache
            $cache_key = "org_primary_sub_id_$organization_id";
            $cached_value = $this->get_cache($cache_key, 'org_subscription');

            if ($cached_value) {
                return $cached_value;
            }

            // If not cached, fetch from API
            $path = "/v1/subscriptions/primary/$organization_id";
            $res = $this->getData(trim($path));

            // mobilo_log(
            //     __METHOD__,
            //     "Primary subscription fetched for org_id: $organization_id, result: " . json_encode($res),
            //     'debug'
            // );
            if ($res['code'] !== 200) {
                return null;
            }

            $result = $res['data'];

            $sub_id = $this->parseGetResult($result);
            // Cache the result
            $this->set_cache($cache_key, $sub_id, MINUTE_IN_SECONDS, 'org_subscription');
            return $sub_id;
        } catch (\Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
            return null;
        }
    }

    public function syncSubscription($subscription_id)
    {
        try {
            mobilo_log(__METHOD__, ">>>>Subscription sync in process for $subscription_id", "info");
            if (!$subscription_id) {
                return null;
            }

            $path = "/v1/subscriptions/";
            $body = [
                "subscriptionId" => $subscription_id,
            ];
            $res = $this->postData($path, [], $body);

            if (!in_array($res['status'], $this->getSuccessCodes())) {
                mobilo_log(__METHOD__, "Subscription sync failed for $subscription_id");
                mobilo_log(__METHOD__, json_encode($res));
                return null;
            }

            $result = $res['data'];

            return $result;
        } catch (\Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
            return null;
        }
    }


}

<?php
namespace Mobilo\WpTheme\Interfaces;

defined('ABSPATH') || exit;

interface BackendServiceManagerInterface
{
    public static function fetchPlanDetails(string $sku): ?array;
    public static function fetchOrgDetails(string $orgId, int $user_id): ?object;
    public static function fetchPlanById(string $plan_id): ?object;
}
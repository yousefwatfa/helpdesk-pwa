<?php

namespace App\Services;

use App\Models\Branch;

/**
 * Names the branch a ticket was opened from, using the IP ranges set on each branch.
 */
class IpTaggingService
{
    public static function getTagByIp($ip)
    {
        if (!$ip) return '-';

        foreach (Branch::whereNotNull('ip_ranges')->get() as $branch) {
            foreach ($branch->ipRangeList() as $range) {
                if (self::ipInRange($ip, $range)) return $branch->name;
            }
        }

        return 'غير معروف';
    }

    public static function isValidRange(string $range): bool
    {
        $parts = explode('/', trim($range));

        return count($parts) === 2
            && filter_var($parts[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
            && ctype_digit($parts[1])
            && (int) $parts[1] <= 32;
    }

    private static function ipInRange($ip, $range)
    {
        if (!self::isValidRange($range) || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        list($subnet, $bits) = explode('/', $range);
        $ip_long = ip2long($ip);
        $subnet_long = ip2long($subnet);
        $mask = -1 << (32 - (int)$bits);
        return ($ip_long & $mask) == ($subnet_long & $mask);
    }
}

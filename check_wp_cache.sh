#!/bin/bash

DOMAIN=${1:-"http://localhost"}
if [[ $DOMAIN != http* ]]; then
    DOMAIN="https://$DOMAIN"
fi

# Script to check caching for wp-includes and wp-admin files
echo "=== WordPress Admin Area Caching Check ==="
echo ""

# Test wp-includes CSS file
echo "Testing wp-includes CSS file:"
curl -s -I "$DOMAIN/wp-includes/css/admin-bar.min.css" | grep -E "(cache-control|expires|x-turbo-charged-by)"
echo ""

# Test wp-includes JS file
echo "Testing wp-includes JS file:"
curl -s -I "$DOMAIN/wp-includes/js/jquery/jquery.min.js" | grep -E "(cache-control|expires|x-turbo-charged-by)"
echo ""

# Test wp-admin CSS file
echo "Testing wp-admin CSS file:"
curl -s -I "$DOMAIN/wp-admin/css/common.min.css" | grep -E "(cache-control|expires|x-turbo-charged-by)"
echo ""

# Test wp-admin JS file
echo "Testing wp-admin JS file:"
curl -s -I "$DOMAIN/wp-admin/js/common.min.js" | grep -E "(cache-control|expires|x-turbo-charged-by)"
echo ""

echo "=== Cache Headers Explanation ==="
echo "cache-control: public, max-age=XXXXX - File is cacheable, XXXXX is seconds"
echo "expires: DATE - File expires on this date"
echo "x-turbo-charged-by: LiteSpeed - Served by LiteSpeed with caching"

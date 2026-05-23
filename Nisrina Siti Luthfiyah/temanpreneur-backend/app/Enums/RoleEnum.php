<?php
namespace App\Enums;

enum RoleEnum: string
{
    case GUEST = 'guest';
    case BUYER = 'buyer';
    case SELLER = 'seller';
    case SELLER_PREMIUM = 'seller_premium';
    case ADMIN = 'admin';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
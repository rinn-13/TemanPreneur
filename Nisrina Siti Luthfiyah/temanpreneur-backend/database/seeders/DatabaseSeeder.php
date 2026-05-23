<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Business;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        echo "\n📚 Starting Database Seeder...\n";

        // ============ ADMIN ============
        echo "👨‍💼 Creating Admin...\n";
        $admin = User::updateOrCreate(
            ['email' => 'admin@smk.belajar.id'],
            [
                'name' => 'Admin Platform',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'roles' => ['admin'],
                'class' => null,
                'status' => 'active',
                'is_verified' => true,
            ]
        );

        // ============ TEST USER ============
        echo "🧪 Creating Test User...\n";
        $testUser = User::updateOrCreate(
            ['email' => 'test@gmail.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'buyer',
                'roles' => ['buyer'],
                'class' => null,
                'status' => 'active',
                'is_verified' => true,
            ]
        );

        // ============ BUYERS (ONLY BUYER) ============
        $buyer1 = User::updateOrCreate(
            ['email' => 'buyer@smk.belajar.id'],
            [
                'name' => 'Budi Pembeli',
                'password' => Hash::make('password'),
                'role' => 'buyer',
                'roles' => ['buyer'],
                'class' => 'XII IPA 1',
                'status' => 'active',
                'is_verified' => true,
            ]
        );

        $buyer2 = User::updateOrCreate(
            ['email' => 'siti.buyer@smk.belajar.id'],
            [
                'name' => 'Siti Nurhaliza',
                'password' => Hash::make('password'),
                'role' => 'buyer',
                'roles' => ['buyer'],
                'class' => 'XI IPA 2',
                'status' => 'active',
                'is_verified' => true,
            ]
        );

        $buyer3 = User::updateOrCreate(
            ['email' => 'ahmad.buyer@smk.belajar.id'],
            [
                'name' => 'Ahmad Wijaya',
                'password' => Hash::make('password'),
                'role' => 'buyer',
                'roles' => ['buyer'],
                'class' => 'X IPA 1',
                'status' => 'active',
                'is_verified' => true,
            ]
        );

        // ============ SELLERS WITH BUSINESSES ============
        echo "👔 Creating Sellers with Businesses...\n";
        
        // SELLER 1: Mama Malya - APPROVED
        $seller1 = User::updateOrCreate(
            ['email' => 'mama.malya@smk.belajar.id'],
            [
                'name' => 'Mama Malya',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'roles' => ['buyer', 'seller'],
                'class' => 'XI IPA 1',
                'status' => 'active',
                'is_verified' => true,
            ]
        );
        
        $business1 = Business::updateOrCreate(
            ['user_id' => $seller1->id],
            [
                'name' => 'Mama Malya Fashion',
                'description' => 'Kerudung dan hijab berkualitas tinggi dengan bahan pilihan',
                'category' => 'fashion',
                'phone' => '6281234567890',
                'is_verified' => true,
                'is_premium' => false,
                'status' => 'approved',
                'processed_at' => now()->subDays(15),
            ]
        );

        // SELLER 2: Snack Pintar - APPROVED
        $seller2 = User::updateOrCreate(
            ['email' => 'snack.pintar@smk.belajar.id'],
            [
                'name' => 'Rini Handoko',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'roles' => ['buyer', 'seller'],
                'class' => 'XI IPA 2',
                'status' => 'active',
                'is_verified' => true,
            ]
        );
        
        $business2 = Business::updateOrCreate(
            ['user_id' => $seller2->id],
            [
                'name' => 'Snack Pintar',
                'description' => 'Snack sehat dan lezat, dibuat fresh setiap hari',
                'category' => 'kuliner',
                'phone' => '6281234567891',
                'is_verified' => true,
                'is_premium' => false,
                'status' => 'approved',
                'processed_at' => now()->subDays(12),
            ]
        );

        // SELLER 3: Eco Woven - PENDING
        $seller3 = User::updateOrCreate(
            ['email' => 'eco.woven@smk.belajar.id'],
            [
                'name' => 'Siti Nurhayati',
                'password' => Hash::make('password'),
                'role' => 'buyer',
                'roles' => ['buyer'],
                'class' => 'X IPA 3',
                'status' => 'active',
                'is_verified' => false,
            ]
        );
        
        $business3 = Business::updateOrCreate(
            ['user_id' => $seller3->id],
            [
                'name' => 'Eco Woven Crafts',
                'description' => 'Produk kerajinan ramah lingkungan dari bahan daur ulang',
                'category' => 'kerajinan',
                'phone' => '6281234567892',
                'is_verified' => false,
                'is_premium' => false,
                'status' => 'pending',
                'created_at' => now()->subDays(3),
            ]
        );

        // SELLER 4: Pixel Store - APPROVED
        $seller4 = User::updateOrCreate(
            ['email' => 'pixel.store@smk.belajar.id'],
            [
                'name' => 'Adi Pratama',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'roles' => ['buyer', 'seller'],
                'class' => 'XII IPA 2',
                'status' => 'active',
                'is_verified' => true,
            ]
        );
        
        $business4 = Business::updateOrCreate(
            ['user_id' => $seller4->id],
            [
                'name' => 'Pixel Store',
                'description' => 'Jasa desain grafis, editing video, dan template digital',
                'category' => 'digital',
                'phone' => '6281234567893',
                'is_verified' => true,
                'is_premium' => true,
                'status' => 'approved',
                'processed_at' => now()->subDays(8),
            ]
        );

        // SELLER 5: Aksesoris Kita - REJECTED
        $seller5 = User::updateOrCreate(
            ['email' => 'aksesoris.kita@smk.belajar.id'],
            [
                'name' => 'Dewi Lestari',
                'password' => Hash::make('password'),
                'role' => 'buyer',
                'roles' => ['buyer'],
                'class' => 'XI IPS 1',
                'status' => 'active',
                'is_verified' => false,
            ]
        );
        
        $business5 = Business::updateOrCreate(
            ['user_id' => $seller5->id],
            [
                'name' => 'Aksesoris Kita',
                'description' => 'Aksesoris fashion dengan desain unik dan trendy',
                'category' => 'aksesoris',
                'phone' => '6281234567894',
                'is_verified' => false,
                'is_premium' => false,
                'status' => 'rejected',
                'rejection_reason' => 'Produk sejenis sudah banyak. Mohon pertimbangkan diferensiasi yang lebih jelas.',
                'processed_at' => now()->subDays(5),
            ]
        );

        // SELLER 6: Beauty Skin Lab - PENDING
        $seller6 = User::updateOrCreate(
            ['email' => 'beauty.skinlab@smk.belajar.id'],
            [
                'name' => 'Cantika Putri',
                'password' => Hash::make('password'),
                'role' => 'buyer',
                'roles' => ['buyer'],
                'class' => 'XII IPA 1',
                'status' => 'active',
                'is_verified' => false,
            ]
        );
        
        $business6 = Business::updateOrCreate(
            ['user_id' => $seller6->id],
            [
                'name' => 'Beauty Skin Lab',
                'description' => 'Skincare organik dengan bahan alami pilihan untuk kulit Indonesia',
                'category' => 'lainnya',
                'phone' => '6287654321234',
                'is_verified' => false,
                'is_premium' => false,
                'status' => 'pending',
                'created_at' => now()->subDays(1),
            ]
        );

        // SELLER 7: Baur Handmade - APPROVED
        $seller7 = User::updateOrCreate(
            ['email' => 'baur.handmade@smk.belajar.id'],
            [
                'name' => 'Baur Rahman',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'roles' => ['buyer', 'seller'],
                'class' => 'XI IPS 2',
                'status' => 'active',
                'is_verified' => true,
            ]
        );
        
        $business7 = Business::updateOrCreate(
            ['user_id' => $seller7->id],
            [
                'name' => 'Baur Handmade',
                'description' => 'Produk kerajinan tangan dari kulit dan bahan daur ulang',
                'category' => 'kerajinan',
                'phone' => '6289876543210',
                'is_verified' => true,
                'is_premium' => false,
                'status' => 'approved',
                'processed_at' => now()->subDays(10),
            ]
        );

        // SELLER 8: FastFood Nusantara - PENDING
        $seller8 = User::updateOrCreate(
            ['email' => 'fastfood.nusantara@smk.belajar.id'],
            [
                'name' => 'Rendra Kusuma',
                'password' => Hash::make('password'),
                'role' => 'buyer',
                'roles' => ['buyer'],
                'class' => 'X IPS 1',
                'status' => 'active',
                'is_verified' => false,
            ]
        );
        
        $business8 = Business::updateOrCreate(
            ['user_id' => $seller8->id],
            [
                'name' => 'FastFood Nusantara',
                'description' => 'Makanan cepat saji dengan cita rasa nusantara autentik',
                'category' => 'kuliner',
                'phone' => '6285555666777',
                'is_verified' => false,
                'is_premium' => false,
                'status' => 'pending',
                'created_at' => now()->subDays(2),
            ]
        );

        // ============ CREATE PRODUCTS ============
        echo "📦 Creating Products...\n";

        // Products for Business 1: Mama Malya Fashion
        collect([
            ['name' => 'Hijab Katun Lembut', 'description' => 'Hijab premium dari bahan katun 100% berkualitas tinggi', 'price' => 35000, 'stock' => 50, 'total_sold' => 214],
            ['name' => 'Kerudung Satin Glossy', 'description' => 'Kerudung dengan finishing satin yang elegan dan nyaman dipakai', 'price' => 45000, 'stock' => 30, 'total_sold' => 189],
            ['name' => 'Hijab Printed Motif', 'description' => 'Hijab dengan motif unik dan warna-warna cerah', 'price' => 40000, 'stock' => 45, 'total_sold' => 156],
            ['name' => 'Pashmina Polos Premium', 'description' => 'Pashmina polos dengan tekstur lembut dan nyaman', 'price' => 50000, 'stock' => 25, 'total_sold' => 98],
        ])->each(fn($p) => Product::create(array_merge($p, ['business_id' => $business1->id])));

        // Products for Business 2: Snack Pintar
        collect([
            ['name' => 'Brownies Coklat Premium', 'description' => 'Brownies dengan coklat premium, lembut dan lezat', 'price' => 25000, 'stock' => 100, 'total_sold' => 312],
            ['name' => 'Cookies Oatmeal Sehat', 'description' => 'Cookies dari oat dengan topping almond yang renyah', 'price' => 20000, 'stock' => 80, 'total_sold' => 245],
            ['name' => 'Nasi Kotak Lengkap', 'description' => 'Nasi dengan lauk pauk lengkap, cocok untuk acara', 'price' => 35000, 'stock' => 40, 'total_sold' => 128],
            ['name' => 'Kue Bolu Lumpur', 'description' => 'Kue bolu dengan rasa coklat yang nikmat', 'price' => 30000, 'stock' => 60, 'total_sold' => 89],
            ['name' => 'Banana Bread', 'description' => 'Roti pisang homemade dengan bahan-bahan pilihan', 'price' => 22000, 'stock' => 75, 'total_sold' => 167],
        ])->each(fn($p) => Product::create(array_merge($p, ['business_id' => $business2->id])));

        // Products for Business 3: Eco Woven Crafts (PENDING)
        collect([
            ['name' => 'Tas Daur Ulang Material', 'description' => 'Tas tangan dari material daur ulang berkualitas tinggi', 'price' => 65000, 'stock' => 20, 'total_sold' => 0],
            ['name' => 'Dompet Ramah Lingkungan', 'description' => 'Dompet dari kulit daur ulang yang tahan lama', 'price' => 40000, 'stock' => 35, 'total_sold' => 0],
        ])->each(fn($p) => Product::create(array_merge($p, ['business_id' => $business3->id])));

        // Products for Business 4: Pixel Store
        collect([
            ['name' => 'Desain Logo Custom', 'description' => 'Jasa desain logo profesional dengan revisi unlimited', 'price' => 150000, 'stock' => 100, 'total_sold' => 45],
            ['name' => 'Template Poster Canva', 'description' => 'Bundle 50+ template poster siap pakai di Canva', 'price' => 35000, 'stock' => 999, 'total_sold' => 234],
            ['name' => 'Editing Video Profesional', 'description' => 'Jasa edit video untuk konten media sosial', 'price' => 200000, 'stock' => 100, 'total_sold' => 28],
            ['name' => 'Desain Undangan Digital', 'description' => 'Undangan pernikahan/acara dalam format digital', 'price' => 50000, 'stock' => 100, 'total_sold' => 62],
        ])->each(fn($p) => Product::create(array_merge($p, ['business_id' => $business4->id])));

        // Products for Business 6: Beauty Skin Lab (PENDING)
        collect([
            ['name' => 'Facial Wash Organik', 'description' => 'Pembersih wajah dari bahan organik alami', 'price' => 55000, 'stock' => 40, 'total_sold' => 0],
            ['name' => 'Moisturizer Sheet Mask', 'description' => 'Masker wajah kertas dengan kandungan kelembab tinggi', 'price' => 35000, 'stock' => 60, 'total_sold' => 0],
        ])->each(fn($p) => Product::create(array_merge($p, ['business_id' => $business6->id])));

        // Products for Business 7: Baur Handmade
        collect([
            ['name' => 'Tas Tangan Kulit Asli', 'description' => 'Tas tangan dari kulit asli dengan jahitan berkualitas', 'price' => 185000, 'stock' => 15, 'total_sold' => 23],
            ['name' => 'Dompet Kulit Custom', 'description' => 'Dompet dari kulit asli bisa dikustomisasi sesuai nama', 'price' => 95000, 'stock' => 25, 'total_sold' => 18],
            ['name' => 'Ikat Pinggang Kulit', 'description' => 'Ikat pinggang dari kulit berkualitas dengan desain minimalis', 'price' => 75000, 'stock' => 35, 'total_sold' => 42],
        ])->each(fn($p) => Product::create(array_merge($p, ['business_id' => $business7->id])));

        // Products for Business 8: FastFood Nusantara (PENDING)
        collect([
            ['name' => 'Nasi Goreng Kampung', 'description' => 'Nasi goreng tradisional dengan resep rahasia nenek', 'price' => 28000, 'stock' => 50, 'total_sold' => 0],
            ['name' => 'Mie Ayam Special', 'description' => 'Mie ayam dengan kuah tradisional yang gurih', 'price' => 18000, 'stock' => 60, 'total_sold' => 0],
        ])->each(fn($p) => Product::create(array_merge($p, ['business_id' => $business8->id])));

        // ============ CREATE ORDERS ============
        echo "📋 Creating Orders with Items...\n";

        // Order 1: Buyer 1 purchased from Mama Malya & Snack Pintar
        $order1 = \App\Models\Order::create([
            'buyer_id' => $buyer1->id,
            'status' => 'selesai',
            'payment_method' => 'transfer',
            'shipping_cost' => 15000,
            'total_amount' => 105000,
            'shipping_address' => 'Jl. Merdeka No. 123, Bandung',
            'shipping_phone' => '081234567890',
            'shipping_name' => 'Budi Pembeli',
            'created_at' => now()->subDays(10),
        ]);

        // Order items
        \App\Models\OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => Product::where('name', 'Hijab Katun Lembut')->first()->id,
            'quantity' => 2,
            'price' => 35000,
            'subtotal' => 70000,
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => Product::where('name', 'Brownies Coklat Premium')->first()->id,
            'quantity' => 1,
            'price' => 25000,
            'subtotal' => 25000,
        ]);

        // Tracking for Order 1
        \App\Models\OrderTracking::create([
            'order_id' => $order1->id,
            'status' => 'diproses',
            'updated_by' => $seller1->id,
            'created_at' => now()->subDays(9),
        ]);
        \App\Models\OrderTracking::create([
            'order_id' => $order1->id,
            'status' => 'dikemas',
            'updated_by' => $seller1->id,
            'created_at' => now()->subDays(8),
        ]);
        \App\Models\OrderTracking::create([
            'order_id' => $order1->id,
            'status' => 'diantarkan',
            'updated_by' => $seller1->id,
            'created_at' => now()->subDays(5),
        ]);
        \App\Models\OrderTracking::create([
            'order_id' => $order1->id,
            'status' => 'selesai',
            'updated_by' => $seller1->id,
            'created_at' => now()->subDays(2),
        ]);

        // Order 2: Buyer 2 purchased from Pixel Store
        $order2 = \App\Models\Order::create([
            'buyer_id' => $buyer2->id,
            'status' => 'selesai',
            'payment_method' => 'cod',
            'shipping_cost' => 10000,
            'total_amount' => 185000,
            'shipping_address' => 'Jl. Ahmad Yani No. 456, Jakarta',
            'shipping_phone' => '082345678901',
            'shipping_name' => 'Siti Nurhaliza',
            'created_at' => now()->subDays(7),
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => Product::where('name', 'Desain Logo Custom')->first()->id,
            'quantity' => 1,
            'price' => 150000,
            'subtotal' => 150000,
        ]);

        // Tracking for Order 2
        foreach (['diproses', 'dikemas', 'diantarkan', 'selesai'] as $status) {
            \App\Models\OrderTracking::create([
                'order_id' => $order2->id,
                'status' => $status,
                'updated_by' => $business4->user_id,
                'created_at' => now()->subDays(7 - (['diproses' => 1, 'dikemas' => 2, 'diantarkan' => 4, 'selesai' => 5][
$status] ?? 0)),
            ]);
        }

        // Order 3: Buyer 1 - Pending order
        $order3 = \App\Models\Order::create([
            'buyer_id' => $buyer1->id,
            'status' => 'dikemas',
            'payment_method' => 'transfer',
            'shipping_cost' => 12000,
            'total_amount' => 87000,
            'shipping_address' => 'Jl. Merdeka No. 123, Bandung',
            'shipping_phone' => '081234567890',
            'shipping_name' => 'Budi Pembeli',
            'created_at' => now()->subDays(3),
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order3->id,
            'product_id' => Product::where('name', 'Kerudung Satin Glossy')->first()->id,
            'quantity' => 1,
            'price' => 45000,
            'subtotal' => 45000,
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order3->id,
            'product_id' => Product::where('name', 'Cookies Oatmeal Sehat')->first()->id,
            'quantity' => 2,
            'price' => 20000,
            'subtotal' => 40000,
        ]);

        // ============ CREATE REVIEWS ============
        echo "⭐ Creating Reviews...\n";

        \App\Models\Review::create([
            'order_id' => $order1->id,
            'rating' => 5,
            'comment' => 'Produk sangat bagus, kualitas sesuai ekspektasi. Pengiriman cepat dan packaging rapi!',
            'created_at' => now()->subDays(1),
        ]);

        \App\Models\Review::create([
            'order_id' => $order2->id,
            'rating' => 4,
            'comment' => 'Desain logo oke, tapi revisi agak lama. Tapi hasilnya memuaskan.',
            'created_at' => now()->subDays(1),
        ]);

        // ============ CREATE ACCESS CODES ============
        echo "🔐 Creating Access Codes for Buyers...\n";

        $accessCodes = [
            'ACCESS001', 'ACCESS002', 'ACCESS003', 'ACCESS004', 'ACCESS005',
            'REGISTER2024', 'BUYER2024', 'NEWUSER001', 'SCHOOLCODE', 'TEMANPRENEUR',
        ];

        foreach ($accessCodes as $code) {
            \App\Models\AccessCode::create([
                'code' => $code,
                'quantity' => 1,
                'is_used' => false,
                'used_by' => null,
                'used_at' => null,
                'expires_at' => now()->addMonths(3),
                'description' => 'Access code untuk registrasi pembeli',
                'created_by' => $admin->id,
            ]);
        }

        // Mark some as used
        \App\Models\AccessCode::whereIn('code', ['ACCESS001', 'ACCESS002'])->update([
            'is_used' => true,
            'used_by' => $buyer1->id,
            'used_at' => now()->subDays(30),
        ]);

        // ============ END OF SEEDER ============
        echo "\n✅ Database Seeder completed successfully!\n";
        echo "📊 Summary:\n";
        echo "   - 1 Admin\n";
        echo "   - 3 Buyers (only buyer)\n";
        echo "   - 8 Sellers\n";
        echo "   - 4 Approved Sellers with businesses\n";
        echo "   - 2 Pending Sellers with businesses\n";
        echo "   - 1 Rejected Seller with business\n";
        echo "   - 1 Eco Woven (pending with products)\n";
        echo "   - 28 Products\n";
        echo "   - 3 Orders with items & tracking\n";
        echo "   - 2 Reviews\n";
        echo "   - 10 Access Codes\n\n";
    }
}

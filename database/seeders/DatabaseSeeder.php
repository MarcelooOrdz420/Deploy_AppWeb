<?php

namespace Database\Seeders;

use App\Models\InventoryMovement;
use App\Models\LoginHistory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@eldorado.pe'],
            [
                'name' => 'Administracion',
                'phone' => '999888777',
                'role' => 'admin',
                'password' => Hash::make('admin12345'),
            ]
        );

        $products = [
            ['name' => '1/4 Pollo a la Brasa', 'category' => 'pollos', 'description' => 'Con papas y ensalada.', 'price' => 18.90, 'image_url' => '/images/products/pollos/cuarto.jpg'],
            ['name' => '1/2 Pollo a la Brasa', 'category' => 'pollos', 'description' => 'Ideal para compartir.', 'price' => 34.90, 'image_url' => '/images/products/pollos/medio_pollo.jpg'],
            ['name' => 'Pollo Entero a la Brasa', 'category' => 'pollos', 'description' => 'Con papas familiares y cremas.', 'price' => 64.90, 'image_url' => '/images/products/pollos/pollo_familiar.jpg'],
            ['name' => 'Mostrito Tradicional', 'category' => 'pollos', 'description' => '1/4 de pollo con arroz chaufa.', 'price' => 24.90, 'image_url' => '/images/products/pollos/mostrito.jpg'],
            ['name' => 'Mega Combo Familiar', 'category' => 'pollos', 'description' => 'Pollo entero + papas + ensalada + gaseosa 1.5L.', 'price' => 79.90, 'image_url' => '/images/products/pollos/mega-combo.jpg'],

            ['name' => 'Parrilla Mixta', 'category' => 'parrillas', 'description' => 'Churrasco, chorizo, anticucho y papas.', 'price' => 46.90, 'image_url' => '/images/products/parrillas/parrillada-mixta.jpg'],
            ['name' => 'Anticuchos x 4', 'category' => 'parrillas', 'description' => 'Corazon de res a la parrilla.', 'price' => 28.90, 'image_url' => '/images/products/parrillas/anticuchos.jpg'],
            ['name' => 'Churrasco a la Parrilla', 'category' => 'parrillas', 'description' => 'Lomo a la parrilla con guarnicion.', 'price' => 36.90, 'image_url' => '/images/products/parrillas/parrilla_arge.jpg'],
            ['name' => 'Alitas BBQ x 8', 'category' => 'parrillas', 'description' => 'Alitas glaseadas en salsa BBQ.', 'price' => 29.90, 'image_url' => '/images/products/parrillas/alitas-bbq.jpg'],
            ['name' => 'Brochetas de Pollo', 'category' => 'parrillas', 'description' => 'Brochetas con vegetales grillados.', 'price' => 27.90, 'image_url' => '/images/products/parrillas/pollo_parrilla.jpg'],

            ['name' => 'Inca Kola Personal 500ml', 'category' => 'bebidas', 'description' => 'Bebida personal helada.', 'price' => 5.50, 'image_url' => '/images/products/bebidas/inca-kola.jpg'],
            ['name' => 'Coca-Cola Personal 500ml', 'category' => 'bebidas', 'description' => 'Bebida personal helada.', 'price' => 5.50, 'image_url' => '/images/products/bebidas/coca-cola.jpg'],
            ['name' => 'Sprite Personal 500ml', 'category' => 'bebidas', 'description' => 'Bebida personal helada.', 'price' => 5.50, 'image_url' => '/images/products/bebidas/sprite.jpg'],
            ['name' => 'Chicha Morada 1L', 'category' => 'bebidas', 'description' => 'Chicha morada artesanal.', 'price' => 12.90, 'image_url' => '/images/products/bebidas/chicha_1L.jpg'],
            ['name' => 'Maracuya Frozen', 'category' => 'bebidas', 'description' => 'Refrescante bebida frozen.', 'price' => 9.90, 'image_url' => '/images/products/default.svg'],
            ['name' => 'Limonada Frozen', 'category' => 'bebidas', 'description' => 'Limonada frozen de la casa.', 'price' => 9.90, 'image_url' => '/images/products/bebidas/limonada.jpg'],
            ['name' => 'Agua Mineral 625ml', 'category' => 'bebidas', 'description' => 'Agua mineral sin gas.', 'price' => 4.00, 'image_url' => '/images/products/bebidas/agua.jpg'],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['name' => $product['name']],
                [
                    'category' => $product['category'],
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'image_url' => $product['image_url'],
                    'is_available' => true,
                    'stock' => 120,
                ]
            );
        }

        $this->seedDemoConsumption();
    }

    private function seedDemoConsumption(): void
    {
        DB::transaction(function (): void {
            OrderStatusHistory::query()->delete();
            OrderItem::query()->delete();
            Order::query()->where('tracking_code', 'like', 'DEM%')->delete();
            UserAddress::query()->where('user_id', '>', 1)->delete();
            LoginHistory::query()->where('user_id', '>', 1)->delete();
            InventoryMovement::query()->where('movement_type', '!=', InventoryMovement::TYPE_OPENING)->delete();
            User::query()->where('id', '>', 1)->delete();

            Product::query()->update(['stock' => 120]);

            $products = Product::query()->orderBy('id')->get()->keyBy('name');
            $admin = User::query()->where('email', 'admin@eldorado.pe')->firstOrFail();

            InventoryMovement::query()->delete();

            foreach (Product::query()->get() as $product) {
                InventoryMovement::query()->create([
                    'product_id' => $product->id,
                    'product_name_snapshot' => $product->name,
                    'movement_type' => InventoryMovement::TYPE_OPENING,
                    'direction' => InventoryMovement::DIRECTION_IN,
                    'quantity' => 120,
                    'stock_before' => 0,
                    'stock_after' => 120,
                    'reference_type' => 'product',
                    'reference_id' => $product->id,
                    'reference_code' => 'initial-load',
                    'note' => 'Carga inicial del producto',
                    'performed_by' => $admin->id,
                    'role_snapshot' => 'admin',
                    'created_at' => Carbon::create(2026, 5, 1, 9, 0, 0, 'America/Lima')->utc(),
                    'updated_at' => Carbon::create(2026, 5, 1, 9, 0, 0, 'America/Lima')->utc(),
                ]);
            }

            $customers = [
                [
                    'name' => 'Carlos Alberto Quiroz Salazar',
                    'email' => 'cquiroz.salazar@gmail.com',
                    'phone' => '953976841',
                    'address' => 'Psje. Quiroz 184, El Tambo, Huancayo',
                    'label' => 'Casa',
                ],
                [
                    'name' => 'Fiorella Milagros Velasquez Huaman',
                    'email' => 'fiore.velasquez.h@gmail.com',
                    'phone' => '964550231',
                    'address' => 'Jr. Arequipa 542, Huancayo',
                    'label' => 'Departamento',
                ],
                [
                    'name' => 'Jhonatan Raul Torres Canchari',
                    'email' => 'jtorres.canchari@outlook.com',
                    'phone' => '972118540',
                    'address' => 'Av. Calmell del Solar 310, Chilca',
                    'label' => 'Casa familiar',
                ],
                [
                    'name' => 'Roxana Patricia Medina Poma',
                    'email' => 'roxana.medina.poma@gmail.com',
                    'phone' => '989007612',
                    'address' => 'Jr. Ica 890, Huancayo',
                    'label' => 'Trabajo',
                ],
                [
                    'name' => 'Luis Fernando Cerron Palomino',
                    'email' => 'lfcerron.palomino@gmail.com',
                    'phone' => '996431552',
                    'address' => 'Av. Huancavelica 1210, Huancayo',
                    'label' => 'Casa',
                ],
                [
                    'name' => 'Mariela Soledad Paitan Rojas',
                    'email' => 'mariela.paitan.rojas@gmail.com',
                    'phone' => '931447285',
                    'address' => 'Psje. Los Andes 112, El Tambo',
                    'label' => 'Casa',
                ],
                [
                    'name' => 'Kevin Eduardo Meza Cardenas',
                    'email' => 'kevin.meza.cardenas@gmail.com',
                    'phone' => '951223408',
                    'address' => 'Jr. Cajamarca 275, Chilca',
                    'label' => 'Oficina',
                ],
                [
                    'name' => 'Andrea Lucero Gamarra Taipe',
                    'email' => 'andreagamarra.t@gmail.com',
                    'phone' => '965772940',
                    'address' => 'Av. Ferrocarril 1880, Huancayo',
                    'label' => 'Casa',
                ],
            ];

            $users = [];

            foreach ($customers as $index => $customer) {
                $user = User::query()->create([
                    'name' => $customer['name'],
                    'email' => $customer['email'],
                    'phone' => $customer['phone'],
                    'role' => 'customer',
                    'is_active' => true,
                    'password' => Hash::make('cliente12345'),
                    'created_at' => Carbon::create(2026, 4, 20 + $index, 10, 0, 0, 'America/Lima')->utc(),
                    'updated_at' => Carbon::create(2026, 5, 20, 10, 0, 0, 'America/Lima')->utc(),
                ]);

                UserAddress::query()->create([
                    'user_id' => $user->id,
                    'label' => $customer['label'],
                    'address' => $customer['address'],
                    'created_at' => Carbon::create(2026, 5, 2, 11, 0, 0, 'America/Lima')->utc(),
                    'updated_at' => Carbon::create(2026, 5, 2, 11, 0, 0, 'America/Lima')->utc(),
                ]);

                LoginHistory::query()->create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36',
                    'successful' => true,
                    'created_at' => Carbon::create(2026, 5, 10 + $index, 19, 15, 0, 'America/Lima')->utc(),
                ]);

                $users[] = $user;
            }

            $orders = [
                [
                    'user' => 0,
                    'tracking' => 'DEM-260501',
                    'created_at' => [2026, 5, 1, 12, 20],
                    'delivery_type' => 'delivery',
                    'status' => Order::STATUS_DELIVERED,
                    'payment_method' => 'yape',
                    'payment_status' => 'verified',
                    'payment_reference' => 'YAP-501928',
                    'proof' => '/storage/payment-proofs/demo-yape-260501.jpg',
                    'salad' => 'salada',
                    'address' => 'Psje. Quiroz 184, El Tambo, Huancayo',
                    'reference' => 'Frente al parque del barrio',
                    'scheduled_for' => null,
                    'receipt' => 'boleta',
                    'dni' => '61643308',
                    'items' => [
                        ['product' => '1/2 Pollo a la Brasa', 'qty' => 1],
                        ['product' => 'Inca Kola Personal 500ml', 'qty' => 2],
                    ],
                ],
                [
                    'user' => 1,
                    'tracking' => 'DEM-260503',
                    'created_at' => [2026, 5, 3, 20, 10],
                    'delivery_type' => 'pickup',
                    'status' => Order::STATUS_DELIVERED,
                    'payment_method' => 'plin',
                    'payment_status' => 'verified',
                    'payment_reference' => 'PLN-640114',
                    'proof' => '/storage/payment-proofs/demo-plin-260503.jpg',
                    'salad' => 'dulce',
                    'address' => null,
                    'reference' => null,
                    'scheduled_for' => [2026, 5, 3, 21, 0],
                    'receipt' => 'boleta',
                    'dni' => '72851462',
                    'items' => [
                        ['product' => 'Pollo Entero a la Brasa', 'qty' => 1],
                        ['product' => 'Chicha Morada 1L', 'qty' => 1],
                    ],
                ],
                [
                    'user' => 2,
                    'tracking' => 'DEM-260505',
                    'created_at' => [2026, 5, 5, 13, 45],
                    'delivery_type' => 'delivery',
                    'status' => Order::STATUS_DELIVERED,
                    'payment_method' => 'mercado_pago',
                    'payment_status' => 'verified',
                    'payment_reference' => 'MP-884201',
                    'proof' => null,
                    'salad' => 'salada',
                    'address' => 'Av. Calmell del Solar 310, Chilca',
                    'reference' => 'Porton negro',
                    'scheduled_for' => null,
                    'receipt' => 'boleta',
                    'dni' => '70411839',
                    'items' => [
                        ['product' => 'Mega Combo Familiar', 'qty' => 1],
                    ],
                ],
                [
                    'user' => 3,
                    'tracking' => 'DEM-260507',
                    'created_at' => [2026, 5, 7, 19, 30],
                    'delivery_type' => 'pickup',
                    'status' => Order::STATUS_DELIVERED,
                    'payment_method' => 'cod',
                    'payment_status' => 'verified',
                    'payment_reference' => 'COD-260507',
                    'proof' => null,
                    'salad' => null,
                    'address' => null,
                    'reference' => null,
                    'scheduled_for' => null,
                    'receipt' => null,
                    'dni' => null,
                    'items' => [
                        ['product' => 'Parrilla Mixta', 'qty' => 1],
                        ['product' => 'Limonada Frozen', 'qty' => 1],
                    ],
                ],
                [
                    'user' => 4,
                    'tracking' => 'DEM-260509',
                    'created_at' => [2026, 5, 9, 18, 5],
                    'delivery_type' => 'delivery',
                    'status' => Order::STATUS_DELIVERED,
                    'payment_method' => 'yape',
                    'payment_status' => 'verified',
                    'payment_reference' => 'YAP-774510',
                    'proof' => '/storage/payment-proofs/demo-yape-260509.jpg',
                    'salad' => 'salada',
                    'address' => 'Av. Huancavelica 1210, Huancayo',
                    'reference' => 'Tercer piso, timbre azul',
                    'scheduled_for' => [2026, 5, 9, 19, 0],
                    'receipt' => 'boleta',
                    'dni' => '45287193',
                    'items' => [
                        ['product' => '1/4 Pollo a la Brasa', 'qty' => 2],
                        ['product' => 'Coca-Cola Personal 500ml', 'qty' => 2],
                    ],
                ],
                [
                    'user' => 5,
                    'tracking' => 'DEM-260511',
                    'created_at' => [2026, 5, 11, 21, 15],
                    'delivery_type' => 'delivery',
                    'status' => Order::STATUS_CANCELLED,
                    'payment_method' => 'plin',
                    'payment_status' => 'rejected',
                    'payment_reference' => 'PLN-992761',
                    'proof' => '/storage/payment-proofs/demo-plin-260511.jpg',
                    'salad' => 'dulce',
                    'address' => 'Psje. Los Andes 112, El Tambo',
                    'reference' => 'Casa color crema',
                    'scheduled_for' => null,
                    'receipt' => 'boleta',
                    'dni' => '71022846',
                    'items' => [
                        ['product' => 'Pollo Entero a la Brasa', 'qty' => 1],
                    ],
                ],
                [
                    'user' => 6,
                    'tracking' => 'DEM-260514',
                    'created_at' => [2026, 5, 14, 12, 55],
                    'delivery_type' => 'pickup',
                    'status' => Order::STATUS_DELIVERED,
                    'payment_method' => 'mercado_pago',
                    'payment_status' => 'verified',
                    'payment_reference' => 'MP-160420',
                    'proof' => null,
                    'salad' => null,
                    'address' => null,
                    'reference' => null,
                    'scheduled_for' => [2026, 5, 14, 14, 0],
                    'receipt' => null,
                    'dni' => null,
                    'items' => [
                        ['product' => 'Alitas BBQ x 8', 'qty' => 1],
                        ['product' => 'Sprite Personal 500ml', 'qty' => 1],
                    ],
                ],
                [
                    'user' => 7,
                    'tracking' => 'DEM-260516',
                    'created_at' => [2026, 5, 16, 20, 25],
                    'delivery_type' => 'delivery',
                    'status' => Order::STATUS_DELIVERED,
                    'payment_method' => 'yape',
                    'payment_status' => 'verified',
                    'payment_reference' => 'YAP-516335',
                    'proof' => '/storage/payment-proofs/demo-yape-260516.jpg',
                    'salad' => 'salada',
                    'address' => 'Av. Ferrocarril 1880, Huancayo',
                    'reference' => 'Al costado del minimarket',
                    'scheduled_for' => [2026, 5, 16, 21, 10],
                    'receipt' => 'boleta',
                    'dni' => '73661950',
                    'items' => [
                        ['product' => '1/2 Pollo a la Brasa', 'qty' => 1],
                        ['product' => 'Chicha Morada 1L', 'qty' => 1],
                    ],
                ],
                [
                    'user' => 0,
                    'tracking' => 'DEM-260519',
                    'created_at' => [2026, 5, 19, 18, 40],
                    'delivery_type' => 'delivery',
                    'status' => Order::STATUS_DELIVERED,
                    'payment_method' => 'cod',
                    'payment_status' => 'verified',
                    'payment_reference' => 'COD-260519',
                    'proof' => null,
                    'salad' => 'dulce',
                    'address' => 'Psje. Quiroz 184, El Tambo, Huancayo',
                    'reference' => 'Porton gris',
                    'scheduled_for' => null,
                    'receipt' => null,
                    'dni' => null,
                    'items' => [
                        ['product' => 'Mostrito Tradicional', 'qty' => 2],
                    ],
                ],
                [
                    'user' => 1,
                    'tracking' => 'DEM-260521',
                    'created_at' => [2026, 5, 21, 14, 5],
                    'delivery_type' => 'pickup',
                    'status' => Order::STATUS_DELIVERED,
                    'payment_method' => 'plin',
                    'payment_status' => 'verified',
                    'payment_reference' => 'PLN-410885',
                    'proof' => '/storage/payment-proofs/demo-plin-260521.jpg',
                    'salad' => null,
                    'address' => null,
                    'reference' => null,
                    'scheduled_for' => [2026, 5, 21, 15, 0],
                    'receipt' => null,
                    'dni' => null,
                    'items' => [
                        ['product' => 'Churrasco a la Parrilla', 'qty' => 1],
                        ['product' => 'Maracuya Frozen', 'qty' => 1],
                    ],
                ],
                [
                    'user' => 2,
                    'tracking' => 'DEM-260523',
                    'created_at' => [2026, 5, 23, 20, 50],
                    'delivery_type' => 'delivery',
                    'status' => Order::STATUS_DELIVERED,
                    'payment_method' => 'yape',
                    'payment_status' => 'verified',
                    'payment_reference' => 'YAP-523771',
                    'proof' => '/storage/payment-proofs/demo-yape-260523.jpg',
                    'salad' => 'salada',
                    'address' => 'Av. Calmell del Solar 310, Chilca',
                    'reference' => 'Puerta de madera',
                    'scheduled_for' => [2026, 5, 23, 22, 0],
                    'receipt' => 'boleta',
                    'dni' => '70411839',
                    'items' => [
                        ['product' => 'Mega Combo Familiar', 'qty' => 1],
                        ['product' => 'Agua Mineral 625ml', 'qty' => 1],
                    ],
                ],
                [
                    'user' => 3,
                    'tracking' => 'DEM-260525',
                    'created_at' => [2026, 5, 25, 13, 30],
                    'delivery_type' => 'pickup',
                    'status' => Order::STATUS_PREPARING,
                    'payment_method' => 'mercado_pago',
                    'payment_status' => 'pending',
                    'payment_reference' => 'MP-250725',
                    'proof' => null,
                    'salad' => null,
                    'address' => null,
                    'reference' => null,
                    'scheduled_for' => [2026, 5, 25, 14, 20],
                    'receipt' => null,
                    'dni' => null,
                    'items' => [
                        ['product' => 'Parrilla Mixta', 'qty' => 1],
                    ],
                ],
                [
                    'user' => 4,
                    'tracking' => 'DEM-260527',
                    'created_at' => [2026, 5, 27, 19, 55],
                    'delivery_type' => 'delivery',
                    'status' => Order::STATUS_DELIVERED,
                    'payment_method' => 'mercado_pago',
                    'payment_status' => 'verified',
                    'payment_reference' => 'MP-882531',
                    'proof' => null,
                    'salad' => 'salada',
                    'address' => 'Av. Huancavelica 1210, Huancayo',
                    'reference' => 'Segundo nivel',
                    'scheduled_for' => null,
                    'receipt' => 'boleta',
                    'dni' => '45287193',
                    'items' => [
                        ['product' => '1/4 Pollo a la Brasa', 'qty' => 1],
                        ['product' => 'Inca Kola Personal 500ml', 'qty' => 1],
                    ],
                ],
                [
                    'user' => 6,
                    'tracking' => 'DEM-260529',
                    'created_at' => [2026, 5, 29, 17, 50],
                    'delivery_type' => 'delivery',
                    'status' => Order::STATUS_ON_THE_WAY,
                    'payment_method' => 'yape',
                    'payment_status' => 'verified',
                    'payment_reference' => 'YAP-529115',
                    'proof' => '/storage/payment-proofs/demo-yape-260529.jpg',
                    'salad' => null,
                    'address' => 'Jr. Cajamarca 275, Chilca',
                    'reference' => 'Frente a farmacia',
                    'scheduled_for' => null,
                    'receipt' => null,
                    'dni' => null,
                    'items' => [
                        ['product' => 'Brochetas de Pollo', 'qty' => 2],
                        ['product' => 'Sprite Personal 500ml', 'qty' => 1],
                    ],
                ],
                [
                    'user' => 7,
                    'tracking' => 'DEM-260531',
                    'created_at' => [2026, 5, 31, 11, 25],
                    'delivery_type' => 'pickup',
                    'status' => Order::STATUS_CONFIRMED,
                    'payment_method' => 'plin',
                    'payment_status' => 'reported',
                    'payment_reference' => 'PLN-531004',
                    'proof' => '/storage/payment-proofs/demo-plin-260531.jpg',
                    'salad' => 'dulce',
                    'address' => null,
                    'reference' => null,
                    'scheduled_for' => [2026, 5, 31, 12, 30],
                    'receipt' => 'boleta',
                    'dni' => '73661950',
                    'items' => [
                        ['product' => '1/2 Pollo a la Brasa', 'qty' => 1],
                    ],
                ],
            ];

            $nextOrderId = 1;
            $nextItemId = 1;
            $nextStatusHistoryId = 1;
            $nextMovementId = 18;

            foreach ($orders as $orderData) {
                $user = $users[$orderData['user']];
                [$createdYear, $createdMonth, $createdDay, $createdHour, $createdMinute] = $orderData['created_at'];
                $createdAt = Carbon::create($createdYear, $createdMonth, $createdDay, $createdHour, $createdMinute, 0, 'America/Lima')->utc();
                $scheduledFor = null;

                if ($orderData['scheduled_for']) {
                    [$scheduledYear, $scheduledMonth, $scheduledDay, $scheduledHour, $scheduledMinute] = $orderData['scheduled_for'];
                    $scheduledFor = Carbon::create($scheduledYear, $scheduledMonth, $scheduledDay, $scheduledHour, $scheduledMinute, 0, 'America/Lima')->utc();
                }

                $total = 0.0;
                foreach ($orderData['items'] as $item) {
                    $total += (float) $products[$item['product']]->price * $item['qty'];
                }

                $order = Order::query()->create([
                    'id' => $nextOrderId,
                    'user_id' => $user->id,
                    'tracking_code' => $orderData['tracking'],
                    'customer_name' => $user->name,
                    'customer_phone' => $user->phone,
                    'customer_email' => $user->email,
                    'delivery_type' => $orderData['delivery_type'],
                    'scheduled_for' => $scheduledFor,
                    'delivery_window_label' => $scheduledFor ? 'Horario programado' : null,
                    'status' => $orderData['status'],
                    'total_amount' => round($total, 2),
                    'payment_method' => $orderData['payment_method'],
                    'payment_gateway' => $orderData['payment_method'] === 'mercado_pago' ? 'mercadopago' : null,
                    'payment_reference' => $orderData['payment_reference'],
                    'payment_proof_path' => $orderData['proof'],
                    'payment_reported_at' => $orderData['proof'] ? $createdAt->copy()->addMinutes(4) : null,
                    'payment_verified_at' => $orderData['payment_status'] === 'verified' ? $createdAt->copy()->addMinutes(18) : null,
                    'payment_status' => $orderData['payment_status'],
                    'billing_document_type' => $orderData['receipt'] ? 'dni' : null,
                    'billing_document_number' => $orderData['dni'],
                    'billing_name' => $orderData['receipt'] ? $user->name : null,
                    'billing_email' => $orderData['receipt'] ? $user->email : null,
                    'billing_address' => $orderData['receipt'] ? $orderData['address'] : null,
                    'billing_receipt_type' => $orderData['receipt'],
                    'billing_metadata' => $orderData['receipt'] ? ['seed' => 'demo-consumption'] : null,
                    'salad_type' => $orderData['salad'],
                    'drink_note' => null,
                    'address' => $orderData['address'],
                    'reference' => $orderData['reference'],
                    'latitude' => null,
                    'longitude' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt->copy()->addMinutes(35),
                ]);

                foreach ($orderData['items'] as $item) {
                    $product = $products[$item['product']];
                    $lineTotal = round((float) $product->price * $item['qty'], 2);

                    OrderItem::query()->create([
                        'id' => $nextItemId++,
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit_price' => $product->price,
                        'quantity' => $item['qty'],
                        'line_total' => $lineTotal,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    $stockBefore = (int) $product->stock;
                    $stockAfter = $orderData['status'] === Order::STATUS_CANCELLED
                        ? $stockBefore
                        : $stockBefore - $item['qty'];

                    if ($orderData['status'] !== Order::STATUS_CANCELLED) {
                        $product->update(['stock' => $stockAfter]);
                        $products[$item['product']] = $product->fresh();
                    }

                    InventoryMovement::query()->create([
                        'id' => $nextMovementId++,
                        'product_id' => $product->id,
                        'product_name_snapshot' => $product->name,
                        'movement_type' => InventoryMovement::TYPE_SALE,
                        'direction' => InventoryMovement::DIRECTION_OUT,
                        'quantity' => $item['qty'],
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'reference_code' => $order->tracking_code,
                        'note' => 'Salida por venta demo',
                        'performed_by' => $user->id,
                        'role_snapshot' => 'customer',
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }

                OrderStatusHistory::query()->create([
                    'id' => $nextStatusHistoryId++,
                    'order_id' => $order->id,
                    'status' => Order::STATUS_PENDING,
                    'note' => 'Pedido registrado desde semilla demo',
                    'changed_by' => $user->id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                if ($orderData['status'] !== Order::STATUS_PENDING) {
                    OrderStatusHistory::query()->create([
                        'id' => $nextStatusHistoryId++,
                        'order_id' => $order->id,
                        'status' => $orderData['status'],
                        'note' => 'Estado actualizado durante la operacion demo',
                        'changed_by' => $admin->id,
                        'created_at' => $createdAt->copy()->addMinutes(30),
                        'updated_at' => $createdAt->copy()->addMinutes(30),
                    ]);
                }

                $nextOrderId++;
            }
        });
    }
}

<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="/images/ico-pollo.jpg">
    <link rel="shortcut icon" type="image/jpeg" href="/images/ico-pollo.jpg">
    <title>Pollos y Parrillas El Dorado - Dashboard Ejecutivo</title>
    <style>
        :root {
            --orange: #ff7a1a;
            --orange-soft: #ff9f62;
            --paper: #171717;
            --paper-soft: #202020;
            --ink: #ffffff;
            --ink-soft: #d1d1d1;
            --line: rgba(255, 122, 26, .22);
            --green: #23885b;
            --red: #b84e34;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Trebuchet MS", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(255, 122, 26, .18), transparent 28%),
                radial-gradient(circle at bottom right, rgba(255, 111, 31, .12), transparent 26%),
                linear-gradient(180deg, #080808 0%, #101010 48%, #171717 100%);
        }

        .container {
            width: min(1280px, 100%);
            margin: 0 auto;
            padding: 22px 18px 42px;
        }

        .hero {
            display: grid;
            grid-template-columns: 1.08fr .92fr;
            gap: 18px;
            padding: 22px;
            border-radius: 32px;
            border: 1px solid rgba(255, 122, 26, .20);
            background: rgba(18, 18, 18, .96);
            box-shadow: 0 30px 60px rgba(0, 0, 0, .28);
            margin-bottom: 18px;
        }

        .eyebrow {
            margin: 0 0 10px;
            font-size: 11px;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--orange-soft);
            font-weight: 900;
        }

        h1, h2, h3 {
            margin: 0;
            color: #fff;
        }

        h1 {
            font-size: clamp(34px, 4vw, 54px);
            line-height: .95;
            margin-bottom: 10px;
        }

        .hero p,
        .section-copy,
        .table-head p {
            margin: 0;
            color: var(--ink-soft);
            line-height: 1.65;
            font-size: 15px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-content: start;
            justify-content: flex-end;
        }

        .pill,
        .mini-label,
        .nav-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid rgba(255, 122, 26, .22);
            background: rgba(24, 24, 24, .94);
            color: #fff;
            font-size: 12px;
            font-weight: 900;
            text-decoration: none;
        }

        .cards,
        .grid-2 {
            display: grid;
            gap: 16px;
        }

        .cards {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            margin-bottom: 18px;
        }

        .grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-bottom: 18px;
        }

        .card,
        .table-shell {
            background: rgba(20, 20, 20, .98);
            border: 1px solid rgba(255, 122, 26, .18);
            border-radius: 24px;
            padding: 18px;
            box-shadow: 0 18px 36px rgba(0, 0, 0, .26);
        }

        .label {
            font-size: 12px;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--orange-soft);
            font-weight: 900;
        }

        .value {
            margin-top: 10px;
            font-size: 34px;
            font-weight: 900;
            color: var(--orange-soft);
        }

        .value.small {
            font-size: 28px;
        }

        .table-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid var(--line);
        }

        th, td {
            text-align: left;
            padding: 13px 14px;
            border-bottom: 1px solid var(--line);
            font-size: 14px;
            vertical-align: top;
        }

        th {
            background: rgba(255, 122, 26, .10);
            color: #fff;
            font-size: 12px;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 12px;
            background: rgba(24,24,24,.96);
            border: 1px solid rgba(255, 122, 26, .20);
            color: #fff;
        }

        .status-badge.ok {
            color: var(--green);
            border-color: rgba(35, 136, 91, .28);
            background: rgba(35, 136, 91, .08);
        }

        .status-badge.alert {
            color: var(--red);
            border-color: rgba(184, 78, 52, .25);
            background: rgba(184, 78, 52, .08);
        }

        .muted {
            color: var(--ink-soft);
            font-size: 13px;
        }

        .section-title {
            margin-bottom: 8px;
        }

        @media (max-width: 980px) {
            .hero,
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 860px) {
            .table-shell {
                overflow-x: auto;
            }

            table {
                min-width: 720px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <section class="hero">
        <div>
            <p class="eyebrow">Resumen Ejecutivo</p>
            <h1>Ventas, almacen y reservas en una sola vista.</h1>
            <p>Este tablero ya trabaja con trazabilidad de stock, ventas exitosas y reservas programadas para que el negocio tenga lectura operativa y comercial en tiempo real.</p>
        </div>
        <div class="hero-actions">
            <a href="/admin/panel" class="nav-link">Volver al panel</a>
            <span class="pill">Top productos por ingreso</span>
            <span class="pill">Top clientes por gasto</span>
            <span class="pill">Rotacion de almacen</span>
            <span class="pill">Reservas de mayo <?php echo e($analytics['reservation_year']); ?></span>
        </div>
    </section>

    <div class="cards">
        <div class="card">
            <div class="label">Ventas de hoy</div>
            <div class="value">S/ <?php echo e(number_format($todaySales, 2)); ?></div>
        </div>
        <div class="card">
            <div class="label">Ventas del mes</div>
            <div class="value">S/ <?php echo e(number_format($monthSales, 2)); ?></div>
        </div>
        <div class="card">
            <div class="label">Pedidos activos</div>
            <div class="value"><?php echo e($pendingOrders); ?></div>
        </div>
        <div class="card">
            <div class="label">Productos con rotacion</div>
            <div class="value"><?php echo e($analytics['inventory_rotation']->count()); ?></div>
        </div>
    </div>

    <div class="grid-2">
        <section class="table-shell">
            <div class="table-head">
                <div>
                    <p class="eyebrow">Sistema de Ventas</p>
                    <h2 class="section-title">Top 10 productos que mas dinero generan</h2>
                    <p class="section-copy">Se consideran ventas satisfactorias: pedidos no cancelados con pago verificado.</p>
                </div>
                <span class="mini-label"><?php echo e($analytics['top_products']->count()); ?> registros</span>
            </div>
            <table>
                <thead>
                <tr>
                    <th>Producto</th>
                    <th>Unidades</th>
                    <th>Ingreso</th>
                </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $analytics['top_products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($product->product_name); ?></td>
                        <td><?php echo e((int) $product->units_sold); ?></td>
                        <td>S/ <?php echo e(number_format((float) $product->revenue, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="3">Aun no hay suficientes ventas verificadas.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="table-shell">
            <div class="table-head">
                <div>
                    <p class="eyebrow">Sistema de Ventas</p>
                    <h2 class="section-title">Top 10 clientes que mas dinero dejaron</h2>
                    <p class="section-copy">La consolidacion toma correo, telefono o nombre cuando el cliente compro como invitado.</p>
                </div>
                <span class="mini-label"><?php echo e($analytics['top_customers']->count()); ?> registros</span>
            </div>
            <table>
                <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Contacto</th>
                    <th>Pedidos</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $analytics['top_customers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($customer->customer_name); ?></td>
                        <td><?php echo e($customer->customer_email !== '-' && $customer->customer_email ? $customer->customer_email : $customer->customer_phone); ?></td>
                        <td><?php echo e((int) $customer->successful_orders); ?></td>
                        <td>S/ <?php echo e(number_format((float) $customer->total_spent, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4">Aun no hay suficientes ventas verificadas.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>

    <div class="grid-2">
        <section class="table-shell">
            <div class="table-head">
                <div>
                    <p class="eyebrow">Control de Ordenes</p>
                    <h2 class="section-title">Estados de orden y operacion reciente</h2>
                    <p class="section-copy">Aqui se resume el flujo de estados para monitorear cuello de botella y pedidos pendientes.</p>
                </div>
                <span class="mini-label"><?php echo e($analytics['status_breakdown']->count()); ?> estados</span>
            </div>
            <table>
                <thead>
                <tr>
                    <th>Estado</th>
                    <th>Pedidos</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                <?php $__currentLoopData = $analytics['status_breakdown']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($status->status); ?></td>
                        <td><?php echo e((int) $status->total_orders); ?></td>
                        <td>S/ <?php echo e(number_format((float) $status->total_amount, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </section>

        <section class="table-shell">
            <div class="table-head">
                <div>
                    <p class="eyebrow">Pedidos recientes</p>
                    <h2 class="section-title">Ultimos pedidos registrados</h2>
                    <p class="section-copy">Incluye estado actual, pago y si fue programado como reserva.</p>
                </div>
                <span class="mini-label"><?php echo e(count($latestOrders)); ?> registros</span>
            </div>
            <table>
                <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Cliente</th>
                    <th>Estado</th>
                    <th>Pago</th>
                    <th>Reserva</th>
                </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $latestOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($order->tracking_code); ?></td>
                        <td><?php echo e($order->customer_name); ?></td>
                        <td><?php echo e($order->status); ?></td>
                        <td><?php echo e($order->payment_method); ?> / <?php echo e($order->payment_status); ?></td>
                        <td><?php echo e($order->scheduled_for ? $order->scheduled_for->format('Y-m-d H:i') : 'No programado'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5">Sin pedidos aun.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>

    <section class="table-shell" style="margin-bottom:18px;">
        <div class="table-head">
            <div>
                <p class="eyebrow">Sustento de Ventas Satisfactorias</p>
                <h2 class="section-title">Comprobante, pasarela y evidencia de pago</h2>
                <p class="section-copy">Este bloque centraliza la boleta PDF, referencia de pago, pasarela y comprobante subido por el cliente.</p>
            </div>
            <span class="mini-label"><?php echo e(collect($analytics['successful_sales_support'])->count()); ?> ventas</span>
        </div>
        <table>
            <thead>
            <tr>
                <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Pago</th>
                    <th>Comprobante</th>
                    <th>Factura electronica</th>
            </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $analytics['successful_sales_support']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $support): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <strong><?php echo e($support['tracking_code']); ?></strong><br>
                        <span class="muted">S/ <?php echo e(number_format((float) $support['total_amount'], 2)); ?></span>
                    </td>
                    <td>
                        <?php echo e($support['customer_name']); ?><br>
                        <span class="muted"><?php echo e($support['receipt_type'] ?: 'Sin comprobante tributario'); ?> <?php echo e($support['billing_document_number'] ? '· '.$support['billing_document_number'] : ''); ?></span>
                    </td>
                    <td>
                        <div><?php echo e($support['payment_method']); ?><?php echo e($support['payment_gateway'] ? ' / '.$support['payment_gateway'] : ''); ?></div>
                        <span class="muted"><?php echo e($support['payment_reference'] ?: 'Sin referencia'); ?></span>
                    </td>
                    <td>
                        <?php if($support['payment_proof_path']): ?>
                            <a href="<?php echo e($support['payment_proof_path']); ?>" target="_blank">Ver comprobante</a><br>
                            <span class="status-badge ok">Pago sustentado</span>
                        <?php else: ?>
                            <span class="status-badge alert">Sin archivo adjunto</span>
                        <?php endif; ?>
                        <div class="muted">Boleta PDF disponible desde el detalle autenticado del pedido.</div>
                    </td>
                    <td>
                        <?php if($support['einvoice_provider']): ?>
                            <div><?php echo e($support['einvoice_provider']); ?></div>
                            <span class="muted"><?php echo e($support['einvoice_sent_at'] ?: 'Sin fecha'); ?></span>
                        <?php else: ?>
                            <span class="muted">Aun no emitida por API</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5">Aun no hay ventas verificadas para mostrar sustento.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>

    <div class="grid-2">
        <section class="table-shell">
            <div class="table-head">
                <div>
                    <p class="eyebrow">Sistema de Almacen</p>
                    <h2 class="section-title">Producto o insumo con mayor rotacion</h2>
                    <p class="section-copy">La rotacion se calcula sobre entradas y salidas reales del historial de movimientos.</p>
                </div>
                <span class="mini-label"><?php echo e($analytics['inventory_rotation']->count()); ?> productos</span>
            </div>
            <table>
                <thead>
                <tr>
                    <th>Producto</th>
                    <th>Salidas</th>
                    <th>Entradas</th>
                    <th>Rotacion</th>
                </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $analytics['inventory_rotation']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($movement->product_name); ?></td>
                        <td><?php echo e((int) $movement->total_outputs); ?></td>
                        <td><?php echo e((int) $movement->total_inputs); ?></td>
                        <td><?php echo e((int) $movement->total_rotation); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4">Aun no hay movimientos de almacen.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="table-shell">
            <div class="table-head">
                <div>
                    <p class="eyebrow">Sistema de Almacen</p>
                    <h2 class="section-title">Usuarios con mas movimientos</h2>
                    <p class="section-copy">La trazabilidad guarda quien movio stock y cuantas unidades afecto en total.</p>
                </div>
                <span class="mini-label"><?php echo e($analytics['top_inventory_users']->count()); ?> usuarios</span>
            </div>
            <table>
                <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Movimientos</th>
                    <th>Unidades</th>
                </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $analytics['top_inventory_users']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userMovement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($userMovement->actor_name); ?><br><span class="muted"><?php echo e($userMovement->actor_email); ?></span></td>
                        <td><?php echo e((int) $userMovement->movement_count); ?></td>
                        <td><?php echo e((int) $userMovement->total_units); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="3">Aun no hay trazabilidad suficiente de usuarios.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="table-shell">
            <div class="table-head">
                <div>
                    <p class="eyebrow">Sistema de Almacen</p>
                    <h2 class="section-title">Roles con mas movimientos realizados</h2>
                    <p class="section-copy">Resumen ideal para saber si la mayor carga operativa la mueve administracion, clientes u otros perfiles.</p>
                </div>
                <span class="mini-label"><?php echo e($analytics['top_inventory_roles']->count()); ?> roles</span>
            </div>
            <table>
                <thead>
                <tr>
                    <th>Rol</th>
                    <th>Movimientos</th>
                    <th>Unidades</th>
                </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $analytics['top_inventory_roles']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($role->role_name); ?></td>
                        <td><?php echo e((int) $role->movement_count); ?></td>
                        <td><?php echo e((int) $role->total_units); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="3">Aun no hay trazabilidad suficiente de roles.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>

    <section class="table-shell">
        <div class="table-head">
            <div>
                <p class="eyebrow">Servicios de Reserva</p>
                <h2 class="section-title">Clientes con mas reservas en mayo <?php echo e($analytics['reservation_year']); ?></h2>
                <p class="section-copy">Este ranking usa pedidos con `scheduled_for` para identificar reservas reales programadas.</p>
            </div>
            <span class="mini-label"><?php echo e($analytics['top_may_reservations']->count()); ?> clientes</span>
        </div>

        <table>
            <thead>
            <tr>
                <th>Cliente</th>
                <th>Contacto</th>
                <th>Reservas</th>
                <th>Monto reservado</th>
            </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $analytics['top_may_reservations']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reservation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($reservation->customer_name); ?></td>
                    <td><?php echo e($reservation->customer_email ?: $reservation->customer_phone); ?></td>
                    <td><?php echo e((int) $reservation->reservations_count); ?></td>
                    <td>S/ <?php echo e(number_format((float) $reservation->reserved_amount, 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4">Todavia no hay reservas programadas en mayo <?php echo e($analytics['reservation_year']); ?>.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>
</body>
</html>
<?php /**PATH C:\Proyectos\WEB_POLLERIA\laravel-app\resources\views\admin\dashboard.blade.php ENDPATH**/ ?>
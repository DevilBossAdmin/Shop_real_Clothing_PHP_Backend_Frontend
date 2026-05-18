<?php
require_once __DIR__ . '/../db.php';

class Report
{
    private static function revenueStatuses(): array
    {
        return ['confirmed', 'shipping', 'completed'];
    }

    public static function revenueTotal(string $from, string $to): int
    {
        $statuses = self::revenueStatuses();
        $in = implode(',', array_fill(0, count($statuses), '?'));

        $sql = "
            SELECT COALESCE(SUM(total), 0)
            FROM orders
            WHERE status IN ($in)
              AND DATE(created_at) BETWEEN ? AND ?
        ";

        $st = db()->prepare($sql);
        $params = array_merge($statuses, [$from, $to]);
        $st->execute($params);

        return (int)$st->fetchColumn();
    }

    public static function revenueSeries(string $from, string $to, string $groupBy = 'day'): array
    {
        $statuses = self::revenueStatuses();
        $in = implode(',', array_fill(0, count($statuses), '?'));

        $groupExpr = "DATE(created_at)";
        if ($groupBy === 'month') {
            $groupExpr = "DATE_FORMAT(created_at, '%Y-%m')";
        }

        $sql = "
            SELECT
                $groupExpr AS label,
                COALESCE(SUM(total), 0) AS revenue
            FROM orders
            WHERE status IN ($in)
              AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY label
            ORDER BY label ASC
        ";

        $st = db()->prepare($sql);
        $params = array_merge($statuses, [$from, $to]);
        $st->execute($params);

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function topProducts(string $from, string $to, int $limit = 10): array
    {
        $statuses = self::revenueStatuses();
        $in = implode(',', array_fill(0, count($statuses), '?'));

        $sql = "
            SELECT
                oi.product_id,
                p.name AS product_name,
                p.sku,
                SUM(oi.qty) AS total_qty,
                SUM(oi.qty * oi.price) AS total_revenue
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            LEFT JOIN products p ON p.id = oi.product_id
            WHERE o.status IN ($in)
              AND DATE(o.created_at) BETWEEN ? AND ?
            GROUP BY oi.product_id, p.name, p.sku
            ORDER BY total_qty DESC, total_revenue DESC
            LIMIT ?
        ";

        $st = db()->prepare($sql);
        $i = 1;
        foreach ($statuses as $status) {
            $st->bindValue($i++, $status);
        }
        $st->bindValue($i++, $from);
        $st->bindValue($i++, $to);
        $st->bindValue($i++, $limit, PDO::PARAM_INT);
        $st->execute();

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function inventorySummary(int $limit = 5): array
    {
        $sql = "
            SELECT
                p.id,
                p.name,
                p.sku,
                COALESCE(SUM(v.stock), 0) AS total_stock
            FROM products p
            LEFT JOIN product_variants v ON v.product_id = p.id
            GROUP BY p.id, p.name, p.sku
            ORDER BY total_stock ASC, p.id DESC
            LIMIT ?
        ";

        $st = db()->prepare($sql);
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->execute();

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function profit(string $from, string $to): array
    {
        $revenue = self::revenueTotal($from, $to);

        return [
            'revenue' => $revenue,
            'cost' => 0,
            'profit' => $revenue,
        ];
    }

    public static function newCustomers(string $from, string $to): int
    {
        $st = db()->prepare("
            SELECT COUNT(*)
            FROM users
            WHERE role = 'customer'
              AND DATE(created_at) BETWEEN ? AND ?
        ");
        $st->execute([$from, $to]);

        return (int)$st->fetchColumn();
    }

    public static function cancelRate(string $from, string $to): array
    {
        $st = db()->prepare("
            SELECT
                COUNT(*) AS total_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_orders
            FROM orders
            WHERE DATE(created_at) BETWEEN ? AND ?
        ");
        $st->execute([$from, $to]);
        $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];

        $total = (int)($row['total_orders'] ?? 0);
        $cancelled = (int)($row['cancelled_orders'] ?? 0);
        $rate = $total > 0 ? round(($cancelled / $total) * 100, 2) : 0;

        return [
            'total_orders' => $total,
            'cancelled_orders' => $cancelled,
            'rate' => $rate,
        ];
    }

    public static function orderStatusPie(string $from, string $to): array
    {
        $st = db()->prepare("
            SELECT
                status,
                COUNT(*) AS total
            FROM orders
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY status
            ORDER BY total DESC
        ");
        $st->execute([$from, $to]);

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
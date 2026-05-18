<?php
require_once __DIR__ . '/../Core/Database.php';
require_once dirname(__DIR__, 2) . '/app/helpers.php';
require_once dirname(__DIR__, 2) . '/app/models/Product.php';
require_once dirname(__DIR__, 2) . '/app/models/Order.php';
require_once dirname(__DIR__, 2) . '/app/models/SystemSetting.php';

function chatbot_reply(string $text): string
{
    $text = trim($text);
    if ($text === '') {
        return 'Bạn hãy nhập nội dung nhé.';
    }

    chat_memory_boot();
    chat_memory_push_user($text);

    $t = mb_strtolower($text, 'UTF-8');
    $tNo = normalize_vn($t);

    if (is_smalltalk_greeting($tNo)) {
        return chat_reply_and_store("Xin chào 👋 Mình có thể hỗ trợ tìm sản phẩm, tư vấn size, tra cứu đơn hàng, mã vận đơn, phí ship và chính sách đổi trả.\nBạn có thể nhắn như: *áo sơ mi trắng*, *cao 170 nặng 65*, hoặc *đơn #12*.");
    }

    if (is_smalltalk_thanks($tNo)) {
        return chat_reply_and_store('Rất vui được hỗ trợ bạn 😊 Nếu cần mình có thể tìm thêm sản phẩm phù hợp theo giá, màu, size hoặc tra cứu đơn hàng cho bạn.');
    }

    if (is_smalltalk_bye($tNo)) {
        return chat_reply_and_store('Dạ cảm ơn bạn. Khi cần cứ nhắn mình bất cứ lúc nào nhé 👋');
    }

    if (contains_any($tNo, ['shop', 'cua hang', 'dia chi', 'hotline', 'lien he', 'gio mo cua', 'thong tin'])) {
        return chat_reply_and_store(bot_shop_info());
    }

    if (contains_any($tNo, ['doi tra', 'tra hang', 'hoan tien', 'bao hanh', 'return'])) {
        return chat_reply_and_store(bot_return_policy());
    }

    if (contains_any($tNo, ['van chuyen', 'ship', 'giao hang', 'phi ship', 'thoi gian giao'])) {
        return chat_reply_and_store(bot_shipping_policy());
    }

    if (contains_any($tNo, ['nhan vien', 'tu van vien', 'admin', 'quan ly', 'nguoi that', 'ho tro truc tiep', 'goi lai'])) {
        return chat_reply_and_store("Mình đang là chat hỗ trợ tự động. Nếu bạn cần nhân viên hỗ trợ trực tiếp, bạn hãy để lại:\n- *Tên*\n- *SĐT*\n- *Nội dung cần hỗ trợ*\nShop sẽ liên hệ lại sớm nhé.");
    }

    if (contains_any($tNo, ['size', 'kich co', 'tu van size', 'cao', 'can', 'nang', 'kg', 'cm'])) {
        $hw = extract_height_weight($tNo);
        if ($hw) {
            return chat_reply_and_store(bot_size_advice($hw['height_cm'], $hw['weight_kg'], detect_product_type($tNo)));
        }
        if (contains_any($tNo, ['size', 'tu van', 'kich co'])) {
            return chat_reply_and_store("Bạn cho mình xin *chiều cao (cm)* và *cân nặng (kg)* nhé.\nVí dụ: *cao 170 nặng 65* hoặc *170cm 65kg*.");
        }
    }

    if (contains_any($tNo, ['don hang', 'order', 'ma don', 'kiem tra don', 'trang thai', 'tracking', 'van don'])) {
        $info = extract_order_lookup($tNo);
        if (!$info) {
            $lastOrderId = chat_memory_get('last_order_id');
            if ($lastOrderId && contains_any($tNo, ['don do', 'don ay', 'don nay'])) {
                $info = ['order_id' => (int)$lastOrderId];
            }
        }
        if (!$info) {
            return chat_reply_and_store("Bạn gửi giúp mình *mã đơn* (vd: #123) hoặc *SĐT đặt hàng* để mình tra cứu nhé.\nVí dụ: *đơn #12* hoặc *đơn hàng sđt 090xxxxxxx*.");
        }
        return chat_reply_and_store(bot_order_lookup($info['order_id'] ?? null, $info['phone'] ?? null));
    }

    if ($choice = parse_list_choice($tNo)) {
        $reply = bot_followup_choice($choice);
        if ($reply !== null) {
            return chat_reply_and_store($reply);
        }
    }

    if (contains_any($tNo, ['size nao', 'size gi', 'mac size', 'size m', 'size l', 'size xl', 'size s', 'mau nao', 'mau gi', 'ton kho', 'con hang', 'het hang'])) {
        $reply = bot_followup_stock_question($text, $tNo);
        if ($reply !== null) {
            return chat_reply_and_store($reply);
        }
    }

    if (contains_any($tNo, ['thong tin san pham', 'chi tiet san pham', 'chi tiet', 'mo ta', 'chat lieu', 'form', 'kieu dang', 'thuong hieu', 'danh muc', 'gia goc', 'giam bao nhieu', 'giam may phan tram', 'sku', 'ma sp', 'ma san pham'])) {
        $reply = bot_product_detail_intent($text, $tNo);
        if ($reply !== null) {
            return chat_reply_and_store($reply);
        }
    }

    if (contains_any($tNo, ['so sanh', 'compare', 'khac nhau', 'nao tot hon', 'nao re hon'])) {
        $reply = bot_compare_products($text, $tNo);
        if ($reply !== null) {
            return chat_reply_and_store($reply);
        }
    }

    if (contains_any($tNo, ['gia', 'bao nhieu tien', 'bao nhieu', 'sale', 'giam', 'khuyen mai', 'san pham', 'sp', 'mua', 'ao', 'quan', 'vay', 'dam', 'hoodie', 'tee', 'thun', 'so mi', 'polo', 'jean', 'kaki', 'chan vay'])) {
        $criteria = extract_product_criteria($text);
        $reply = bot_product_search_advanced($criteria, $text);
        if ($reply) {
            return chat_reply_and_store($reply);
        }
    }

    $contextReply = bot_contextual_followup($text, $tNo);
    if ($contextReply !== null) {
        return chat_reply_and_store($contextReply);
    }

    return chat_reply_and_store(bot_help());
}

function chat_reply_and_store(string $reply): string
{
    chat_memory_push_bot($reply);
    return $reply;
}

function bot_help(): string
{
    return "Mình có thể hỗ trợ nhanh các mục sau:\n"
        . "1) *Tìm sản phẩm*: tên, loại, màu, size, khoảng giá.\n"
        . "   Ví dụ: *áo sơ mi trắng*, *áo dưới 400k*, *quần jean size L*.\n"
        . "2) *Xem giá / khuyến mãi / tồn kho*: hỏi theo tên sản phẩm hoặc SKU.\n"
        . "3) *Đơn hàng*: *đơn #123* hoặc *đơn hàng sđt 090...*\n"
        . "4) *Vận chuyển*: phí ship, thời gian giao.\n"
        . "5) *Đổi trả*: điều kiện và thời hạn.\n"
        . "6) *Tư vấn size*: *cao 170 nặng 65* hoặc *165cm 52kg*.";
}

function bot_shop_info(): string
{
    $s = null;
    if (class_exists('SystemSetting') && method_exists('SystemSetting', 'get')) {
        $s = SystemSetting::get();
    }
    $name = $s['shop_name'] ?? 'Shop';
    $hotline = $s['hotline'] ?? 'Chưa cấu hình';
    $addr = $s['address'] ?? 'Chưa cấu hình';
    $email = $s['email'] ?? 'Chưa cấu hình';

    return "Thông tin shop:\n"
        . "- Tên: *{$name}*\n"
        . "- Hotline: *{$hotline}*\n"
        . "- Địa chỉ: {$addr}\n"
        . "- Email: {$email}";
}

function bot_return_policy(): string
{
    return "Chính sách đổi trả (tham khảo):\n"
        . "- Đổi size hoặc đổi mẫu trong *7 ngày* từ khi nhận hàng.\n"
        . "- Sản phẩm còn tem mác, chưa giặt, chưa qua sử dụng.\n"
        . "- Hàng lỗi do shop: shop hỗ trợ phí đổi trả.\n"
        . "- Đổi do chọn nhầm: khách hỗ trợ phí ship hai chiều.\n"
        . "Bạn có thể gửi *mã đơn* để mình hỗ trợ nhanh hơn.";
}

function bot_shipping_policy(): string
{
    $ship = $_SESSION['_ship_settings'] ?? null;
    $enabled = ($ship['SHIP_ENABLED'] ?? '1') !== '0';
    $fee = (int)($ship['SHIP_FEE'] ?? 0);
    $freeFrom = (int)($ship['SHIP_FREE_FROM'] ?? 0);
    $note = trim((string)($ship['SHIP_NOTE'] ?? ''));

    $msg = "Vận chuyển:\n";
    if (!$enabled) {
        return $msg . "- Hiện shop đang tạm tắt giao hàng.";
    }

    $msg .= "- Phí ship: *" . money($fee) . "*\n";
    if ($freeFrom > 0) {
        $msg .= "- Miễn phí ship từ: *" . money($freeFrom) . "*\n";
    }
    $msg .= "- Thời gian giao dự kiến: nội thành 1-2 ngày, tỉnh 2-5 ngày tùy khu vực.\n";
    if ($note !== '') {
        $msg .= "- Ghi chú: {$note}\n";
    }
    return trim($msg);
}

function bot_size_advice(int $heightCm, int $weightKg, ?string $productType = null): string
{
    $size = 'M';
    if ($heightCm <= 160 && $weightKg <= 52) $size = 'S';
    elseif ($heightCm <= 168 && $weightKg <= 60) $size = 'M';
    elseif ($heightCm <= 176 && $weightKg <= 72) $size = 'L';
    elseif ($heightCm <= 183 && $weightKg <= 82) $size = 'XL';
    else $size = 'XXL';

    $fitNote = 'form vừa';
    if ($productType === 'quan') {
        $fitNote = 'nên ưu tiên xem thêm vòng eo và mông để chọn chuẩn hơn';
    } elseif ($productType === 'ao') {
        $fitNote = 'nếu thích mặc rộng có thể tăng thêm 1 size';
    }

    return "Tư vấn size theo thông tin bạn cung cấp:\n"
        . "- Chiều cao: *{$heightCm}cm*\n"
        . "- Cân nặng: *{$weightKg}kg*\n"
        . "👉 Gợi ý: *size {$size}*.\n"
        . "- Ghi chú: {$fitNote}.\n"
        . "Nếu bạn nói rõ đang xem *áo*, *quần* hay *váy*, mình sẽ tư vấn sát hơn.";
}

function bot_order_lookup(?int $orderId, ?string $phone): string
{
    $db = Database::connection();

    if ($orderId) {
        $st = $db->prepare("SELECT id, customer_name, customer_phone, total, status, payment_method, payment_status, tracking_code, shipping_status, created_at
                            FROM orders WHERE id=? LIMIT 1");
        $st->execute([$orderId]);
        $o = $st->fetch(PDO::FETCH_ASSOC);
        if (!$o) return "Mình không tìm thấy đơn *#{$orderId}*. Bạn kiểm tra lại mã đơn giúp mình nhé.";
        chat_memory_set('last_order_id', (int)$o['id']);
        return format_order($o);
    }

    if ($phone) {
        $st = $db->prepare("SELECT id, customer_name, customer_phone, total, status, payment_method, payment_status, tracking_code, shipping_status, created_at
                            FROM orders WHERE customer_phone LIKE ? ORDER BY id DESC LIMIT 5");
        $st->execute(['%' . $phone]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) return "Mình chưa thấy đơn nào với SĐT *{$phone}*. Bạn kiểm tra lại giúp mình nhé.";

        $msg = "Mình tìm thấy " . count($rows) . " đơn gần nhất theo SĐT *{$phone}*:\n";
        foreach ($rows as $idx => $o) {
            $n = $idx + 1;
            $msg .= "{$n}) #{$o['id']} | " . money((int)$o['total']) . " | {$o['status']} | " . ($o['created_at'] ?? '') . "\n";
        }
        $msg .= "Bạn muốn xem chi tiết đơn nào? Ví dụ: *đơn #{$rows[0]['id']}* hoặc *đơn {$rows[0]['id']}*.";
        chat_memory_set('last_orders', array_map(fn($r) => (int)$r['id'], $rows));
        return $msg;
    }

    return 'Bạn gửi giúp mình *mã đơn* hoặc *SĐT* nhé.';
}

function format_order(array $o): string
{
    $id = (int)$o['id'];
    $total = money((int)$o['total']);
    $stt = map_order_status((string)($o['status'] ?? ''));
    $pm = map_payment_method((string)($o['payment_method'] ?? ''));
    $ps = map_payment_status((string)($o['payment_status'] ?? ''));
    $trk = trim((string)($o['tracking_code'] ?? ''));
    $shipSt = trim((string)($o['shipping_status'] ?? ''));

    $msg = "Chi tiết đơn *#{$id}*:\n"
        . "- Khách: " . ($o['customer_name'] ?? '') . " | " . ($o['customer_phone'] ?? '') . "\n"
        . "- Tổng tiền: *{$total}*\n"
        . "- Trạng thái: *{$stt}*\n"
        . "- Thanh toán: {$pm} / {$ps}\n";
    if ($shipSt !== '') $msg .= "- Tình trạng giao: {$shipSt}\n";
    if ($trk !== '') $msg .= "- Mã vận đơn: *{$trk}*\n";
    if (!empty($o['created_at'])) $msg .= "- Ngày tạo: {$o['created_at']}\n";

    return trim($msg);
}

function bot_product_search_advanced(array $criteria, string $rawText): ?string
{
    $db = Database::connection();
    $limit = 5;

    $params = [];
    $where = ["p.status='active'"];
    $joins = ["JOIN categories c ON c.id = p.category_id"];

    $keyword = trim((string)($criteria['keyword'] ?? ''));
    $normalizedKeyword = normalize_vn(mb_strtolower($keyword, 'UTF-8'));
    $categoryKeyword = detect_category_keyword($normalizedKeyword ?: normalize_vn(mb_strtolower($rawText, 'UTF-8')));
    $color = trim((string)($criteria['color'] ?? ''));
    $size = trim((string)($criteria['size'] ?? ''));
    $priceMin = $criteria['price_min'] ?? null;
    $priceMax = $criteria['price_max'] ?? null;
    $onlySale = (bool)($criteria['only_sale'] ?? false);
    $onlyNew = (bool)($criteria['only_new'] ?? false);
    $stockOnly = (bool)($criteria['stock_only'] ?? false);

    if (!empty($criteria['sku'])) {
        $st = $db->prepare("SELECT p.*, c.name AS category_name
                            FROM products p
                            JOIN categories c ON c.id = p.category_id
                            WHERE p.sku = ?
                            LIMIT 1");
        $st->execute([$criteria['sku']]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return "Mình chưa tìm thấy sản phẩm với mã *{$criteria['sku']}*. Bạn kiểm tra lại SKU giúp mình nhé.";
        }
        chat_memory_set_product_results([$row]);
        $msg = format_product_result_list([$row], "Mình tìm thấy đúng sản phẩm theo SKU:");
        if ($stockOnly) {
            $msg .= "\n\n" . bot_product_stock((int)$row['id'], (string)$row['name'], $size ?: null, $color ?: null);
        }
        return $msg;
    }

    if ($priceMin !== null) {
        $where[] = 'p.price >= ?';
        $params[] = $priceMin;
    }
    if ($priceMax !== null) {
        $where[] = 'p.price <= ?';
        $params[] = $priceMax;
    }
    if ($onlySale) {
        $where[] = 'p.compare_at_price IS NOT NULL AND p.compare_at_price > p.price';
    }
    if ($onlyNew) {
        $where[] = 'p.is_new = 1';
    }
    if ($categoryKeyword !== null) {
        $where[] = '(LOWER(c.name) LIKE ? OR LOWER(c.slug) LIKE ?)';
        $params[] = '%' . $categoryKeyword . '%';
        $params[] = '%' . slugify($categoryKeyword) . '%';
    }

    if ($color !== '' || $size !== '' || $stockOnly) {
        $joins[] = 'LEFT JOIN product_variants v ON v.product_id = p.id';
        if ($size !== '') {
            $where[] = 'LOWER(v.size) = ?';
            $params[] = mb_strtolower($size, 'UTF-8');
        }
        if ($color !== '') {
            $where[] = 'LOWER(v.color) LIKE ?';
            $params[] = '%' . mb_strtolower($color, 'UTF-8') . '%';
        }
        if ($stockOnly) {
            $where[] = 'v.stock > 0';
        }
    }

    if ($keyword !== '') {
        $tokens = array_values(array_filter(explode(' ', $normalizedKeyword)));
        $tokens = array_values(array_filter($tokens, fn($w) => !in_array($w, chat_stop_words(), true)));
        if ($tokens) {
            foreach ($tokens as $token) {
                $where[] = '(LOWER(p.name) LIKE ? OR LOWER(p.slug) LIKE ? OR LOWER(IFNULL(p.sku, "")) LIKE ? OR LOWER(c.name) LIKE ?)';
                $like = '%' . $token . '%';
                array_push($params, $like, $like, $like, $like);
            }
        }
    }

    $sql = "SELECT DISTINCT p.*, c.name AS category_name
            FROM products p
            " . implode("\n", $joins) . "
            WHERE " . implode(' AND ', $where) . "
            ORDER BY
              (CASE WHEN p.compare_at_price IS NOT NULL AND p.compare_at_price > p.price THEN 1 ELSE 0 END) DESC,
              p.is_new DESC,
              p.id DESC
            LIMIT {$limit}";

    $st = $db->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows && $keyword !== '') {
        $fallbackKeyword = trim(remove_generic_product_words($normalizedKeyword));
        if ($fallbackKeyword !== '' && $fallbackKeyword !== $normalizedKeyword) {
            return bot_product_search_advanced([
                'keyword' => $fallbackKeyword,
                'color' => $color,
                'size' => $size,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'only_sale' => $onlySale,
                'only_new' => $onlyNew,
                'stock_only' => $stockOnly,
            ], $rawText);
        }
    }

    if (!$rows) {
        return "Mình chưa tìm thấy sản phẩm phù hợp.\nBạn có thể thử cách hỏi như: *áo sơ mi trắng*, *áo dưới 400k*, *quần jean size L* hoặc *sale áo thun* nhé.";
    }

    chat_memory_set_product_results($rows);
    $title = build_product_result_title($criteria, count($rows));
    $msg = format_product_result_list($rows, $title);

    $msg .= "\n\nBạn có thể hỏi tiếp như:\n"
        . "- *cái 1 còn size gì*\n"
        . "- *mẫu 2 có màu gì*\n"
        . "- *cái đầu tiên giá bao nhiêu*\n"
        . "- *cho mình mẫu dưới 300k*";

    return $msg;
}



function build_consulting_note(array $criteria, array $rows): string
{
    if (!$rows) return '';

    $parts = [];
    $category = detect_category_keyword(normalize_vn(mb_strtolower((string)($criteria['keyword'] ?? ''), 'UTF-8')));
    $priceMax = $criteria['price_max'] ?? null;
    $onlySale = (bool)($criteria['only_sale'] ?? false);
    $color = trim((string)($criteria['color'] ?? ''));
    $size = trim((string)($criteria['size'] ?? ''));

    if ($category === 'so mi' || $category === 'ao so mi') {
        $parts[] = 'Phân tích nhanh: áo sơ mi hợp nhu cầu đi làm, gặp khách hàng hoặc mặc lịch sự hằng ngày.';
        $parts[] = 'Gợi ý chọn: ưu tiên form gọn, màu trắng/xanh nhạt, chất vải ít nhăn và dễ phối với quần tây hoặc jean tối màu.';
    } elseif ($category === 'ao thun') {
        $parts[] = 'Phân tích nhanh: áo thun phù hợp mặc hằng ngày, đi chơi hoặc phối theo phong cách trẻ trung.';
    } elseif ($category === 'quan jean') {
        $parts[] = 'Phân tích nhanh: quần jean hợp đi chơi, đi học, phối linh hoạt với áo thun hoặc sơ mi.';
    }

    if ($priceMax !== null) {
        $parts[] = 'Mình đang ưu tiên các mẫu nằm trong ngân sách bạn đưa ra để dễ chốt hơn.';
    }
    if ($onlySale) {
        $parts[] = 'Danh sách ưu tiên thêm các mẫu đang giảm giá để tối ưu chi phí.';
    }
    if ($color !== '') {
        $parts[] = 'Mình đã lọc theo màu *' . $color . '* để sát nhu cầu hơn.';
    }
    if ($size !== '') {
        $parts[] = 'Mình cũng ưu tiên các biến thể có size *' . $size . '* còn hàng.';
    }

    $parts[] = 'Bạn có thể nói rõ hơn mục đích như *đi làm*, *đi chơi*, *trẻ trung*, *lịch sự*, *dưới 400k* để mình tư vấn sát hơn.';
    return implode("
", $parts);
}

function format_product_result_list(array $rows, string $title): string
{
    $msg = $title . "\n";
    foreach ($rows as $idx => $p) {
        $num = $idx + 1;
        $price = money((int)$p['price']);
        $cmp = (int)($p['compare_at_price'] ?? 0);
        $sale = '';
        if ($cmp > 0 && $cmp > (int)$p['price']) {
            $percent = (int)round((1 - ((int)$p['price'] / $cmp)) * 100);
            $sale = " | SALE ~{$percent}%";
        }
        $cat = !empty($p['category_name']) ? ' | ' . $p['category_name'] : '';
        $msg .= "{$num}) *{$p['name']}*{$cat}\n"
            . "- Giá: {$price}{$sale}\n"
            . "- SKU: " . ($p['sku'] ?: 'Đang cập nhật') . "\n"
            . "- Link: " . url('/p/' . $p['slug']) . "\n";
    }
    return trim($msg);
}

function bot_product_stock(int $productId, string $productName, ?string $sizeFilter = null, ?string $colorFilter = null): string
{
    $db = Database::connection();
    $sql = "SELECT size, color, stock FROM product_variants WHERE product_id=?";
    $params = [$productId];

    if ($sizeFilter !== null && $sizeFilter !== '') {
        $sql .= ' AND LOWER(size)=?';
        $params[] = mb_strtolower($sizeFilter, 'UTF-8');
    }
    if ($colorFilter !== null && $colorFilter !== '') {
        $sql .= ' AND LOWER(color) LIKE ?';
        $params[] = '%' . mb_strtolower($colorFilter, 'UTF-8') . '%';
    }
    $sql .= ' ORDER BY size ASC, color ASC';

    $st = $db->prepare($sql);
    $st->execute($params);
    $vars = $st->fetchAll(PDO::FETCH_ASSOC);

    if (!$vars) {
        return "Mình chưa thấy biến thể phù hợp cho *{$productName}*. Bạn thử hỏi size hoặc màu khác nhé.";
    }

    $summary = [];
    foreach ($vars as $v) {
        $summary[] = trim(($v['size'] ?? '') . ' / ' . ($v['color'] ?? '')) . ': *' . (int)($v['stock'] ?? 0) . '*';
    }
    return "Tồn kho *{$productName}*:\n- " . implode("\n- ", $summary);
}

function bot_followup_choice(int $choice): ?string
{
    $rows = chat_memory_get('last_product_results');
    if (!$rows || empty($rows[$choice - 1])) {
        return null;
    }
    $p = $rows[$choice - 1];
    chat_memory_set('focus_product_id', (int)$p['id']);
    chat_memory_set('focus_product_name', (string)$p['name']);

    $price = money((int)$p['price']);
    $cmp = (int)($p['compare_at_price'] ?? 0);
    $sale = '';
    if ($cmp > 0 && $cmp > (int)$p['price']) {
        $sale = ' | Giá gốc: ' . money($cmp);
    }

    return "Bạn đang xem *{$p['name']}*.\n- Giá hiện tại: *{$price}*{$sale}\n- SKU: " . ($p['sku'] ?: 'Đang cập nhật') . "\n- Link: " . url('/p/' . $p['slug']) . "\nBạn có thể hỏi tiếp: *còn size gì*, *có màu gì*, *còn hàng không* hoặc *mẫu khác tương tự*.";
}

function bot_followup_stock_question(string $text, string $tNo): ?string
{
    $product = resolve_context_product($text, $tNo);
    if (!$product) {
        return null;
    }

    $summary = product_variant_summary((int)$product['id']);
    if (contains_any($tNo, ['mau nao con nhieu', 'mau nao nhieu nhat'])) {
        $top = array_key_first($summary['by_color']);
        if ($top !== null) {
            return 'Màu còn nhiều nhất của *' . $product['name'] . '* là *' . $top . '* với khoảng *' . (int)$summary['by_color'][$top] . '* sản phẩm.';
        }
    }
    if (contains_any($tNo, ['size nao con nhieu', 'size nao nhieu nhat'])) {
        $top = array_key_first($summary['by_size']);
        if ($top !== null) {
            return 'Size còn nhiều nhất của *' . $product['name'] . '* là *' . $top . '* với khoảng *' . (int)$summary['by_size'][$top] . '* sản phẩm.';
        }
    }

    $size = extract_size($text);
    $color = extract_color($tNo);
    return bot_product_stock((int)$product['id'], (string)$product['name'], $size, $color);
}

function bot_contextual_followup(string $text, string $tNo): ?string
{
    $product = resolve_context_product($text, $tNo);
    if ($product && contains_any($tNo, ['gia', 'bao nhieu'])) {
        $price = money((int)$product['price']);
        $cmp = (int)($product['compare_at_price'] ?? 0);
        $msg = "*{$product['name']}* hiện có giá *{$price}*.";
        if ($cmp > 0 && $cmp > (int)$product['price']) {
            $msg .= ' Giá gốc ' . money($cmp) . '.';
        }
        $msg .= ' Bạn muốn mình kiểm tra thêm size, màu, mô tả hay tình trạng còn hàng không?';
        return $msg;
    }

    if ($product && contains_any($tNo, ['mau', 'mau nao', 'co mau gi'])) {
        return bot_list_colors((int)$product['id'], (string)$product['name']);
    }

    if ($product && contains_any($tNo, ['size', 'size nao', 'co size gi'])) {
        return bot_list_sizes((int)$product['id'], (string)$product['name']);
    }

    if ($product && contains_any($tNo, ['chi tiet', 'mo ta', 'thong tin', 'chat lieu', 'form', 'danh muc', 'sku'])) {
        return bot_product_overview($product);
    }

    if ($product && contains_any($tNo, ['tuong tu', 'giong vay', 'giong nhu vay', 'mau khac'])) {
        return bot_related_products((int)$product['id']);
    }

    return null;
}

function bot_list_sizes(int $productId, string $productName): string
{
    $db = Database::connection();
    $st = $db->prepare('SELECT DISTINCT size FROM product_variants WHERE product_id=? ORDER BY size ASC');
    $st->execute([$productId]);
    $rows = array_values(array_filter(array_map(fn($r) => trim((string)$r['size']), $st->fetchAll(PDO::FETCH_ASSOC))));
    if (!$rows) {
        return "*{$productName}* hiện chưa có dữ liệu size trong hệ thống.";
    }
    return "*{$productName}* hiện có các size: *" . implode(', ', $rows) . '*.';
}

function bot_list_colors(int $productId, string $productName): string
{
    $db = Database::connection();
    $st = $db->prepare('SELECT DISTINCT color FROM product_variants WHERE product_id=? ORDER BY color ASC');
    $st->execute([$productId]);
    $rows = array_values(array_filter(array_map(fn($r) => trim((string)$r['color']), $st->fetchAll(PDO::FETCH_ASSOC))));
    if (!$rows) {
        return "*{$productName}* hiện chưa có dữ liệu màu trong hệ thống.";
    }
    return "*{$productName}* hiện có các màu: *" . implode(', ', $rows) . '*.';
}

function bot_product_detail_intent(string $text, string $tNo): ?string
{
    $product = resolve_context_product($text, $tNo);
    if (!$product) {
        return null;
    }

    if (contains_any($tNo, ['size', 'con khong', 'con hang', 'ton kho']) && (extract_size($text) || extract_color($tNo))) {
        return bot_product_stock((int)$product['id'], (string)$product['name'], extract_size($text), extract_color($tNo));
    }

    if (contains_any($tNo, ['chat lieu', 'mo ta', 'chi tiet', 'thong tin', 'form', 'danh muc', 'sku', 'gia goc', 'giam bao nhieu', 'giam may phan tram', 'ma sp', 'ma san pham'])) {
        return bot_product_overview($product);
    }

    return null;
}

function bot_product_overview(array $product): string
{
    $db = Database::connection();
    $st = $db->prepare('SELECT c.name AS category_name, c.slug AS category_slug FROM categories c WHERE c.id=? LIMIT 1');
    $st->execute([(int)$product['category_id']]);
    $cat = $st->fetch(PDO::FETCH_ASSOC) ?: [];

    $variant = product_variant_summary((int)$product['id']);
    $price = money((int)$product['price']);
    $cmp = (int)($product['compare_at_price'] ?? 0);
    $discountText = 'Không giảm giá';
    if ($cmp > 0 && $cmp > (int)$product['price']) {
        $discountText = money($cmp) . ' → ' . $price . ' (giảm ' . product_discount_percent($product) . '%)';
    }

    $desc = trim((string)($product['description'] ?? ''));
    if ($desc === '') {
        $desc = 'Shop chưa cập nhật mô tả chi tiết cho sản phẩm này.';
    }

    $lines = [];
    $lines[] = 'Thông tin sản phẩm *' . $product['name'] . '*:';
    $lines[] = '- Giá bán: *' . $price . '*';
    $lines[] = '- Giá/khuyến mãi: ' . $discountText;
    $lines[] = '- SKU: ' . ((string)($product['sku'] ?? '') !== '' ? $product['sku'] : 'Đang cập nhật');
    $lines[] = '- Danh mục: ' . ($cat['category_name'] ?? ($product['category_name'] ?? 'Đang cập nhật'));
    $lines[] = '- Trạng thái: ' . ((string)($product['status'] ?? '') === 'active' ? 'Đang bán' : 'Tạm ẩn');
    $lines[] = '- Hàng mới: ' . (!empty($product['is_new']) ? 'Có' : 'Không');
    $lines[] = '- Mô tả: ' . $desc;
    if (!empty($variant['sizes'])) $lines[] = '- Size hiện có: *' . implode(', ', $variant['sizes']) . '*';
    if (!empty($variant['colors'])) $lines[] = '- Màu hiện có: *' . implode(', ', $variant['colors']) . '*';
    $lines[] = '- Tổng tồn kho biến thể: *' . (int)$variant['total_stock'] . '*';
    if (!empty($product['thumbnail'])) $lines[] = '- Ảnh: ' . $product['thumbnail'];
    $lines[] = '- Link: ' . url('/p/' . $product['slug']);
    $lines[] = 'Bạn có thể hỏi tiếp: *size M màu trắng còn không*, *màu nào còn nhiều nhất*, *so sánh 1 và 2*.';

    return implode("
", $lines);
}

function product_variant_summary(int $productId): array
{
    $db = Database::connection();
    $st = $db->prepare('SELECT size, color, stock FROM product_variants WHERE product_id=? ORDER BY size ASC, color ASC');
    $st->execute([$productId]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $sizes = [];
    $colors = [];
    $total = 0;
    $bySize = [];
    $byColor = [];
    foreach ($rows as $r) {
        $size = trim((string)($r['size'] ?? ''));
        $color = trim((string)($r['color'] ?? ''));
        $stock = (int)($r['stock'] ?? 0);
        if ($size !== '') $sizes[$size] = true;
        if ($color !== '') $colors[$color] = true;
        $total += $stock;
        if ($size !== '') $bySize[$size] = ($bySize[$size] ?? 0) + $stock;
        if ($color !== '') $byColor[$color] = ($byColor[$color] ?? 0) + $stock;
    }
    arsort($bySize);
    arsort($byColor);
    return [
        'rows' => $rows,
        'sizes' => array_keys($sizes),
        'colors' => array_keys($colors),
        'total_stock' => $total,
        'by_size' => $bySize,
        'by_color' => $byColor,
    ];
}

function product_discount_percent(array $product): int
{
    $cmp = (int)($product['compare_at_price'] ?? 0);
    $price = (int)($product['price'] ?? 0);
    if ($cmp <= 0 || $cmp <= $price || $price <= 0) return 0;
    return (int)round((1 - ($price / $cmp)) * 100);
}

function bot_related_products(int $productId): string
{
    $db = Database::connection();
    $st = $db->prepare('SELECT id, category_id, name, price FROM products WHERE id=? LIMIT 1');
    $st->execute([$productId]);
    $current = $st->fetch(PDO::FETCH_ASSOC);
    if (!$current) return 'Mình chưa tìm thấy sản phẩm để gợi ý thêm.';

    $st = $db->prepare('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id=p.category_id WHERE p.status="active" AND p.category_id=? AND p.id<>? ORDER BY ABS(p.price - ?) ASC, p.id DESC LIMIT 4');
    $st->execute([(int)$current['category_id'], $productId, (int)$current['price']]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    if (!$rows) {
        return 'Hiện mình chưa thấy thêm mẫu tương tự trong cùng danh mục.';
    }
    chat_memory_set_product_results($rows);
    return format_product_result_list($rows, 'Các mẫu gần giống với *' . $current['name'] . '*:');
}

function bot_compare_products(string $text, string $tNo): ?string
{
    $rows = chat_memory_get('last_product_results') ?: [];
    preg_match_all('/(\d{1,2})/', $tNo, $m);
    $nums = array_values(array_unique(array_map('intval', $m[1] ?? [])));
    if (count($nums) < 2) {
        if (contains_any($tNo, ['dau tien va thu hai', '1 va 2'])) $nums = [1, 2];
    }
    if (count($nums) < 2) return null;
    $a = $rows[$nums[0]-1] ?? null;
    $b = $rows[$nums[1]-1] ?? null;
    if (!$a || !$b) return null;

    $sa = product_variant_summary((int)$a['id']);
    $sb = product_variant_summary((int)$b['id']);
    $pa = (int)$a['price'];
    $pb = (int)$b['price'];
    $cheaper = $pa === $pb ? 'Hai mẫu đang có cùng mức giá.' : (($pa < $pb) ? ('*' . $a['name'] . '* rẻ hơn *' . money($pb - $pa) . '*.') : ('*' . $b['name'] . '* rẻ hơn *' . money($pa - $pb) . '*.'));

    return 'So sánh nhanh 2 sản phẩm:
'
        . '1) *' . $a['name'] . '* | ' . money($pa) . ' | size: ' . (implode(', ', $sa['sizes']) ?: 'chưa rõ') . ' | màu: ' . (implode(', ', $sa['colors']) ?: 'chưa rõ') . ' | tồn: ' . (int)$sa['total_stock'] . '
'
        . '2) *' . $b['name'] . '* | ' . money($pb) . ' | size: ' . (implode(', ', $sb['sizes']) ?: 'chưa rõ') . ' | màu: ' . (implode(', ', $sb['colors']) ?: 'chưa rõ') . ' | tồn: ' . (int)$sb['total_stock'] . '
'
        . '- Kết luận: ' . $cheaper;
}

function normalize_vn(string $s): string
{
    $map = [
        'á'=>'a','à'=>'a','ả'=>'a','ã'=>'a','ạ'=>'a','ă'=>'a','ắ'=>'a','ằ'=>'a','ẳ'=>'a','ẵ'=>'a','ặ'=>'a','â'=>'a','ấ'=>'a','ầ'=>'a','ẩ'=>'a','ẫ'=>'a','ậ'=>'a',
        'đ'=>'d',
        'é'=>'e','è'=>'e','ẻ'=>'e','ẽ'=>'e','ẹ'=>'e','ê'=>'e','ế'=>'e','ề'=>'e','ể'=>'e','ễ'=>'e','ệ'=>'e',
        'í'=>'i','ì'=>'i','ỉ'=>'i','ĩ'=>'i','ị'=>'i',
        'ó'=>'o','ò'=>'o','ỏ'=>'o','õ'=>'o','ọ'=>'o','ô'=>'o','ố'=>'o','ồ'=>'o','ổ'=>'o','ỗ'=>'o','ộ'=>'o','ơ'=>'o','ớ'=>'o','ờ'=>'o','ở'=>'o','ỡ'=>'o','ợ'=>'o',
        'ú'=>'u','ù'=>'u','ủ'=>'u','ũ'=>'u','ụ'=>'u','ư'=>'u','ứ'=>'u','ừ'=>'u','ử'=>'u','ữ'=>'u','ự'=>'u',
        'ý'=>'y','ỳ'=>'y','ỷ'=>'y','ỹ'=>'y','ỵ'=>'y'
    ];
    $s = strtr($s, $map);
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
}

function contains_any(string $text, array $needles): bool
{
    foreach ($needles as $n) {
        if ($n !== '' && strpos($text, $n) !== false) return true;
    }
    return false;
}

function extract_height_weight(string $tNo): ?array
{
    $h = null; $w = null;

    if (preg_match('/cao\s*(\d{2,3})/', $tNo, $m)) $h = (int)$m[1];
    if (preg_match('/(\d{2,3})\s*cm/', $tNo, $m)) $h = (int)$m[1];

    if (preg_match('/(?:can|nang)\s*(\d{2,3})/', $tNo, $m)) $w = (int)$m[1];
    if (preg_match('/(\d{2,3})\s*kg/', $tNo, $m)) $w = (int)$m[1];

    if ($h && $w) return ['height_cm' => $h, 'weight_kg' => $w];
    return null;
}

function extract_order_lookup(string $tNo): ?array
{
    $out = [];

    if (preg_match('/#\s*(\d{1,9})/', $tNo, $m)) $out['order_id'] = (int)$m[1];
    if (preg_match('/don\s*(\d{1,9})/', $tNo, $m)) $out['order_id'] = (int)$m[1];
    if (preg_match('/(0\d{8,10})/', $tNo, $m)) $out['phone'] = $m[1];

    return $out ?: null;
}

function extract_product_criteria(string $text): array
{
    $t = mb_strtolower(trim($text), 'UTF-8');
    $tNo = normalize_vn($t);
    $criteria = [
        'keyword' => '',
        'size' => extract_size($text),
        'color' => extract_color($tNo),
        'price_min' => null,
        'price_max' => null,
        'only_sale' => contains_any($tNo, ['sale', 'giam', 'khuyen mai']),
        'only_new' => contains_any($tNo, ['moi', 'new arrival', 'hang moi']),
        'stock_only' => contains_any($tNo, ['ton kho', 'con hang', 'size nao', 'mau nao', 'het hang']),
        'sku' => null,
    ];

    if (preg_match('/[A-Z]{2}-[A-Z]{2}-\d{3}/', mb_strtoupper($text, 'UTF-8'), $m)) {
        $criteria['sku'] = $m[0];
        return $criteria;
    }

    $price = extract_price_range($tNo);
    if ($price) {
        $criteria['price_min'] = $price['min'];
        $criteria['price_max'] = $price['max'];
    }

    $keyword = remove_generic_product_words($tNo);
    $keyword = preg_replace('/\bsize\s*(s|m|l|xl|xxl|xxxl)\b/', ' ', $keyword);
    $keyword = preg_replace('/\b(mau|color)\s+[a-z]+\b/', ' ', $keyword);
    $keyword = preg_replace('/\bduoi\s*\d+[k]?(?:\s*den\s*\d+[k]?)?\b/', ' ', $keyword);
    $keyword = preg_replace('/\btren\s*\d+[k]?\b/', ' ', $keyword);
    $keyword = preg_replace('/\btu\s*\d+[k]?\s*den\s*\d+[k]?\b/', ' ', $keyword);
    $keyword = trim(preg_replace('/\s+/', ' ', $keyword));
    $criteria['keyword'] = $keyword;

    return $criteria;
}

function extract_price_range(string $tNo): ?array
{
    if (preg_match('/tu\s*(\d+[\.,]?\d*)\s*(k|nghin|tr|trieu)?\s*den\s*(\d+[\.,]?\d*)\s*(k|nghin|tr|trieu)?/', $tNo, $m)) {
        return [
            'min' => normalize_price_unit($m[1], $m[2] ?? ''),
            'max' => normalize_price_unit($m[3], $m[4] ?? ''),
        ];
    }
    if (preg_match('/duoi\s*(\d+[\.,]?\d*)\s*(k|nghin|tr|trieu)?/', $tNo, $m)) {
        return ['min' => null, 'max' => normalize_price_unit($m[1], $m[2] ?? '')];
    }
    if (preg_match('/tren\s*(\d+[\.,]?\d*)\s*(k|nghin|tr|trieu)?/', $tNo, $m)) {
        return ['min' => normalize_price_unit($m[1], $m[2] ?? ''), 'max' => null];
    }
    if (preg_match('/(\d+[\.,]?\d*)\s*(k|nghin|tr|trieu)\b/', $tNo, $m)) {
        $value = normalize_price_unit($m[1], $m[2] ?? '');
        return ['min' => max(0, $value - 50000), 'max' => $value + 50000];
    }
    return null;
}

function normalize_price_unit(string $number, string $unit): int
{
    $number = (float)str_replace(',', '.', $number);
    $unit = trim($unit);
    if (in_array($unit, ['tr', 'trieu'], true)) {
        return (int)round($number * 1000000);
    }
    if (in_array($unit, ['k', 'nghin'], true)) {
        return (int)round($number * 1000);
    }
    return (int)round($number);
}

function extract_size(string $text): ?string
{
    if (preg_match('/\b(?:size\s*)?(xxxl|xxl|xl|l|m|s|f|free size|freesize|28|29|30|31|32|33|34|35|36)\b/i', $text, $m)) {
        return strtoupper($m[1]);
    }
    return null;
}

function extract_color(string $tNo): ?string
{
    $colors = ['trang', 'den', 'xanh', 'xanh den', 'xanh dam', 'xanh nhat', 'xanh duong', 'xanh navy', 'do', 'hong', 'vang', 'xam', 'xam tro', 'nau', 'be', 'kem', 'tim', 'cam'];
    foreach ($colors as $color) {
        if (preg_match('/\b' . preg_quote($color, '/') . '\b/', $tNo)) {
            return $color;
        }
    }
    return null;
}

function parse_list_choice(string $tNo): ?int
{
    if (preg_match('/\b(?:cai|mau|san pham|sp|so)\s*(\d{1,2})\b/', $tNo, $m)) {
        return (int)$m[1];
    }
    if (contains_any($tNo, ['cai dau tien', 'mau dau tien', 'san pham dau tien'])) return 1;
    if (contains_any($tNo, ['cai thu hai', 'mau thu hai', 'san pham thu hai'])) return 2;
    if (contains_any($tNo, ['cai thu ba', 'mau thu ba', 'san pham thu ba'])) return 3;
    return null;
}

function resolve_context_product(string $text, string $tNo): ?array
{
    if ($choice = parse_list_choice($tNo)) {
        $rows = chat_memory_get('last_product_results');
        if ($rows && !empty($rows[$choice - 1])) {
            $p = $rows[$choice - 1];
            chat_memory_set('focus_product_id', (int)$p['id']);
            chat_memory_set('focus_product_name', (string)$p['name']);
            return $p;
        }
    }

    $focusId = chat_memory_get('focus_product_id');
    if ($focusId) {
        $db = Database::connection();
        $st = $db->prepare('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id=p.category_id WHERE p.id=? LIMIT 1');
        $st->execute([(int)$focusId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row) return $row;
    }

    $criteria = extract_product_criteria($text);
    if (!empty($criteria['sku']) || !empty($criteria['keyword'])) {
        $replyRows = bot_try_find_product_rows($criteria, $text);
        if ($replyRows) {
            chat_memory_set_product_results($replyRows);
            $first = $replyRows[0];
            chat_memory_set('focus_product_id', (int)$first['id']);
            chat_memory_set('focus_product_name', (string)$first['name']);
            return $first;
        }
    }

    return null;
}

function bot_try_find_product_rows(array $criteria, string $rawText): array
{
    $db = Database::connection();
    if (!empty($criteria['sku'])) {
        $st = $db->prepare('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id=p.category_id WHERE p.sku=? LIMIT 5');
        $st->execute([$criteria['sku']]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    $keyword = trim((string)($criteria['keyword'] ?? ''));
    if ($keyword === '') return [];

    $tokens = array_values(array_filter(explode(' ', normalize_vn(mb_strtolower($keyword, 'UTF-8')))));
    $tokens = array_values(array_filter($tokens, fn($w) => !in_array($w, chat_stop_words(), true)));
    if (!$tokens) return [];

    $where = ["p.status='active'"];
    $params = [];
    foreach ($tokens as $token) {
        $where[] = '(LOWER(p.name) LIKE ? OR LOWER(p.slug) LIKE ? OR LOWER(IFNULL(p.sku, "")) LIKE ?)';
        $like = '%' . $token . '%';
        array_push($params, $like, $like, $like);
    }
    $sql = 'SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id=p.category_id WHERE ' . implode(' AND ', $where) . ' ORDER BY p.id DESC LIMIT 5';
    $st = $db->prepare($sql);
    $st->execute($params);
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function detect_category_keyword(string $tNo): ?string
{
    $map = [
        'ao so mi' => ['ao so mi', 'so mi'],
        'ao thun' => ['ao thun', 'thun', 'tee'],
        'ao polo' => ['polo'],
        'hoodie' => ['hoodie'],
        'quan jean' => ['quan jean', 'jean'],
        'quan kaki' => ['quan kaki', 'kaki'],
        'chan vay' => ['chan vay'],
        'vay' => ['vay', 'dam'],
        'ao' => ['ao'],
        'quan' => ['quan'],
    ];
    foreach ($map as $canonical => $keys) {
        foreach ($keys as $k) {
            if (strpos($tNo, $k) !== false) return $canonical;
        }
    }
    return null;
}

function build_product_result_title(array $criteria, int $count): string
{
    $parts = [];
    if (!empty($criteria['keyword'])) $parts[] = trim((string)$criteria['keyword']);
    if (!empty($criteria['color'])) $parts[] = 'màu ' . $criteria['color'];
    if (!empty($criteria['size'])) $parts[] = 'size ' . $criteria['size'];
    if (!empty($criteria['only_sale'])) $parts[] = 'đang giảm giá';
    if (!empty($criteria['only_new'])) $parts[] = 'hàng mới';
    if (!empty($criteria['price_max']) && empty($criteria['price_min'])) $parts[] = 'dưới ' . money((int)$criteria['price_max']);
    if (!empty($criteria['price_min']) && empty($criteria['price_max'])) $parts[] = 'trên ' . money((int)$criteria['price_min']);
    if (!empty($criteria['price_min']) && !empty($criteria['price_max'])) $parts[] = 'từ ' . money((int)$criteria['price_min']) . ' đến ' . money((int)$criteria['price_max']);

    if (!$parts) {
        return "Mình tìm thấy {$count} sản phẩm phù hợp:";
    }
    return 'Mình tìm thấy ' . $count . ' sản phẩm cho yêu cầu *' . implode(', ', $parts) . '*:';
}

function remove_generic_product_words(string $tNo): string
{
    $remove = [
        'cho minh', 'tim', 'xem', 'hoi', 'muon', 'can', 'lay', 'co', 'khong', 'voi', 'san pham', 'sp',
        'gia', 'bao nhieu', 'khuyen mai', 'sale', 'giam', 'con hang', 'ton kho', 'het hang', 'mau nao',
        'size nao', 'cai', 'mau', 'san pham', 'giup', 'nhe', 'a', 'shop', 'tu van', 'thong tin', 'chi tiet', 'mo ta', 'chat lieu', 'form', 'sku', 'ma sp', 'ma san pham'
    ];
    foreach ($remove as $r) {
        $tNo = str_replace($r, ' ', $tNo);
    }
    return trim(preg_replace('/\s+/', ' ', $tNo));
}

function chat_stop_words(): array
{
    return ['ao', 'quan', 'vay', 'dam', 'mau', 'size', 'san', 'pham', 'shop', 'cho', 'minh', 'tim', 'xem', 'thong', 'tin', 'chi', 'tiet', 'mo', 'ta'];
}

function detect_product_type(string $tNo): ?string
{
    if (strpos($tNo, 'quan') !== false) return 'quan';
    if (strpos($tNo, 'ao') !== false) return 'ao';
    if (strpos($tNo, 'vay') !== false || strpos($tNo, 'dam') !== false) return 'vay';
    return null;
}

function map_order_status(string $status): string
{
    return [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'picking' => 'Chờ lấy hàng',
        'shipping' => 'Đang giao',
        'completed' => 'Hoàn thành',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã hủy',
    ][$status] ?? $status;
}

function map_payment_method(string $method): string
{
    return [
        'cod' => 'Thanh toán khi nhận hàng',
        'qr' => 'Quét mã QR',
        'bank' => 'Chuyển khoản',
        'card' => 'Thẻ',
    ][$method] ?? ($method ?: 'Chưa cập nhật');
}

function map_payment_status(string $status): string
{
    return [
        'unpaid' => 'Chưa thanh toán',
        'paid' => 'Đã thanh toán',
        'failed' => 'Thanh toán lỗi',
    ][$status] ?? ($status ?: 'Chưa cập nhật');
}

function is_smalltalk_greeting(string $tNo): bool
{
    return contains_any($tNo, ['xin chao', 'chao shop', 'hello', 'hi', 'helo', 'alo']);
}

function is_smalltalk_thanks(string $tNo): bool
{
    return contains_any($tNo, ['cam on', 'thanks', 'thank you']);
}

function is_smalltalk_bye(string $tNo): bool
{
    return contains_any($tNo, ['tam biet', 'bye', 'hen gap lai']);
}

function chat_memory_boot(): void
{
    if (!isset($_SESSION['_chat_ctx']) || !is_array($_SESSION['_chat_ctx'])) {
        $_SESSION['_chat_ctx'] = [
            'history' => [],
            'last_product_results' => [],
            'focus_product_id' => null,
            'focus_product_name' => null,
            'last_order_id' => null,
            'last_orders' => [],
        ];
    }
}

function chat_memory_push_user(string $text): void
{
    $_SESSION['_chat_ctx']['history'][] = ['role' => 'user', 'text' => $text, 'at' => time()];
    $_SESSION['_chat_ctx']['history'] = array_slice($_SESSION['_chat_ctx']['history'], -12);
}

function chat_memory_push_bot(string $text): void
{
    $_SESSION['_chat_ctx']['history'][] = ['role' => 'bot', 'text' => $text, 'at' => time()];
    $_SESSION['_chat_ctx']['history'] = array_slice($_SESSION['_chat_ctx']['history'], -12);
}

function chat_memory_set(string $key, $value): void
{
    chat_memory_boot();
    $_SESSION['_chat_ctx'][$key] = $value;
}

function chat_memory_get(string $key)
{
    chat_memory_boot();
    return $_SESSION['_chat_ctx'][$key] ?? null;
}

function chat_memory_set_product_results(array $rows): void
{
    $sanitized = [];
    foreach ($rows as $r) {
        $sanitized[] = [
            'id' => (int)$r['id'],
            'name' => (string)$r['name'],
            'slug' => (string)$r['slug'],
            'sku' => (string)($r['sku'] ?? ''),
            'price' => (int)$r['price'],
            'compare_at_price' => (int)($r['compare_at_price'] ?? 0),
            'category_name' => (string)($r['category_name'] ?? ''),
        ];
    }
    chat_memory_set('last_product_results', $sanitized);
    if (!empty($sanitized[0])) {
        chat_memory_set('focus_product_id', (int)$sanitized[0]['id']);
        chat_memory_set('focus_product_name', (string)$sanitized[0]['name']);
    }
}

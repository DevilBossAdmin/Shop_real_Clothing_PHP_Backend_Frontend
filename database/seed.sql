-- USE shop;

-- INSERT INTO categories (id, parent_id, name, slug, sort_order) VALUES
-- (1, NULL, 'Nam', 'nam', 1),
-- (2, NULL, 'Nữ', 'nu', 2),
-- (3, NULL, 'Phụ kiện', 'phu-kien', 3),
-- (4, 1, 'Áo', 'ao-nam', 1),
-- (5, 1, 'Quần', 'quan-nam', 2),
-- (6, 2, 'Áo', 'ao-nu', 1),
-- (7, 2, 'Quần', 'quan-nu', 2),
-- (8, 4, 'Áo thun', 'ao-thun-nam', 1),
-- (9, 4, 'Áo sơ mi', 'ao-so-mi-nam', 2),
-- (10, 6, 'Áo thun', 'ao-thun-nu', 1);

-- INSERT INTO products (category_id, name, slug, sku, price, compare_at_price, thumbnail, description, is_new, status) VALUES
-- (8, 'Áo thun nam basic', 'ao-thun-nam-basic', 'ATN-TEE-001', 199000, 249000, 'https://via.placeholder.com/800x800?text=Ao+thun+nam', 'Cotton thoáng mát, form dễ mặc.', 1, 'active'),
-- (8, 'Áo thun nam in chữ', 'ao-thun-nam-in-chu', 'ATN-TEE-002', 229000, NULL, 'https://via.placeholder.com/800x800?text=Ao+thun+chu', 'In bền màu, phù hợp đi chơi.', 1, 'active'),
-- (9, 'Áo sơ mi nam oxford', 'ao-so-mi-nam-oxford', 'ATN-SHIRT-001', 329000, 399000, 'https://via.placeholder.com/800x800?text=So+mi+oxford', 'Chất oxford đứng form, lịch sự.', 1, 'active'),
-- (5, 'Quần jean nam slim', 'quan-jean-nam-slim', 'ATN-JEAN-001', 399000, 499000, 'https://via.placeholder.com/800x800?text=Jean+slim', 'Co giãn nhẹ, tôn dáng.', 1, 'active'),
-- (10, 'Áo thun nữ crop', 'ao-thun-nu-crop', 'ATN-TEE-101', 179000, 219000, 'https://via.placeholder.com/800x800?text=Crop+tee', 'Dáng crop trẻ trung.', 1, 'active'),
-- (7, 'Quần ống rộng nữ', 'quan-ong-rong-nu', 'ATN-PANT-101', 359000, NULL, 'https://via.placeholder.com/800x800?text=Wide+pants', 'Ống rộng thoải mái, dễ phối.', 1, 'active'),
-- (3, 'Nón lưỡi trai', 'non-luoi-trai', 'ATN-ACC-001', 149000, NULL, 'https://via.placeholder.com/800x800?text=Cap', 'Phụ kiện basic.', 1, 'active');

-- -- Variants (size/color/stock)
-- INSERT INTO product_variants (product_id, size, color, stock) VALUES
-- (1, 'S', 'Đen', 20), (1, 'M', 'Đen', 30), (1, 'L', 'Đen', 15),
-- (1, 'M', 'Trắng', 25), (1, 'L', 'Trắng', 10),
-- (2, 'M', 'Đen', 18), (2, 'L', 'Đen', 12),
-- (3, 'M', 'Xanh', 8), (3, 'L', 'Xanh', 6),
-- (4, '29', 'Xanh đậm', 10), (4, '30', 'Xanh đậm', 14),
-- (5, 'S', 'Trắng', 22), (5, 'M', 'Trắng', 16),
-- (6, 'S', 'Be', 12), (6, 'M', 'Be', 15),
-- (7, 'F', 'Đen', 50);

-- -- Create a demo customer account:
-- -- email: demo@shop.local  password: 123456
-- INSERT INTO users (name, email, password_hash)
-- VALUES ('Demo User', 'demo@shop.local', '$2y$10$CwTycUXWue0Thq9StjUM0uJ8dXRu1oKp9c5fOQxwYxj6Jm8bGZ3Ue');

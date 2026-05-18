<?php
$cfg = require __DIR__ . '/../../config.php';
$menu = Category::tree();
$u = auth_user();
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e($cfg['site']['name']) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= e(url('/assets/css/style.css')) ?>">

  <style>
    .mega-left{
      display: flex;
      flex-direction: column;
      gap: 22px;
      border-right: 1px solid #eee;
      padding-right: 20px;
    }

    .mega-left a{
      display: block;
      text-decoration: none;
      color: #666;
      font-size: 18px;
      font-weight: 600;
      line-height: 1.3;
      transition: .2s;
    }

    .mega-left a:hover{
      color: #111;
      transform: translateX(4px);
    }

    .mega-right{
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
      align-items: start;
    }

    .mega-product{
      display: block;
      text-decoration: none;
      color: #333;
    }

    .mega-product img{
      width: 100%;
      height: 150px;
      object-fit: cover;
      border-radius: 6px;
      background: #f6f6f6;
      display: block;
    }

    .mega-product-name{
      margin-top: 10px;
      font-size: 16px;
      color: #666;
      line-height: 1.4;
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }

    .navbar,
    .navbar .container,
    .navbar-collapse,
    .navbar-nav{
      overflow: visible !important;
    }

    .header-main-row{
      display: grid;
      grid-template-columns: 160px 1fr 360px;
      align-items: center;
      min-height: 78px;
      width: 100%;
      column-gap: 24px;
      position: relative;
    }

    .header-logo-wrap{
      display: flex;
      align-items: center;
      justify-content: flex-start;
      z-index: 3;
    }

    .header-center-nav{
      position: relative;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 2;
    }

    .header-center-nav .navbar-nav{
      display: flex;
      flex-direction: row;
      gap: 34px;
      align-items: center;
      justify-content: center;
      margin: 0;
      padding: 0;
      list-style: none;
    }

    .header-center-nav .nav-link{
      font-size: 18px;
      font-weight: 600;
      text-transform: uppercase;
      color: #222;
      letter-spacing: .4px;
      padding: 18px 0;
      text-align: center;
      white-space: nowrap;
    }

    .header-center-nav .nav-link:hover{
      color: #000;
    }

    .header-search-wrap{
      width: 360px;
      max-width: 360px;
      justify-self: end;
      z-index: 3;
    }

    .mega-menu-item{
      position: static;
    }

    .mega-dropdown{
      position: absolute;
      top: 100%;
      left: 50%;
      transform: translateX(-50%);
      width: 980px;
      max-width: 92vw;
      background: #fff;
      display: none;
      grid-template-columns: 260px 1fr;
      gap: 28px;
      padding: 24px;
      box-shadow: 0 12px 28px rgba(0,0,0,0.12);
      z-index: 9999;
      border-radius: 0 0 10px 10px;
    }

    .mega-menu-item:hover .mega-dropdown{
      display: grid;
    }

    @media (max-width: 1199px){
      .header-main-row{
        grid-template-columns: 140px 1fr 280px;
        column-gap: 18px;
      }

      .header-center-nav .navbar-nav{
        gap: 22px;
      }

      .header-center-nav .nav-link{
        font-size: 16px;
      }

      .header-search-wrap{
        width: 280px;
        max-width: 280px;
      }

      .mega-dropdown{
        width: 900px;
        max-width: 94vw;
      }
    }

    @media (max-width: 991px){
      .header-main-row{
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        min-height: auto;
      }

      .header-logo-wrap{
        width: auto;
      }

      .header-center-nav{
        width: 100%;
        order: 3;
        justify-content: center;
      }

      .header-center-nav .navbar-nav{
        flex-wrap: wrap;
        gap: 16px;
      }

      .header-center-nav .nav-link{
        font-size: 15px;
        padding: 10px 0;
      }

      .header-search-wrap{
        width: 100%;
        max-width: 100%;
        order: 2;
        justify-self: auto;
      }

      .mega-dropdown{
        position: static;
        transform: none;
        width: 100%;
        max-width: 100%;
        grid-template-columns: 1fr;
      }

      .mega-left{
        border-right: none;
        padding-right: 0;
        border-bottom: 1px solid #eee;
        padding-bottom: 16px;
      }

      .mega-right{
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
<header class="border-bottom bg-white sticky-top">
  <div class="topbar small py-2">
    <div class="container d-flex justify-content-between align-items-center">
      <div>📞 <?= e($cfg['site']['hotline']) ?></div>
      <div class="d-flex gap-3 align-items-center">
        <a class="text-decoration-none" href="<?= e(url('/cart')) ?>">Giỏ hàng (<?= cart_count() ?>)</a>
        <?php if ($u): ?>
          <a class="text-decoration-none" href="<?= e(url('/account')) ?>"><?= e($u['name']) ?></a>
          <a class="text-decoration-none" href="<?= e(url('/logout')) ?>">Đăng xuất</a>
        <?php else: ?>
          <a class="text-decoration-none" href="<?= e(url('/login')) ?>">Đăng nhập</a>
        <?php endif; ?>
        <a class="text-decoration-none text-muted" href="<?= e(url('/admin')) ?>">Admin</a>
      </div>
    </div>
  </div>

  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="nav">
        <div class="header-main-row">
          <div class="header-logo-wrap">
            <a class="navbar-brand fw-bold m-0" href="<?= e(url('/')) ?>">T&T</a>
          </div>

          <div class="header-center-nav">
            <ul class="navbar-nav">
              <li class="nav-item">
                <a class="nav-link" href="<?= e(url('/')) ?>">Trang chủ</a>
              </li>

              <?php foreach ($menu as $c): ?>
                <?php if ($c['slug'] === 'ao-xuan-he' || $c['slug'] === 'quan' || $c['slug'] === 'phu-kien'): ?>
                  <li class="nav-item mega-menu-item">
                    <a class="nav-link" href="<?= e(url('/c/' . $c['slug'])) ?>">
                      <?= e($c['name']) ?>
                    </a>

                    <?php if (!empty($c['children'])): ?>
                      <?php
                      $goiY = [];
                      foreach ($c['children'] as $child) {
                          $items = Product::listActive(3, 0, (int)$child['id']);
                          foreach ($items as $it) {
                              $goiY[] = $it;
                              if (count($goiY) >= 3) {
                                  break 2;
                              }
                          }
                      }
                      ?>

                      <div class="mega-dropdown">
                        <div class="mega-left">
                          <?php foreach ($c['children'] as $child): ?>
                            <a href="<?= e(url('/c/' . $child['slug'])) ?>">
                              <?= e($child['name']) ?>
                            </a>
                          <?php endforeach; ?>
                        </div>

                        <div class="mega-right">
                          <?php foreach ($goiY as $p): ?>
                            <?php
                            $thumb = $p['thumbnail'] ?? '';
                            if ($thumb && strpos($thumb, '/') === 0) {
                                $thumbUrl = url($thumb);
                            } elseif ($thumb) {
                                $thumbUrl = url('/uploads/' . $thumb);
                            } else {
                                $thumbUrl = url('/assets/img/no-image.png');
                            }
                            ?>
                            <a class="mega-product" href="<?= e(url('/p/' . $p['slug'])) ?>">
                              <img src="<?= e($thumbUrl) ?>" alt="<?= e($p['name']) ?>">
                              <div class="mega-product-name"><?= e($p['name']) ?></div>
                            </a>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    <?php endif; ?>
                  </li>
                <?php endif; ?>
              <?php endforeach; ?>
            </ul>
          </div>

          <div class="header-search-wrap">
            <form class="d-flex" method="get" action="<?= e(url('/search')) ?>">
              <div class="position-relative w-100">
                <input
                  id="searchInput"
                  class="form-control"
                  name="q"
                  value="<?= e($_GET['q'] ?? '') ?>"
                  placeholder="Tìm kiếm sản phẩm..."
                  autocomplete="off"
                >
                <div
                  id="searchSuggestBox"
                  class="list-group position-absolute w-100 shadow"
                  style="z-index:9999; top:calc(100% + 6px); display:none; max-height:340px; overflow:auto;"
                ></div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </nav>
</header>

<main class="py-4">

<script>
(function () {
  const input = document.getElementById('searchInput');
  const box = document.getElementById('searchSuggestBox');
  if (!input || !box) return;

  let timer = null;
  let lastQ = '';
  let activeIndex = -1;
  let items = [];

  function hideBox() {
    box.style.display = 'none';
    box.innerHTML = '';
    activeIndex = -1;
    items = [];
  }

  function showBox() {
    box.style.display = 'block';
  }

  function setActive(idx) {
    activeIndex = idx;
    const links = box.querySelectorAll('a[data-idx]');
    links.forEach(a => a.classList.remove('active'));
    const cur = box.querySelector(`a[data-idx="${idx}"]`);
    if (cur) cur.classList.add('active');
  }

  function render(data) {
    items = data || [];
    if (!items.length) {
      hideBox();
      return;
    }

    const uploadsBase = "<?= e(url('/uploads/')) ?>";
    const productBase = "<?= e(url('/p/')) ?>";
    const rootBase = "<?= e(url('')) ?>";

    box.innerHTML = items.map((it, idx) => {
      const price = (it.price ?? 0).toLocaleString('vi-VN') + '₫';
      let thumbUrl = null;

      if (it.thumbnail) {
        if (String(it.thumbnail).startsWith('/')) {
          thumbUrl = rootBase + it.thumbnail;
        } else {
          thumbUrl = uploadsBase + it.thumbnail;
        }
      }

      const imgHtml = thumbUrl
        ? `<img src="${thumbUrl}" style="width:34px;height:34px;object-fit:cover;border-radius:8px;margin-right:10px" onerror="this.style.display='none'">`
        : `<div style="width:34px;height:34px;border-radius:8px;margin-right:10px;background:#f1f3f5"></div>`;

      const href = productBase + it.slug;

      return `
        <a class="list-group-item list-group-item-action d-flex align-items-center" href="${href}" data-idx="${idx}">
          ${imgHtml}
          <div class="flex-grow-1">
            <div class="fw-semibold" style="line-height:1.2">${it.name}</div>
            <div class="text-muted small">${price}</div>
          </div>
        </a>
      `;
    }).join('');

    activeIndex = -1;
    showBox();
  }

  async function fetchSuggest(q) {
    const url = "<?= e(url('/index.php')) ?>" + "?r=/search/suggest&q=" + encodeURIComponent(q);
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) return [];
    return await res.json();
  }

  function debounce(fn, ms) {
    clearTimeout(timer);
    timer = setTimeout(fn, ms);
  }

  input.addEventListener('input', () => {
    const q = input.value.trim();
    if (q.length < 1) {
      hideBox();
      return;
    }

    debounce(async () => {
      if (q === lastQ) return;
      lastQ = q;

      try {
        const data = await fetchSuggest(q);
        if (input.value.trim() !== q) return;
        render(data);
      } catch (e) {
        hideBox();
      }
    }, 200);
  });

  input.addEventListener('keydown', (e) => {
    if (box.style.display === 'none') return;

    const max = items.length - 1;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setActive(Math.min(max, activeIndex + 1));
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      setActive(Math.max(0, activeIndex - 1));
    } else if (e.key === 'Enter') {
      const cur = box.querySelector(`a[data-idx="${activeIndex}"]`);
      if (cur) {
        e.preventDefault();
        window.location.href = cur.getAttribute('href');
      }
    } else if (e.key === 'Escape') {
      hideBox();
    }
  });

  document.addEventListener('click', (e) => {
    if (!box.contains(e.target) && e.target !== input) {
      hideBox();
    }
  });

  input.addEventListener('focus', () => {
    if (box.innerHTML.trim() !== '') {
      showBox();
    }
  });
})();
</script>
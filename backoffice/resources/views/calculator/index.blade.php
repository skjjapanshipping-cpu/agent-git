<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>คำนวณค่าสินค้าและค่าขนส่ง | SKJ Japan Shipping</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #e53e3e;
            --primary-dark: #c53030;
            --primary-light: #fed7d7;
            --accent: #2b6cb0;
            --accent-light: #bee3f8;
            --bg: #f7fafc;
            --card: #ffffff;
            --text: #2d3748;
            --text-light: #718096;
            --border: #e2e8f0;
            --success: #38a169;
            --warning: #d69e2e;
            --radius: 12px;
            --shadow: 0 4px 24px rgba(0,0,0,0.07);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Sarabun', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff;
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(229,62,62,0.3);
        }
        .header-inner {
            max-width: 900px;
            margin: 0 auto;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .header-logo {
            width: 46px; height: 46px;
            background: #fff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            position: relative;
        }
        .header-logo i {
            font-size: 20px;
            background: linear-gradient(135deg, #e53e3e 0%, #2b6cb0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .header h1 { font-size: 1.2rem; font-weight: 700; }
        .header p { font-size: 0.82rem; opacity: 0.9; margin-top: 2px; }

        /* Container */
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 24px 16px 60px;
        }

        /* Card */
        .card {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .card-header {
            padding: 16px 20px;
            font-weight: 700;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--border);
        }
        .card-header i { font-size: 1.1rem; }
        .card-header.red { background: var(--primary-light); color: var(--primary-dark); }
        .card-header.blue { background: var(--accent-light); color: var(--accent); }
        .card-header.green { background: #c6f6d5; color: #276749; }
        .card-body { padding: 20px; }

        /* URL Input */
        .url-group {
            display: flex;
            gap: 10px;
        }
        .url-group input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.2s;
        }
        .url-group input:focus {
            outline: none;
            border-color: var(--primary);
        }
        .btn-fetch {
            padding: 12px 24px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-fetch:hover { background: var(--primary-dark); }
        .btn-fetch:disabled { opacity: 0.6; cursor: wait; }
        .btn-fetch .spinner {
            display: none;
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        .btn-fetch.loading .spinner { display: inline-block; }
        .btn-fetch.loading .btn-text { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Product Preview */
        .product-preview {
            display: none;
            margin-top: 16px;
            padding: 16px;
            background: #f7fafc;
            border-radius: 8px;
            border: 1px solid var(--border);
        }
        .product-preview.show { display: flex; gap: 16px; align-items: flex-start; }
        .product-preview img {
            width: 100px; height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border);
            flex-shrink: 0;
        }
        .product-info { flex: 1; }
        .product-info .site-badge {
            display: inline-block;
            padding: 2px 10px;
            background: var(--primary);
            color: #fff;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .product-info h3 {
            font-size: 0.95rem;
            font-weight: 600;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .product-prices {
            margin-top: 8px;
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        .product-prices .price-tag {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
        }
        .product-prices .shipping-tag {
            font-size: 0.9rem;
            color: var(--text-light);
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .form-grid.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
        .form-group { display: flex; flex-direction: column; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 6px;
        }
        .form-group input, .form-group select {
            padding: 10px 14px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.2s;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--accent);
        }
        .form-group .unit {
            font-size: 0.82rem;
            color: var(--text-light);
            margin-top: 4px;
        }

        /* Result Rows */
        .result-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }
        .result-row:last-child { border-bottom: none; }
        .result-row .label {
            font-size: 0.9rem;
            color: var(--text-light);
        }
        .result-row .value {
            font-size: 1rem;
            font-weight: 600;
            text-align: right;
        }
        .result-row .sub {
            font-size: 0.8rem;
            color: var(--text-light);
            font-weight: 400;
        }

        /* Total */
        .total-box {
            margin-top: 4px;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 10px;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .total-box .total-label { font-size: 1rem; font-weight: 600; }
        .total-box .total-value { font-size: 1.5rem; font-weight: 700; }
        .total-box .total-sub { font-size: 0.85rem; opacity: 0.85; }

        /* Note */
        .note {
            margin-top: 12px;
            padding: 14px 18px;
            background: #fffbeb;
            border: 1px solid #f6e05e;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #744210;
        }
        .note i { margin-right: 6px; }

        /* Error */
        .error-msg {
            display: none;
            margin-top: 12px;
            padding: 12px 16px;
            background: #fed7d7;
            border: 1px solid #fc8181;
            border-radius: 8px;
            font-size: 0.88rem;
            color: #9b2c2c;
        }
        .error-msg.show { display: block; }

        /* Btn Calculate */
        .btn-calc {
            width: 100%;
            padding: 14px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 8px;
        }
        .btn-calc:hover { background: #2c5282; }

        /* Responsive */
        @media (max-width: 600px) {
            .header-inner { padding: 14px 16px; }
            .header h1 { font-size: 1rem; }
            .url-group { flex-direction: column; }
            .btn-fetch { justify-content: center; }
            .form-grid { grid-template-columns: 1fr; }
            .form-grid.cols-3 { grid-template-columns: 1fr 1fr; }
            .product-preview.show { flex-direction: column; }
            .product-preview img { width: 100%; height: 180px; }
            .total-box { flex-direction: column; text-align: center; gap: 6px; }
        }

        /* Quick links */
        .quick-links {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 12px;
        }
        .quick-links a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: #f7fafc;
            border: 1px solid var(--border);
            border-radius: 20px;
            font-size: 0.8rem;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.2s;
        }
        .quick-links a:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary-dark);
        }

        /* Divider */
        .divider {
            height: 1px;
            background: var(--border);
            margin: 16px 0;
        }

        /* Hidden */
        .hidden { display: none !important; }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <div class="header-inner">
        <div class="header-logo">
                <i class="fas fa-calculator"></i>
            </div>
        <div>
            <h1>คำนวณค่าสินค้าและค่าขนส่ง</h1>
            <p>ประมาณค่าใช้จ่ายทั้งหมดก่อนสั่งซื้อ</p>
        </div>
    </div>
</div>

<div class="container">

    <!-- Step 1: URL Input -->
    <div class="card">
        <div class="card-header red">
            <i class="fas fa-link"></i> ขั้นตอนที่ 1 — ดึงข้อมูลสินค้า
        </div>
        <div class="card-body">
            <div class="url-group">
                <input type="url" id="productUrl" placeholder="วาง URL สินค้าจากเว็บญี่ปุ่น เช่น Yahoo Auctions, Mercari, Rakuten...">
                <button class="btn-fetch" id="btnFetch" onclick="fetchProduct()">
                    <span class="spinner"></span>
                    <span class="btn-text"><i class="fas fa-search"></i> ดึงราคา</span>
                </button>
            </div>
            <div class="quick-links">
                <span style="font-size:0.8rem;color:var(--text-light);">รองรับ:</span>
                <a href="#"><i class="fas fa-gavel"></i> Yahoo Auctions</a>
                <a href="#"><i class="fas fa-store"></i> Mercari</a>
                <a href="#"><i class="fas fa-shopping-bag"></i> Rakuten</a>
                <a href="#"><i class="fas fa-box"></i> Amazon JP</a>
                <a href="#"><i class="fas fa-tag"></i> PayPay フリマ</a>
            </div>

            <div class="error-msg" id="errorMsg"></div>

            <div class="product-preview" id="productPreview">
                <img id="previewImg" src="" alt="">
                <div class="product-info">
                    <span class="site-badge" id="previewSite"></span>
                    <h3 id="previewTitle"></h3>
                    <div class="product-prices">
                        <div>
                            <div style="font-size:0.78rem;color:var(--text-light);">ราคาสินค้า</div>
                            <div class="price-tag" id="previewPrice"></div>
                        </div>
                        <div>
                            <div style="font-size:0.78rem;color:var(--text-light);">ค่าส่งในญี่ปุ่น</div>
                            <div class="shipping-tag" id="previewShipping"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Manual Adjustments -->
    <div class="card">
        <div class="card-header blue">
            <i class="fas fa-sliders-h"></i> ขั้นตอนที่ 2 — กรอกรายละเอียดเพิ่มเติม
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>ราคาสินค้า (เยน)</label>
                    <input type="number" id="priceYen" value="0" min="0" onchange="calculate()">
                </div>
                <div class="form-group">
                    <label>ค่าส่งในญี่ปุ่น (เยน)</label>
                    <input type="number" id="shippingJP" value="0" min="0" onchange="calculate()">
                    <span class="unit" id="shippingNote"></span>
                </div>
                <div class="form-group">
                    <label>เรทเงินเยน (บาท/เยน)</label>
                    <input type="number" id="exchangeRate" value="0.235" min="0" step="0.001" onchange="calculate()">
                    <span class="unit">ตัวอย่าง: 1 เยน = 0.235 บาท</span>
                </div>
                <div class="form-group">
                    <label>น้ำหนักโดยประมาณ (กก.)</label>
                    <input type="number" id="weightKg" value="1" min="0.1" step="0.1" onchange="calculate()">
                </div>
                <div class="form-group">
                    <label>ค่านำเข้ามาไทย (บาท/กก.)</label>
                    <input type="number" id="importRate" value="150" min="0" onchange="calculate()">
                    <span class="unit">เริ่มต้น 150 บาท/กก.</span>
                </div>
                <div class="form-group">
                    <label>ค่าส่งพัสดุในไทย (บาท/กล่อง)</label>
                    <input type="number" id="thaiShipping" value="25" min="0" onchange="calculate()">
                    <span class="unit">เริ่มต้น 25 บาท/กล่อง</span>
                </div>
            </div>
            <button class="btn-calc" onclick="calculate()">
                <i class="fas fa-calculator"></i> คำนวณ
            </button>
        </div>
    </div>

    <!-- Step 3: Results -->
    <div class="card" id="resultCard">
        <div class="card-header green">
            <i class="fas fa-receipt"></i> ขั้นตอนที่ 3 — สรุปค่าใช้จ่ายโดยประมาณ
        </div>
        <div class="card-body">
            <!-- Section: Japan Costs -->
            <div style="font-weight:700;color:var(--primary);margin-bottom:8px;">
                <i class="fas fa-yen-sign"></i> ค่าใช้จ่ายฝั่งญี่ปุ่น
            </div>
            <div class="result-row">
                <span class="label">ราคาสินค้า</span>
                <span class="value"><span id="r_priceYen">0</span> เยน</span>
            </div>
            <div class="result-row">
                <span class="label">ค่าส่งในญี่ปุ่น (มาโกดัง SKJ)</span>
                <span class="value"><span id="r_shipJP">0</span> เยน</span>
            </div>
            <div class="result-row">
                <span class="label"><strong>รวมเยน</strong></span>
                <span class="value" style="color:var(--primary);"><span id="r_totalYen">0</span> เยน</span>
            </div>
            <div class="result-row">
                <span class="label">คิดเป็นเงินบาท <span class="sub">(เรท <span id="r_rate">0.235</span>)</span></span>
                <span class="value"><span id="r_totalBaht">0</span> บาท</span>
            </div>

            <div class="divider"></div>

            <!-- Section: Thailand Costs -->
            <div style="font-weight:700;color:var(--accent);margin-bottom:8px;">
                <i class="fas fa-truck"></i> ค่าใช้จ่ายฝั่งไทย
            </div>
            <div class="result-row">
                <span class="label">ค่านำเข้าจากญี่ปุ่น-ไทย <span class="sub">(<span id="r_importRate">150</span> บาท × <span id="r_weight">1</span> กก.)</span></span>
                <span class="value"><span id="r_importCost">150</span> บาท</span>
            </div>
            <div class="result-row">
                <span class="label">ค่าส่งพัสดุในไทย</span>
                <span class="value"><span id="r_thaiShip">25</span> บาท</span>
            </div>

            <div class="divider"></div>

            <!-- Total -->
            <div class="total-box">
                <div>
                    <div class="total-label"><i class="fas fa-check-circle"></i> รวมค่าใช้จ่ายทั้งหมดโดยประมาณ</div>
                    <div class="total-sub">ราคาสินค้า + ค่าส่งทั้งหมด</div>
                </div>
                <div style="text-align:right;">
                    <div class="total-value" id="r_grandTotal">0 บาท</div>
                    <div class="total-sub" id="r_grandTotalYen">0 เยน + ค่าขนส่ง</div>
                </div>
            </div>

            <div class="note">
                <i class="fas fa-info-circle"></i>
                <strong>หมายเหตุ:</strong> เป็นการคำนวณเพื่อประเมินค่าใช้จ่ายเบื้องต้นเท่านั้น
            </div>
        </div>
    </div>

</div>

<script>
    // Fetch product from URL
    function fetchProduct() {
        var url = document.getElementById('productUrl').value.trim();
        if (!url) { showError('กรุณาวาง URL สินค้า'); return; }

        var btn = document.getElementById('btnFetch');
        btn.classList.add('loading');
        btn.disabled = true;
        hideError();
        document.getElementById('productPreview').classList.remove('show');

        fetch('{{ route("api.scrape.product") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ url: url })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.classList.remove('loading');
            btn.disabled = false;

            if (data.success) {
                // Fill preview
                var preview = document.getElementById('productPreview');
                document.getElementById('previewSite').textContent = data.site;
                document.getElementById('previewTitle').textContent = data.title || 'ไม่พบชื่อสินค้า';
                document.getElementById('previewPrice').textContent = numberFormat(data.price) + ' เยน';

                if (data.shipping > 0) {
                    document.getElementById('previewShipping').textContent = numberFormat(data.shipping) + ' เยน';
                } else if (data.shipping_text) {
                    document.getElementById('previewShipping').textContent = data.shipping_text;
                } else {
                    document.getElementById('previewShipping').textContent = 'ไม่ระบุ';
                }

                var img = document.getElementById('previewImg');
                if (data.image) {
                    img.src = data.image;
                    img.style.display = 'block';
                } else {
                    img.style.display = 'none';
                }

                preview.classList.add('show');

                // Fill form
                document.getElementById('priceYen').value = data.price;
                document.getElementById('shippingJP').value = data.shipping || 0;
                if (data.shipping_text) {
                    document.getElementById('shippingNote').textContent = data.shipping_text;
                }

                calculate();
            } else {
                showError(data.error || 'ไม่สามารถดึงข้อมูลราคาจาก URL นี้ได้ คุณสามารถกรอกราคาด้วยตนเองได้');
            }
        })
        .catch(function(err) {
            btn.classList.remove('loading');
            btn.disabled = false;
            showError('เกิดข้อผิดพลาด: ' + err.message);
        });
    }

    // Calculate
    function calculate() {
        var priceYen = parseFloat(document.getElementById('priceYen').value) || 0;
        var shippingJP = parseFloat(document.getElementById('shippingJP').value) || 0;
        var rate = parseFloat(document.getElementById('exchangeRate').value) || 0.235;
        var weight = parseFloat(document.getElementById('weightKg').value) || 1;
        var importRate = parseFloat(document.getElementById('importRate').value) || 150;
        var thaiShip = parseFloat(document.getElementById('thaiShipping').value) || 25;

        var totalYen = priceYen + shippingJP;
        var totalBaht = totalYen * rate;
        var importCost = importRate * weight;
        var grandTotal = totalBaht + importCost + thaiShip;

        // Update results
        document.getElementById('r_priceYen').textContent = numberFormat(priceYen);
        document.getElementById('r_shipJP').textContent = numberFormat(shippingJP);
        document.getElementById('r_totalYen').textContent = numberFormat(totalYen);
        document.getElementById('r_rate').textContent = rate;
        document.getElementById('r_totalBaht').textContent = numberFormat2(totalBaht);
        document.getElementById('r_importRate').textContent = numberFormat(importRate);
        document.getElementById('r_weight').textContent = weight;
        document.getElementById('r_importCost').textContent = numberFormat2(importCost);
        document.getElementById('r_thaiShip').textContent = numberFormat(thaiShip);
        document.getElementById('r_grandTotal').textContent = numberFormat2(grandTotal) + ' บาท';
        document.getElementById('r_grandTotalYen').textContent = numberFormat(totalYen) + ' เยน + ค่าขนส่ง';
    }

    function numberFormat(n) {
        return Math.round(n).toLocaleString('en-US');
    }
    function numberFormat2(n) {
        return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function showError(msg) {
        var el = document.getElementById('errorMsg');
        el.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + msg;
        el.classList.add('show');
    }
    function hideError() {
        document.getElementById('errorMsg').classList.remove('show');
    }

    // Allow Enter key on URL input
    document.getElementById('productUrl').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); fetchProduct(); }
    });

    // Initial calculate
    calculate();
</script>

</body>
</html>

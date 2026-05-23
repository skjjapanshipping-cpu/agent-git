@extends('layouts.app')

@section('template_title')
    เพิ่มสมาชิกใหม่
@endsection

@section('content')
<section class="content container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-9">

            @includeif('partials.errors')

            <div class="card card-default" style="border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.06);">
                <div class="card-header" style="background:linear-gradient(135deg, #1D8AC9 0%, #0c5e8e 100%); color:#fff; padding:18px 22px; border-radius:14px 14px 0 0;">
                    <h4 style="margin:0; font-weight:600;"><i class="fa fa-user-plus"></i> เพิ่มสมาชิกใหม่</h4>
                    <p style="margin:6px 0 0; opacity:.85; font-size:14px;">แอดมินกรอกข้อมูลลูกค้า ระบบจะสุ่มรหัสผ่านและส่งอีเมล welcome อัตโนมัติ</p>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('customers.store') }}" role="form" autocomplete="off">
                        @csrf

                        <!-- รหัสลูกค้า -->
                        <div class="form-group">
                            <label for="customerno"><strong>รหัสลูกค้า</strong> <span class="text-muted small">(แก้ไขได้ ถ้าต้องการกำหนดเอง)</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text" style="font-weight:600;">ANW-</span></div>
                                <input type="text" id="customerno_suffix" class="form-control" placeholder="500"
                                    value="{{ old('customerno', str_replace('ANW-','',$suggestedCustomerno)) }}"
                                    style="font-family:'SF Mono','Menlo',monospace; font-weight:600; color:#1D8AC9;">
                            </div>
                            <input type="hidden" name="customerno" id="customerno_full" value="{{ old('customerno', strtolower($suggestedCustomerno)) }}">
                            <small class="text-muted">ระบบเลือกเลขถัดไปให้แล้ว — เปลี่ยนได้ถ้าต้องการระบุเอง (ห้ามซ้ำ)</small>
                            @error('customerno')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <hr>

                        <!-- ข้อมูลส่วนตัว -->
                        <h5 style="margin-bottom:15px; color:#0c5e8e;"><i class="fa fa-user"></i> ข้อมูลลูกค้า</h5>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="name">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="เช่น สมชาย ใจดี" required autofocus>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label for="email">อีเมล <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="customer@example.com" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">ใช้เข้าสู่ระบบ + รับ welcome email พร้อมรหัสผ่าน</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="mobile">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                                <input type="tel" name="mobile" class="form-control @error('mobile') is-invalid @enderror"
                                    value="{{ old('mobile') }}" placeholder="0812345678" maxlength="10" required>
                                @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label>รหัสผ่าน</label>
                                <input type="text" class="form-control" value="ระบบจะสุ่มรหัสผ่านปลอดภัยให้อัตโนมัติ" readonly
                                    style="background:#f8fafc; font-style:italic; color:#64748b;">
                                <small class="text-muted">ระบบจะแสดงรหัสผ่านในหน้าถัดไป (ครั้งเดียว) + ส่งทางอีเมล</small>
                            </div>
                        </div>

                        <hr>

                        <!-- ที่อยู่ -->
                        <h5 style="margin-bottom:15px; color:#0c5e8e;"><i class="fa fa-map-marker"></i> ที่อยู่ในไทย (สำหรับจัดส่งของถึงไทย) <span class="text-muted small">- ไม่บังคับ</span></h5>

                        <div class="form-group">
                            <label for="addr">ที่อยู่</label>
                            <input type="text" name="addr" class="form-control" value="{{ old('addr') }}" placeholder="บ้านเลขที่ ซอย ถนน">
                        </div>

                        {{-- ✨ Quick Search: พิมพ์ จังหวัด/อำเภอ/ตำบล/รหัสไปรษณีย์ อย่างใดอย่างหนึ่ง → กรอกฟิลด์อื่นให้อัตโนมัติ --}}
                        <div class="form-group address-quick-search position-relative">
                            <label for="address_quick_search" style="font-weight:600; color:#0c5e8e;">
                                <i class="fa fa-search"></i> ค้นหาที่อยู่ด่วน
                                <span class="text-muted small" style="font-weight:400;">— พิมพ์ จังหวัด / อำเภอ / ตำบล / รหัสไปรษณีย์ อย่างใดอย่างหนึ่ง</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-search"></i></span></div>
                                <input type="text" id="address_quick_search" class="form-control" autocomplete="off"
                                       placeholder="เช่น บางรัก, ห้วยขวาง, กรุงเทพ, 10110 ..."
                                       style="font-size:15px;">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="address_quick_clear" title="ล้าง">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="address_quick_results" class="address-quick-results" style="display:none;"></div>
                            <small id="address_quick_status" class="text-muted d-block mt-1" style="min-height:18px;"></small>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="province">จังหวัด</label>
                                <select name="province" id="province" class="form-control">
                                    <option value="">-- เลือกจังหวัด --</option>
                                    @foreach($provinces as $p)
                                        <option value="{{ $p->province }}" {{ old('province')==$p->province?'selected':'' }}>{{ $p->province }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="distrinct">เขต/อำเภอ</label>
                                <select name="distrinct" id="distrinct" class="form-control">
                                    <option value="">-- เลือกอำเภอ --</option>
                                    @foreach($amphoes as $a)
                                        <option value="{{ $a->amphoe }}" {{ old('distrinct')==$a->amphoe?'selected':'' }}>{{ $a->amphoe }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="subdistrinct">แขวง/ตำบล</label>
                                <select name="subdistrinct" id="subdistrinct" class="form-control">
                                    <option value="">-- เลือกตำบล --</option>
                                    @foreach($tambons as $t)
                                        <option value="{{ $t->tambon }}" {{ old('subdistrinct')==$t->tambon?'selected':'' }}>{{ $t->tambon }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="postcode">รหัสไปรษณีย์</label>
                                <input type="text" name="postcode" id="postcode" class="form-control" value="{{ old('postcode') }}" maxlength="5">
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> ยกเลิก</a>
                            <button type="submit" class="btn btn-primary btn-lg" style="background:#1D8AC9; border-color:#1D8AC9; padding:10px 32px;">
                                <i class="fa fa-user-plus"></i> สร้างบัญชี + ส่งอีเมลให้ลูกค้า
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection

@section('extra-script')
<style>
.address-quick-search { background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:14px 16px; margin-bottom:16px; }
.address-quick-search .input-group-text { background:#fff; border-right:0; color:#1D8AC9; }
.address-quick-search #address_quick_search { border-left:0; border-right:0; }
.address-quick-search #address_quick_clear { border:1px solid #ced4da; border-left:0; background:#fff; }
.address-quick-results {
    position:absolute; z-index:1050; left:14px; right:14px; top:100%; margin-top:4px;
    background:#fff; border:1px solid #cbd5e1; border-radius:10px;
    box-shadow:0 8px 24px rgba(0,0,0,.12); max-height:340px; overflow-y:auto;
}
.address-quick-results .aq-item { padding:10px 14px; cursor:pointer; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:10px; font-size:14px; }
.address-quick-results .aq-item:last-child { border-bottom:0; }
.address-quick-results .aq-item:hover,
.address-quick-results .aq-item.active { background:#eff6ff; }
.address-quick-results .aq-item .aq-zip { background:#1D8AC9; color:#fff; font-weight:600; font-family:'SF Mono',Menlo,monospace; padding:3px 8px; border-radius:6px; font-size:12px; min-width:54px; text-align:center; }
.address-quick-results .aq-item .aq-text { flex:1; line-height:1.35; color:#0f172a; }
.address-quick-results .aq-item .aq-text small { color:#64748b; }
.address-quick-results .aq-empty { padding:14px; text-align:center; color:#94a3b8; font-size:13px; }
.address-quick-results .aq-loading { padding:14px; text-align:center; color:#64748b; font-size:13px; }
.address-quick-results mark { background:#fef08a; color:inherit; padding:0 2px; border-radius:2px; }
</style>
<script>
    // sync customerno suffix -> hidden full value
    (function(){
        var suffix = document.getElementById('customerno_suffix');
        var full = document.getElementById('customerno_full');
        function sync() {
            var v = (suffix.value || '').toString().trim();
            full.value = v ? ('anw-' + v.replace(/\D/g, '')) : '';
        }
        suffix.addEventListener('input', sync);
        sync();
    })();

    // re-use thai address logic from existing customer/form
    document.querySelector('#province')?.addEventListener('change', () => typeof showAmphoes === 'function' && showAmphoes());
    document.querySelector('#distrinct')?.addEventListener('change', () => typeof showTambons === 'function' && showTambons());
    document.querySelector('#subdistrinct')?.addEventListener('change', () => typeof showZipcode === 'function' && showZipcode());

    // ===== Quick Address Search =====
    (function() {
        const searchUrl = "{{ url('/api/tambons/search') }}";
        const inp = document.getElementById('address_quick_search');
        const box = document.getElementById('address_quick_results');
        const status = document.getElementById('address_quick_status');
        const clearBtn = document.getElementById('address_quick_clear');
        if (!inp || !box) return;

        let debounceTimer = null;
        let activeIdx = -1;
        let lastResults = [];
        let currentReq = 0;

        function setStatus(msg) { status.textContent = msg || ''; }
        function hide() { box.style.display = 'none'; box.innerHTML = ''; activeIdx = -1; }
        function show() { box.style.display = 'block'; }

        function escapeHtml(s) {
            return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        }
        function highlight(text, q) {
            if (!q) return escapeHtml(text);
            const esc = escapeHtml(text);
            try {
                const re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                return esc.replace(re, '<mark>$1</mark>');
            } catch { return esc; }
        }

        function render(results, q) {
            lastResults = results || [];
            if (!results.length) {
                box.innerHTML = '<div class="aq-empty">😕 ไม่พบที่อยู่ที่ตรงกับคำว่า "' + escapeHtml(q) + '"</div>';
                show();
                return;
            }
            box.innerHTML = results.map((r, i) => `
                <div class="aq-item${i===0?' active':''}" data-idx="${i}">
                    <span class="aq-zip">${escapeHtml(r.zipcode || '-')}</span>
                    <div class="aq-text">
                        <strong>${highlight('ตำบล' + r.tambon, q)}</strong>
                        <small> · อำเภอ${highlight(r.amphoe, q)} · จังหวัด${highlight(r.province, q)}</small>
                    </div>
                </div>
            `).join('');
            activeIdx = 0;
            show();
        }

        function pick(r) {
            if (!r) return;
            const provSel = document.getElementById('province');
            const distSel = document.getElementById('distrinct');
            const subSel  = document.getElementById('subdistrinct');
            const post    = document.getElementById('postcode');

            ensureOption(provSel, r.province);
            provSel.value = r.province;

            showAmphoesPromise(provSel, distSel).then(() => {
                ensureOption(distSel, r.amphoe);
                distSel.value = r.amphoe;
                return showTambonsPromise(provSel, distSel, subSel);
            }).then(() => {
                ensureOption(subSel, r.tambon);
                subSel.value = r.tambon;
                post.value = r.zipcode || '';
                inp.value = `${r.tambon} · ${r.amphoe} · ${r.province} (${r.zipcode || '-'})`;
                hide();
                setStatus('✓ กรอกที่อยู่ให้แล้วเรียบร้อย — ยังแก้ไขเพิ่มเติมจากเมนูด้านล่างได้');
                inp.blur();
            });
        }

        function ensureOption(sel, value) {
            if (!sel || !value) return;
            let exists = Array.from(sel.options).some(o => o.value === value);
            if (!exists) {
                const opt = document.createElement('option');
                opt.value = value; opt.text = value;
                sel.appendChild(opt);
            }
        }

        // Promise wrappers around legacy cascading loaders
        function showAmphoesPromise(provSel, distSel) {
            const url = "{{ url('/api/amphoes') }}?province=" + encodeURIComponent(provSel.value);
            return fetch(url).then(r => r.json()).then(items => {
                distSel.innerHTML = '<option value="">-- เลือกอำเภอ --</option>';
                items.forEach(it => {
                    const o = document.createElement('option'); o.value = it.amphoe; o.text = it.amphoe;
                    distSel.appendChild(o);
                });
            });
        }
        function showTambonsPromise(provSel, distSel, subSel) {
            const url = "{{ url('/api/tambons') }}?province=" + encodeURIComponent(provSel.value)
                      + "&amphoe=" + encodeURIComponent(distSel.value);
            return fetch(url).then(r => r.json()).then(items => {
                subSel.innerHTML = '<option value="">-- เลือกตำบล --</option>';
                items.forEach(it => {
                    const o = document.createElement('option'); o.value = it.tambon; o.text = it.tambon;
                    subSel.appendChild(o);
                });
            });
        }

        function search(q) {
            if (!q || q.trim().length < 1) { hide(); setStatus(''); return; }
            const reqId = ++currentReq;
            box.innerHTML = '<div class="aq-loading"><i class="fa fa-spinner fa-spin"></i> กำลังค้นหา...</div>';
            show();
            fetch(searchUrl + '?q=' + encodeURIComponent(q) + '&limit=30')
                .then(r => r.json())
                .then(data => {
                    if (reqId !== currentReq) return; // outdated
                    render(data, q.trim());
                    setStatus(data.length ? `พบ ${data.length} รายการ — กดเลือกหรือใช้ลูกศร ↑↓ + Enter` : '');
                })
                .catch(err => { console.error(err); setStatus('เกิดข้อผิดพลาด ลองใหม่อีกครั้ง'); hide(); });
        }

        inp.addEventListener('input', e => {
            clearTimeout(debounceTimer);
            const v = e.target.value;
            debounceTimer = setTimeout(() => search(v), 220);
        });
        inp.addEventListener('focus', () => {
            if (lastResults.length && inp.value.trim()) show();
        });
        inp.addEventListener('keydown', e => {
            const items = box.querySelectorAll('.aq-item');
            if (!items.length) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); activeIdx = Math.min(activeIdx+1, items.length-1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); activeIdx = Math.max(activeIdx-1, 0); }
            else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIdx >= 0 && lastResults[activeIdx]) pick(lastResults[activeIdx]);
                return;
            } else if (e.key === 'Escape') { hide(); return; }
            else return;
            items.forEach((el, i) => el.classList.toggle('active', i === activeIdx));
            items[activeIdx]?.scrollIntoView({ block: 'nearest' });
        });
        box.addEventListener('mousedown', e => {
            const item = e.target.closest('.aq-item');
            if (!item) return;
            e.preventDefault();
            const idx = parseInt(item.dataset.idx, 10);
            pick(lastResults[idx]);
        });
        document.addEventListener('click', e => {
            if (!e.target.closest('.address-quick-search')) hide();
        });
        clearBtn.addEventListener('click', () => {
            inp.value = ''; setStatus(''); hide(); lastResults = []; inp.focus();
        });
    })();
</script>
@endsection

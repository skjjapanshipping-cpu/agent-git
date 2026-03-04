<div class="box box-info padding-1">
    <div class="box-body">

        <div class="form-group">
            {{ Form::label('วันที่') }}
            @php
                $orderDateValue = isset($customerorder) && $customerorder->order_date 
                    ? \Carbon\Carbon::parse($customerorder->order_date)->format('Y-m-d\TH:i')
                    : now()->format('Y-m-d\TH:i');
            @endphp
            <input type="datetime-local" name="order_date" value="{{ $orderDateValue }}" class="form-control {{ $errors->has('order_date') ? ' is-invalid' : '' }}" placeholder="">
            {!! $errors->first('order_date', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('รหัสลูกค้า') }}
            {{ Form::text('customerno', $customerorder->customerno??'ANW-', ['id'=>'customerno','class' => 'form-control' . ($errors->has('customerno') ? ' is-invalid' : ''), 'placeholder' => '']) }}
            {!! $errors->first('customerno', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('Boss') }}
            @php
                $bossOptions = ['' => 'เลือก Boss'];
                $bossList = \App\Models\Boss::pluck('name', 'id')->toArray();
                foreach ($bossList as $id => $name) {
                    // สลับจุดวงกลม น้ำเงิน/เหลือง ตาม id คี่/คู่
                    $dot = ($id % 2 === 1) ? '🔵' : '🟡';
                    $bossOptions[$id] = $dot . ' ' . $name;
                }
            @endphp
            {{ Form::select('boss_id', $bossOptions, $customerorder->boss_id ?? '', ['class' => 'form-control' . ($errors->has('boss_id') ? ' is-invalid' : ''), 'required']) }}
            {!! $errors->first('boss_id', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('Item') }}
{{--            {{ Form::text('itemno',!empty($customerorder->itemno)?$customerorder->itemno:\App\Models\Customerorder::newItemno($customerorder->customerno), ['id' => 'itemno','class' => 'form-control' . ($errors->has('itemno') ? ' is-invalid' : ''), 'placeholder' => '']) }}--}}
            {{ Form::text('itemno',$customerorder->itemno, ['id' => 'itemno','class' => 'form-control' . ($errors->has('itemno') ? ' is-invalid' : ''), 'placeholder' => '']) }}
            {!! $errors->first('itemno', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('Item2') }}
            {{ Form::text('itemno2', $customerorder->itemno2 ?? '', ['id' => 'itemno2','class' => 'form-control' . ($errors->has('itemno2') ? ' is-invalid' : ''), 'placeholder' => 'กรอกรหัสสินค้าเพิ่มเติม', 'maxlength' => '255']) }}
            {!! $errors->first('itemno2', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('Note Admin') }}
            {{ Form::textarea('note_admin', $customerorder->note_admin ?? '', ['class' => 'form-control' . ($errors->has('note_admin') ? ' is-invalid' : ''), 'placeholder' => 'Note Admin']) }}
            {!! $errors->first('note_admin', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group d-none">
            {{ Form::label('ประเภท') }}
            {{ Form::select('category', \App\Models\Category::pluck('name','id'), $customerorder->category??18, ['class' => 'form-control' . ($errors->has('category') ? ' is-invalid' : ''), 'placeholder' => 'กรุณาเลือกประเภท']) }}
            {!! $errors->first('category', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('รูปภาพ') }}
            <input type="file" id="image_input" name="image_link" class="form-control-file {{ $errors->has('image_link') ? ' is-invalid' : '' }}">
            <input type="hidden" id="fetched_image_url" name="fetched_image_url" value="">
            <img id="image_preview" src="{{ isset($customerorder) ? asset('uploads/' . $customerorder->image_link) : '#' }}" alt="Preview" style="max-width: 200px; display: {{ isset($customerorder) && $customerorder->image_link ? 'block' : 'none' }}">
{{--            @if(isset($customerorder) && $customerorder->image_link)--}}
{{--                <button type="button" id="delete_image_button" class="btn btn-danger mt-2">ลบรูปภาพ</button>--}}
{{--            @endif--}}
            {!! $errors->first('image_link', '<div class="invalid-feedback">กรุณาอัพโหลดรูปภาพ</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('ลิงค์สินค้า') }}
            <div class="input-group">
                {{ Form::text('link', $customerorder->link, ['class' => 'form-control' . ($errors->has('link') ? ' is-invalid' : ''), 'placeholder' => 'วาง URL สินค้า (Mercari, Yahoo, Paypayfleamarket...)', 'id' => 'product_link']) }}
                <div class="input-group-append">
                    <button type="button" class="btn btn-info" id="btnFetchProduct" onclick="fetchProductInfo()">
                        <i class="fa fa-download"></i> ดึงข้อมูล
                    </button>
                </div>
            </div>
            {!! $errors->first('link', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
            <div id="fetchStatus" style="margin-top:8px;font-size:13px;"></div>
            <button type="button" id="btnClearFetch" style="display:none;margin-top:6px;padding:5px 14px;background:#e53e3e;color:#fff;border:none;border-radius:6px;font-size:13px;font-weight:bold;cursor:pointer;" onclick="clearFetchedData()"><i class="fa fa-times"></i> ล้างข้อมูลที่ดึงมา</button>
            <div id="fetchPreview" style="display:none;margin-top:10px;padding:12px;background:#f8f9fa;border-radius:8px;border:1px solid #e2e8f0;">
                <div style="display:flex;gap:12px;align-items:flex-start;">
                    <img id="fetchedImage" src="" style="width:120px;height:120px;object-fit:cover;border-radius:6px;border:1px solid #ddd;flex-shrink:0;" />
                    <div style="flex:1;min-width:0;">
                        <div id="fetchedTitle" style="font-weight:600;font-size:14px;margin-bottom:6px;word-break:break-word;"></div>
                        <div id="fetchedSite" style="font-size:12px;color:#718096;margin-bottom:4px;"></div>
                        <div id="fetchedDetails" style="font-size:13px;margin-top:6px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('จำนวน') }}
            {{ Form::number('quantity', $customerorder->quantity??1, ['class' => 'form-control' . ($errors->has('quantity') ? ' is-invalid' : ''), 'placeholder' => '' ,'id' => 'quantity', 'step' => '1']) }}
            {!! $errors->first('quantity', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('ค่าสินค้า (เยน)') }}
            {{ Form::number('product_price_yen', old('product_price_yen', $customerorder->product_price_yen ?? ''), ['class' => 'form-control', 'step' => '0.01', 'placeholder' => 'ราคาสินค้า (เยน)', 'id' => 'product_price_yen']) }}
        </div>
        <div class="form-group">
            {{ Form::label('ค่าส่งในญี่ปุ่น (เยน)') }}
            {{ Form::number('shipping_jp_yen', old('shipping_jp_yen', $customerorder->shipping_jp_yen ?? ''), ['class' => 'form-control', 'step' => '0.01', 'placeholder' => 'ค่าส่งในญี่ปุ่น (ถ้ามี)', 'id' => 'shipping_jp_yen']) }}
        </div>
        <div class="form-group">
            {{ Form::label('รวมเงินเยนทั้งหมด') }}
            {{ Form::number('product_cost_yen', $customerorder->product_cost_yen, ['class' => 'form-control' . ($errors->has('product_cost_yen') ? ' is-invalid' : ''), 'step' => '0.01', 'placeholder' => 'รวม ค่าสินค้า + ค่าส่ง','id' => 'product_cost_yen', 'style' => 'font-weight:bold;border:2px solid #3182ce;background:#edf2f7;']) }}
            {!! $errors->first('product_cost_yen', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('เรท') }}
            {{ Form::number('rateprice', $customerorder->rateprice??\App\Models\Dailyrate::curRatePerBath(), ['class' => 'form-control' . ($errors->has('rateprice') ? ' is-invalid' : ''), 'step' => '0.001', 'placeholder' => '','id' => 'rateprice']) }}
            {!! $errors->first('rateprice', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('เงินบาท') }}
            {{ Form::number('product_cost_baht', $customerorder->product_cost_baht??$customerorder->product_cost_yen*\App\Models\Dailyrate::curRatePerBath()*$customerorder->quantity, ['class' => 'form-control' . ($errors->has('product_cost_baht') ? ' is-invalid' : ''), 'step' => '0.01', 'placeholder' => '' ,'id' => 'product_cost_baht']) }}
            {!! $errors->first('product_cost_baht', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>

        <div class="form-group">
            {{ Form::label('C.Status', null, ['style' => 'color: #22c7dd;']) }}
            {{ Form::select('status', \App\Models\PayStatus::pluck('name','id'), $customerorder->status??1, ['class' => 'form-control' . ($errors->has('status') ? ' is-invalid' : ''), 'placeholder' => 'เลือกสถานะชำระเงิน']) }}
            {!! $errors->first('status', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('Buyer Status', null, ['class' => 'text-danger']) }}
            {{ Form::select('supplier_status_id', \App\Models\SupplierStatus::pluck('name','id'), $customerorder->supplier_status_id ?? 1, ['class' => 'form-control' . ($errors->has('supplier_status_id') ? ' is-invalid' : ''), 'required' => true]) }}
            {!! $errors->first('supplier_status_id', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('เลขพัสดุ') }}
            {{ Form::text('tracking_number', $customerorder->tracking_number, ['class' => 'form-control' . ($errors->has('tracking_number') ? ' is-invalid' : ''), 'placeholder' => '']) }}
            {!! $errors->first('tracking_number', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('รอบปิดตู้') }}
            {{ Form::date('cutoff_date', $customerorder->cutoff_date , ['class' => 'form-control' . ($errors->has('cutoff_date') ? ' is-invalid' : ''), 'placeholder' => '', 'id' => 'cutoff_date']) }}
            {!! $errors->first('cutoff_date', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group ml-5">
            {{ Form::checkbox('cutoff_date_check','', false, ['class' => 'form-check-input' . ($errors->has('cutoff_date') ? ' is-invalid' : ''), 'id' => 'cutoff_date_check']) }}
            {{ Form::label('รอบปิดตู้(รอดำเนินการ)') }}



        </div>
        <div class="form-group">
            {{ Form::label('สถานะขนส่ง') }}
            {{ Form::select('shipping_status', \App\Models\ShippingStatus::pluck('name','id'),$customerorder->shipping_status??1, ['class' => 'form-control' . ($errors->has('shipping_status') ? ' is-invalid' : ''), 'placeholder' => 'เลือกสถานะขนส่ง']) }}
            {!! $errors->first('shipping_status', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>

        <div class="form-group">
            {{ Form::label('หมายเหตุ') }}
            {{ Form::textarea('note', $customerorder->note, ['class' => 'form-control' . ($errors->has('note') ? ' is-invalid' : ''), 'placeholder' => 'Note']) }}
            {!! $errors->first('note', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>


    </div>
    <div class="box-footer mt20">
        <input type="hidden" id="lastItemno" name="lastItemno" >
        <button type="submit" class="btn btn-primary" id="submitBtn">{{ __('Submit') }}</button>
        <button type="button" class="btn btn-danger" onclick="window.history.back();">Cancel</button>

    </div>
</div>
<script src="{{asset('dashboard/assets/js/core/jquery.min.js')}}"></script>
<script>

    $(document).ready(function() {
        // ดึง item number ใหม่เฉพาะหน้าสร้าง (ไม่ใช่หน้า Edit)
        var isEditPage = window.location.pathname.includes('/edit');
        var itemnoTimer = null;
        var lastFetchedCustomerno = ''; // เก็บค่าล่าสุดที่ดึงแล้ว ไม่ดึงซ้ำ
        $('#customerno').on('input blur', function() {
            if (isEditPage) return; // หน้า Edit ไม่ต้องรันเลขใหม่
            var customerno = $(this).val().trim();
            if (!customerno || customerno.length < 4) return; // รอให้พิมพ์อย่างน้อย 4 ตัว
            if (customerno === lastFetchedCustomerno) return; // ค่าเดิม ไม่ต้องดึงซ้ำ
            // debounce: รอ 400ms หลังพิมพ์เสร็จ เพื่อไม่ยิง AJAX ทุกตัวอักษร
            clearTimeout(itemnoTimer);
            itemnoTimer = setTimeout(function() {
                lastFetchedCustomerno = customerno;
                $.ajax({
                    type: 'GET',
                    url: window.location.hostname === 'localhost' ? '/get-new-itemno' : '/skjtrack/get-new-itemno',
                    data: { customerno: customerno },
                    success: function(data) {
                        $('#itemno').val(data);
                        $('#lastItemno').val(data-1);
                        // รีเซ็ตตัวนับเมื่อเปลี่ยนรหัสลูกค้า
                        findAvailableCount = 0;
                        $('#findAvailableItemno').text('หาหมายเลขที่ว่าง');
                    }
                });
            }, 400);
        });
        
        // เพิ่มปุ่มสำหรับหาหมายเลขที่ว่างอยู่
        $('#itemno').after('<button type="button" id="findAvailableItemno" class="btn btn-sm btn-info ml-2">หาหมายเลขที่ว่าง</button>');
        
        // ตัวแปรเก็บจำนวนครั้งที่กดปุ่ม
        var findAvailableCount = 0;
        
        $('#findAvailableItemno').on('click', function() {
            var customerno = $('#customerno').val();
            if (!customerno) {
                alert('กรุณากรอกรหัสลูกค้าก่อน');
                return;
            }
            
            // เพิ่มจำนวนครั้งที่กด
            findAvailableCount++;
            
            $.ajax({
                type: 'GET',
                url: window.location.hostname === 'localhost' ? '/get-available-itemno' : '/skjtrack/get-available-itemno',
                data: { 
                    customerno: customerno,
                    skip_count: findAvailableCount - 1 // ส่งจำนวนครั้งที่ข้ามไป
                },
                dataType: 'json',
                success: function(response) {
                    if (response.availableItemno) {
                        $('#itemno').val(response.availableItemno);
                        // อัพเดท lastItemno เป็นหมายเลขที่ใช้อยู่สูงสุด
                        var maxUsedItemno = Math.max(...response.usedItemnos, 0);
                        $('#lastItemno').val(maxUsedItemno);
                        
                        // เปลี่ยนข้อความปุ่มเพื่อแสดงสถานะ
                        if (findAvailableCount > 1) {
                            $('#findAvailableItemno').text('หาหมายเลขถัดไป (' + findAvailableCount + ')');
                        }
                        
                        alert('พบหมายเลขที่ว่างลำดับที่ ' + findAvailableCount + ': ' + response.availableItemno);
                    } else {
                        // ตรวจสอบว่ามีเลขว่างเพิ่มเติมหรือไม่
                        if (response.foundCount > findAvailableCount - 1) {
                            alert('ไม่พบหมายเลขที่ว่างลำดับที่ ' + findAvailableCount + ' แต่ยังมีเลขว่างอื่นๆ กรุณากดปุ่มอีกครั้ง');
                        } else {
                            alert('ไม่พบหมายเลขที่ว่างเพิ่มเติม กรุณาใช้หมายเลขใหม่');
                            // รีเซ็ตตัวนับเมื่อไม่พบหมายเลขเพิ่มเติม
                            findAvailableCount = 0;
                            $('#findAvailableItemno').text('หาหมายเลขที่ว่าง');
                        }
                    }
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการค้นหาหมายเลขที่ว่าง');
                }
            });
        });
    });
    document.addEventListener("DOMContentLoaded", function() {
        //
        // const productCostYenInput = document.getElementById('product_cost_yen');
        // const quantityInput = document.getElementById('quantity');
        // const ratePriceInput = document.getElementById('rateprice');
        //
        // if (productCostYenInput) {
        //     productCostYenInput.addEventListener('input', calculateProductCost);
        // }
        //
        // if (quantityInput) {
        //     quantityInput.addEventListener('input', calculateProductCost);
        // }
        //
        // if (ratePriceInput) {
        //     ratePriceInput.addEventListener('input', calculateProductCost);
        // }




        const imageInput = document.getElementById('image_input');
        const imagePreview = document.getElementById('image_preview');
        const deleteImageButton = document.getElementById('delete_image_button');

        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    imagePreview.src = event.target.result;
                    imagePreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                imagePreview.src = '#';
                imagePreview.style.display = 'none';
            }
        });

        if (deleteImageButton) {
            deleteImageButton.addEventListener('click', function() {
                // ตรวจสอบว่ามีรูปภาพอยู่หรือไม่ก่อนส่ง request
                if (imagePreview.src !== '#' && imagePreview.style.display !== 'none') {
                    // สร้าง XMLHttpRequest object
                    const xhr = new XMLHttpRequest();
                    // กำหนด method และ URL ของการ request
                    xhr.open('POST', '/delete-image', true);
                    // กำหนดค่า header สำหรับ request
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    // กำหนดคำสั่งเมื่อ request เสร็จสิ้น
                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            // ลบรูปภาพออกจากหน้า
                            imagePreview.src = '#';
                            imagePreview.style.display = 'none';
                            imageInput.value = ''; // Clear input file
                        } else {
                            alert('เกิดข้อผิดพลาดในการลบรูปภาพ');
                        }
                    };
                    // ส่ง request
                    xhr.send(JSON.stringify({ imageSrc: imagePreview.src }));
                }
            });
        }



        document.getElementById('product_price_yen').addEventListener('input', function() { updateTotalYen(); });
        document.getElementById('shipping_jp_yen').addEventListener('input', function() { updateTotalYen(); });

        // เพิ่ม event listener เมื่อมีการกดคีย์ในช่องรวมเงินเยน
        document.getElementById('product_cost_yen').addEventListener('input', function() {
            calculateProductCost();
        });

// เพิ่ม event listener เมื่อมีการกดคีย์ในช่องจำนวนสินค้า
        // document.getElementById('quantity').addEventListener('input', function() {
        //     console.log('quantity')
        //     calculateProductCost();
        // });

        document.getElementById('rateprice').addEventListener('input', function() {

                calculateProductCost();
        });



        if(document.getElementById('cutoff_date').value===''){
            document.getElementById('cutoff_date').readOnly = true;
            document.getElementById('cutoff_date_check').checked=true;
        }else{
            document.getElementById('cutoff_date_check').checked=false;
        }
        document.getElementById('cutoff_date_check').addEventListener('change', function() {
            if (this.checked) {
                // Checkbox ถูกเลือก
                console.log('Checkbox ถูกเลือก');
                document.getElementById('cutoff_date').value='';
                document.getElementById('cutoff_date').readOnly = true;
                // ทำสิ่งที่ต้องการเมื่อ checkbox ถูกเลือก
            } else {
                // Checkbox ไม่ถูกเลือก
                console.log('Checkbox ไม่ถูกเลือก');
                document.getElementById('cutoff_date').readOnly = false;
                // ทำสิ่งที่ต้องการเมื่อ checkbox ไม่ถูกเลือก
            }
        });

        // ป้องกันการ submit ซ้ำ
        let isSubmitting = false;
        
        // ป้องกันการกด Enter ซ้ำ
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !isSubmitting) {
                // ตรวจสอบว่าไม่ใช่ textarea
                if (e.target.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    checkDuplicateItemno(function(isOk) {
                        if (isOk) submitForm();
                    });
                }
            }
        });
        
        // ป้องกันการคลิกปุ่ม submit ซ้ำ
        document.getElementById('submitBtn').addEventListener('click', function(e) {
            e.preventDefault();
            
            // ตรวจสอบ itemno ก่อน submit
            var itemnoValue = $('#itemno').val();
            var cleanItemno = parseInt(itemnoValue);
            
            if (isNaN(cleanItemno) || cleanItemno <= 0) {
                return;
            }
            
            if (!isSubmitting) {
                checkDuplicateItemno(function(isOk) {
                    if (isOk) submitForm();
                });
            }
        });
        
        function submitForm() {
            if (isSubmitting) return;
            
            isSubmitting = true;
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.textContent;
            
            // เปลี่ยนข้อความและ disable ปุ่ม
            submitBtn.textContent = 'กำลังส่งข้อมูล...';
            submitBtn.disabled = true;
            submitBtn.classList.add('disabled');
            
            // ส่งฟอร์ม
            const form = document.querySelector('form');
            if (form) {
                form.submit();
            }
            
            // รีเซ็ตสถานะหากเกิดข้อผิดพลาด (หลังจาก 5 วินาที)
            setTimeout(function() {
                isSubmitting = false;
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                submitBtn.classList.remove('disabled');
            }, 5000);
        }

    });
    // Global: Auto-sum ค่าสินค้า + ค่าส่ง = รวมเงินเยนทั้งหมด
    function updateTotalYen() {
        var priceStr = document.getElementById('product_price_yen').value.trim();
        var shipStr = document.getElementById('shipping_jp_yen').value.trim();
        if (priceStr || shipStr) {
            var price = parseFloat(priceStr) || 0;
            var shipping = parseFloat(shipStr) || 0;
            document.getElementById('product_cost_yen').value = (price + shipping).toFixed(2);
        }
        calculateProductCost();
    }

    function calculateProductCost() {
        // ดึงค่าจำนวนเงินเยน
        var product_cost_yen = parseFloat(document.getElementById('product_cost_yen').value)||0;

        // ดึงค่าอัตราแลกเปลี่ยน
        var rateprice = parseFloat(document.getElementById('rateprice').value)||0;

        // ดึงค่าจำนวนสินค้า
        var quantity = document.getElementById('quantity').value;

        // คำนวณค่าสินค้าในหน่วยเงินบาท
        // var product_cost_baht = product_cost_yen * rateprice * quantity;
        var product_cost_baht = product_cost_yen * rateprice ;

        // ใส่ค่าคำนวณลงในช่องเงินบาท
        document.getElementById('product_cost_baht').value = product_cost_baht.toFixed(2);
    }

    // ฟังก์ชันตรวจสอบ itemno ซ้ำ (async callback)
    function checkDuplicateItemno(callback) {
        var inputItemnoRaw = $('#itemno').val();
        var inputItemno = parseInt(inputItemnoRaw);
        var customerno = $('#customerno').val();
        
        // ตรวจสอบว่าเป็นการแก้ไขหรือสร้างใหม่
        var isEdit = window.location.pathname.includes('/edit') || window.location.pathname.includes('/update');
        var currentItemno = '{{ $customerorder->itemno ?? "" }}';
        
        if (!customerno) {
            alert('กรุณากรอกรหัสลูกค้าก่อน');
            return callback(false);
        }
        
        if (isNaN(inputItemno) || inputItemno <= 0) {
            return callback(false);
        }
        
        // ถ้าเป็นการแก้ไขและรหัสไม่เปลี่ยน ให้ผ่าน
        if (isEdit && inputItemnoRaw === currentItemno) {
            return callback(true);
        }
        
        // เช็คกับฐานข้อมูลจริง (async)
        $.ajax({
            type: 'GET',
            url: window.location.hostname === 'localhost' ? '/check-itemno-exists' : '/skjtrack/check-itemno-exists',
            data: { 
                customerno: customerno,
                itemno: inputItemnoRaw,
                exclude_id: isEdit ? '{{ $customerorder->id ?? "" }}' : ''
            },
            dataType: 'json',
            timeout: 5000,
            success: function(exists) {
                if (exists) {
                    alert('รหัสสินค้าซ้ำ กรุณาใช้รหัสอื่น');
                    $('#itemno').val('');
                    $('#itemno').focus();
                    callback(false);
                } else {
                    callback(true);
                }
            },
            error: function(xhr, status, error) {
                console.log('Error checking itemno:', error);
                // AJAX ล้มเหลว — ให้ผ่านไปเลย server จะ validate อีกที
                callback(true);
            }
        });
    }

    // === Clear fetched data ===
    function clearFetchedData() {
        document.getElementById('product_price_yen').value = '';
        document.getElementById('shipping_jp_yen').value = '';
        document.getElementById('product_cost_yen').value = '';
        document.getElementById('fetched_image_url').value = '';
        document.getElementById('fetchPreview').style.display = 'none';
        document.getElementById('fetchStatus').innerHTML = '';
        document.getElementById('btnClearFetch').style.display = 'none';
        document.getElementById('product_cost_baht').value = '';
    }

    // === Fetch Product Info from URL ===
    function fetchProductInfo() {
        var url = document.getElementById('product_link').value.trim();
        if (!url) {
            alert('กรุณาวาง URL ลิงค์สินค้าก่อน');
            return;
        }

        var btn = document.getElementById('btnFetchProduct');
        var statusEl = document.getElementById('fetchStatus');
        var previewEl = document.getElementById('fetchPreview');

        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> กำลังดึงข้อมูล...';
        statusEl.innerHTML = '<span style="color:#3182ce;">กำลังดึงข้อมูลจาก URL...</span>';
        previewEl.style.display = 'none';

        $.ajax({
            type: 'POST',
            url: window.location.hostname === 'localhost' ? '/api/scrape-product' : '/skjtrack/api/scrape-product',
            data: { url: url },
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            dataType: 'json',
            timeout: 30000,
            success: function(data) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-download"></i> ดึงข้อมูล';

                try {
                    if (data.success) {
                        if (data.price > 0) {
                            document.getElementById('product_price_yen').value = data.price;
                        }
                        // เซ็ตค่าส่งเฉพาะเมื่อ scraper ดึงได้จริง (> 0) ไม่เขียนทับค่าเดิม
                        if (data.shipping && data.shipping > 0) {
                            document.getElementById('shipping_jp_yen').value = data.shipping;
                        }

                        updateTotalYen();

                        if (data.image) {
                            document.getElementById('fetchedImage').src = data.image;
                            document.getElementById('fetched_image_url').value = data.image;
                        }
                        document.getElementById('fetchedTitle').textContent = data.title || '-';
                        document.getElementById('fetchedSite').textContent = data.site || 'Unknown';

                        var details = '';
                        details += '<div style="display:flex;flex-wrap:wrap;gap:8px 20px;">';
                        details += '<div><b>ราคา:</b> <span style="color:#e53e3e;font-weight:bold;font-size:15px;">' + (data.price > 0 ? data.price.toLocaleString() + ' เยน' : '-') + '</span></div>';
                        if (data.shipping_text) {
                            details += '<div><b>ค่าส่ง:</b> ' + data.shipping_text + '</div>';
                        } else if (data.shipping > 0) {
                            details += '<div><b>ค่าส่ง:</b> ' + data.shipping.toLocaleString() + ' เยน</div>';
                        } else {
                            details += '<div><b>ค่าส่ง:</b> <span style="color:#999;">ไม่พบข้อมูล</span></div>';
                        }
                        details += '</div>';
                        document.getElementById('fetchedDetails').innerHTML = details;
                        previewEl.style.display = 'block';

                        // Warning for shipping + link to URL
                        var shipWarn = '';
                        if (!data.shipping || data.shipping == 0) {
                            var productUrl = document.getElementById('product_link').value.trim();
                            shipWarn = '<br><span style="color:#e53e3e;font-weight:bold;">⚠️ กรุณาตรวจสอบค่าส่งในญี่ปุ่นจาก URL ด้วยตัวเอง (ระบบดึงค่าส่งไม่ได้)</span>'
                                + '<br><a href="' + productUrl + '" target="_blank" style="display:inline-block;margin-top:6px;padding:6px 14px;background:#3182ce;color:#fff;border-radius:6px;text-decoration:none;font-size:13px;font-weight:bold;"><i class="fa fa-external-link"></i> เปิดดู URL สินค้า</a>';
                        }
                        document.getElementById('btnClearFetch').style.display = 'inline-block';
                        statusEl.innerHTML = '<span style="color:#38a169;">✅ ดึงข้อมูลสำเร็จ — ' + (data.price > 0 ? data.price.toLocaleString() + ' เยน' : '') + '</span>' + shipWarn;
                    } else {
                        document.getElementById('btnClearFetch').style.display = 'inline-block';
                        var errUrl = document.getElementById('product_link').value.trim();
                        statusEl.innerHTML = '<span style="color:#e53e3e;">❌ ไม่สามารถดึงราคาได้ — ' + (data.error || 'กรุณากรอกราคาด้วยตัวเอง') + '</span>'
                            + '<br><a href="' + errUrl + '" target="_blank" style="display:inline-block;margin-top:6px;padding:6px 14px;background:#3182ce;color:#fff;border-radius:6px;text-decoration:none;font-size:13px;font-weight:bold;"><i class="fa fa-external-link"></i> เปิดดู URL สินค้า</a>';
                    }
                } catch(e) {
                    statusEl.innerHTML = '<span style="color:#e53e3e;">JS Error: ' + e.message + '</span>';
                    console.error('fetchProductInfo error:', e);
                }
            },
            error: function(xhr, status, error) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-download"></i> ดึงข้อมูล';
                var msg = 'HTTP ' + xhr.status;
                if (xhr.status === 419) msg = 'CSRF Token หมดอายุ — กรุณา refresh หน้า';
                else if (xhr.status === 404) msg = 'API route ไม่พบ';
                else if (xhr.status === 500) msg = 'Server error';
                else if (status === 'timeout') msg = 'หมดเวลา — เว็บไม่ตอบกลับ';
                if (xhr.responseJSON && xhr.responseJSON.message) msg += ': ' + xhr.responseJSON.message;
                document.getElementById('btnClearFetch').style.display = 'inline-block';
                var errUrl2 = document.getElementById('product_link').value.trim();
                statusEl.innerHTML = '<span style="color:#e53e3e;">❌ ' + msg + '</span>'
                    + '<br><a href="' + errUrl2 + '" target="_blank" style="display:inline-block;margin-top:6px;padding:6px 14px;background:#3182ce;color:#fff;border-radius:6px;text-decoration:none;font-size:13px;font-weight:bold;"><i class="fa fa-external-link"></i> เปิดดู URL สินค้า</a>';
            }
        });
    }

    // ตรวจสอบเฉพาะตอนออกจากช่องกรอก (blur) รองรับทั้ง Mac และ Windows
    $('#itemno').on('blur', function() {
        checkDuplicateItemno();
    });
    
    // รีเซ็ตตัวนับเมื่อมีการเปลี่ยนแปลงในช่อง itemno
    $('#itemno').on('input change', function() {
        findAvailableCount = 0;
        $('#findAvailableItemno').text('หาหมายเลขที่ว่าง');
    });

    // ลบ event listener เดิมที่อาจทำให้เกิดการ submit ซ้ำ
    // $('#customerorder-form').on('submit', function(e) {
    //     if (!checkDuplicateItemno()) {
    //         e.preventDefault();
    //     }
    // });
</script>






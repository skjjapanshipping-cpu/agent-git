<div class="box box-info padding-1">
    <div class="box-body">

        <div id="cur_addr">
            @php
                $customer = null;
                if (Route::currentRouteName() === 'customershippings.edit' && isset($customershipping->customerno)) {
                    $customer = \App\User::where('customerno', $customershipping->customerno)->first();
                }
            @endphp
            <input type="hidden" id="fullname" value="{{$customer->name ?? $authUser->name ?? ''}}" />
            <input type="hidden" id="address" value="{{$customer->addr ?? $authUser->addr ?? ''}}"/>
            <input type="hidden" id="addr_province" value="{{$customer->province ?? $authUser->province ?? ''}}"/>
            <input type="hidden" id="addr_district" value="{{$customer->distrinct ?? $authUser->distrinct ?? ''}}"/>
            <input type="hidden" id="addr_subdistrict" value="{{$customer->subdistrinct ?? $authUser->subdistrinct ?? ''}}"/>
            <input type="hidden" id="addr_postcode" value="{{$customer->postcode ?? $authUser->postcode ?? ''}}"/>
            <input type="hidden" id="addr_mobile" value="{{$customer->mobile ?? $authUser->mobile ?? ''}}"/>
        </div>
        <div class="form-group">
            {{ Form::label('ship_date','วันที่') }}
            {{ Form::date('ship_date', $customershipping->ship_date??now(), ['class' => 'form-control' . ($errors->has('ship_date') ? ' is-invalid' : ''), 'placeholder' => '']) }}
{{--            {{ Form::text('ship_date', $customershipping->ship_date, ['class' => 'form-control' . ($errors->has('ship_date') ? ' is-invalid' : ''), 'placeholder' => '']) }}--}}
            {!! $errors->first('ship_date', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('customerno','รหัสลูกค้า') }}
            {{ Form::text('customerno', $customershipping->customerno??'ANW-', ['id'=>'customerno','class' => 'form-control' . ($errors->has('customerno') ? ' is-invalid' : ''), 'placeholder' => '']) }}
            {!! $errors->first('customerno', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('Item') }}
            {{ Form::text('itemno',$customershipping->itemno??'', ['id' => 'itemno','class' => 'form-control' . ($errors->has('itemno') ? ' is-invalid' : ''), 'placeholder' => '']) }}
{{--            {{ Form::text('itemno',!empty($customershipping->itemno)?$customershipping->itemno:\App\Models\Customerorder::newItemno($customershipping->customerno), ['id' => 'itemno','class' => 'form-control' . ($errors->has('itemno') ? ' is-invalid' : ''), 'placeholder' => '']) }}--}}

            {!! $errors->first('itemno', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('note_admin','note admin') }}
            {{ Form::textarea('note_admin', $customershipping->note_admin, ['class' => 'form-control' . ($errors->has('note_admin') ? ' is-invalid' : ''), 'placeholder' => '']) }}
            {!! $errors->first('note_admin', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('track_no','เลขพัสดุ') }}
            {{ Form::text('track_no', $customershipping->track_no, ['class' => 'form-control' . ($errors->has('track_no') ? ' is-invalid' : ''), 'placeholder' => '']) }}
            {!! $errors->first('track_no', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('cod','COD') }}
{{--            {{ Form::text('cod', $customershipping->cod, ['class' => 'form-control' . ($errors->has('cod') ? ' is-invalid' : ''), 'placeholder' => '']) }}--}}
            {{ Form::number('cod', $customershipping->cod, ['class' => 'form-control' . ($errors->has('cod') ? ' is-invalid' : ''), 'step' => '0.01', 'placeholder' => '','id' => 'cod']) }}
            {!! $errors->first('cod', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('cod_rate','COD Rate') }}
            {{ Form::number('cod_rate', $customershipping->cod_rate ?? \App\Models\Dailyrate::getCodRate(), ['class' => 'form-control' . ($errors->has('cod_rate') ? ' is-invalid' : ''), 'step' => '0.001', 'placeholder' => '', 'readonly' => true]) }}
            <small class="form-text text-muted">COD Rate ณ วันที่สร้าง (ไม่สามารถแก้ไขได้)</small>
            {!! $errors->first('cod_rate', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('weight','น้ำหนัก') }}
            {{ Form::number('weight', $customershipping->weight, ['class' => 'form-control' . ($errors->has('weight') ? ' is-invalid' : ''), 'step' => '0.01', 'placeholder' => '']) }}
            {!! $errors->first('weight', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('width','กว้าง') }}
            {{ Form::number('width', $customershipping->width, ['class' => 'form-control' . ($errors->has('width') ? ' is-invalid' : ''), 'step' => '0.01', 'placeholder' => '']) }}
            {!! $errors->first('width', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('length','ยาว') }}
            {{ Form::number('length', $customershipping->length, ['class' => 'form-control' . ($errors->has('length') ? ' is-invalid' : ''), 'step' => '0.01', 'placeholder' => '']) }}
            {!! $errors->first('length', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('height','สูง') }}
            {{ Form::number('height', $customershipping->height, ['class' => 'form-control' . ($errors->has('height') ? ' is-invalid' : ''), 'step' => '0.01', 'placeholder' => '']) }}
            {!! $errors->first('height', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            @php
               $list_unit_price=['180','170','160','150','140','130','120','115','110','100','90','0']
             @endphp
            {{ Form::label('ราคาต่อหน่วย') }}
            <select  id="importcost_select" class="form-control">
                <option value=""  {{ empty($customershipping->unit_price) ? 'selected' : '' }}>เลือกราคาต่อหน่วย</option>

                @foreach($list_unit_price as $unitpriceItem)
                    <option value="{{$unitpriceItem}}" {{ $customershipping->unit_price==$unitpriceItem ? 'selected' : '' }}>{{$unitpriceItem}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            {{ Form::label('unit_price','หน่วยละ') }}
            {{ Form::number('unit_price', $customershipping->unit_price, ['class' => 'form-control' . ($errors->has('unit_price') ? ' is-invalid' : ''), 'step' => '0.01', 'placeholder' => '','id'=>'unit_price','readonly'=>true]) }}
            {!! $errors->first('unit_price', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group ">
            {{ Form::label('iswholeprice', 'ราคาเหมา', ['class' => 'mr-4 mb-0']) }}
            {{ Form::checkbox('iswholeprice', 1, $customershipping->iswholeprice == 1, ['class' => 'form-check-input mb-0', 'id' => 'iswholeprice']) }}
            <label for="iswholeprice" class="ml-2 mb-0">เลือกหากเป็นราคาเหมา</label>
            {!! $errors->first('iswholeprice', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group" id="wholeprice_calc_row" style="display: {{ $customershipping->iswholeprice == 1 ? 'block' : 'none' }}; background: #f8f9fa; padding: 12px 15px; border-radius: 6px; border: 1px solid #dee2e6;">
            <label style="font-weight: 600; margin-bottom: 8px; display: block;">คำนวณค่านำเข้า (กว้าง × ยาว × สูง × 0.01)</label>
            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 6px;">
                <input type="number" id="wp_width" step="0.01" min="0" placeholder="กว้าง cm" style="width: 100px; padding: 4px 8px; border: 1px solid #ced4da; border-radius: 4px; text-align: center;" value="{{ $customershipping->width ?? '' }}">
                <span style="font-weight: bold;">×</span>
                <input type="number" id="wp_length" step="0.01" min="0" placeholder="ยาว cm" style="width: 100px; padding: 4px 8px; border: 1px solid #ced4da; border-radius: 4px; text-align: center;" value="{{ $customershipping->length ?? '' }}">
                <span style="font-weight: bold;">×</span>
                <input type="number" id="wp_height" step="0.01" min="0" placeholder="สูง cm" style="width: 100px; padding: 4px 8px; border: 1px solid #ced4da; border-radius: 4px; text-align: center;" value="{{ $customershipping->height ?? '' }}">
                <span style="font-weight: bold;">× 0.01 =</span>
                <span id="wp_result" style="font-weight: bold; color: #e53e3e; font-size: 16px;">0.00</span>
                <span style="font-weight: bold;">บาท</span>
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('import_cost','ค่านำเข้า') }}
            {{ Form::number('import_cost', $customershipping->import_cost, ['class' => 'form-control' . ($errors->has('import_cost') ? ' is-invalid' : ''), 'step' => '0.01', 'placeholder' => '', 'id' => 'import_cost']) }}
            {!! $errors->first('import_cost', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('box_image','รูปหน้ากล่อง') }}
{{--            {{ Form::text('box_image', $customershipping->box_image, ['class' => 'form-control' . ($errors->has('box_image') ? ' is-invalid' : ''), 'placeholder' => '']) }}--}}
            <div class="d-flex align-items-start">
                <div class="mr-3">
                    <input type="file" id="box_image_input" name="box_image" class="form-control-file {{ $errors->has('box_image') ? ' is-invalid' : '' }}">
                    <img id="box_image_preview" src="{{ isset($customershipping) ? asset( $customershipping->box_image) : '#' }}" alt="Preview" style="max-width: 200px; display: {{ isset($customershipping) && $customershipping->box_image ? 'block' : 'none' }}">
                </div>
                @if(isset($customershipping) && $customershipping->box_image)
                <button type="button" class="btn btn-danger btn-sm" id="delete_box_image">
                    <i class="fa fa-trash"></i> ลบรูป
                </button>
                @endif
            </div>
            {!! $errors->first('box_image', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('product_image','รูปสินค้า') }}
            <div class="d-flex align-items-start">
                <div class="mr-3">
                    <input type="file" id="product_image_input" name="product_image" class="form-control-file {{ $errors->has('product_image') ? ' is-invalid' : '' }}">
                    <img id="product_image_preview" src="{{ isset($customershipping) ? asset($customershipping->product_image) : '#' }}" 
                        alt="Preview" style="max-width: 200px; display: {{ isset($customershipping) && $customershipping->product_image ? 'block' : 'none' }}">
                </div>
                @if(isset($customershipping) && $customershipping->product_image)
                <button type="button" class="btn btn-danger btn-sm" id="delete_product_image">
                    <i class="fa fa-trash"></i> ลบรูป
                </button>
                @endif
            </div>
            {!! $errors->first('product_image', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('box_no','เลขหน้ากล่อง') }}
            {{ Form::text('box_no', $customershipping->box_no, ['class' => 'form-control' . ($errors->has('box_no') ? ' is-invalid' : ''), 'placeholder' => '']) }}
            {!! $errors->first('box_no', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('warehouse','โกดัง') }}
            {{ Form::text('warehouse', $customershipping->warehouse, ['class' => 'form-control' . ($errors->has('warehouse') ? ' is-invalid' : ''), 'placeholder' => '']) }}
            {!! $errors->first('warehouse', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('shipping_method','ประเภทขนส่ง') }}
            <select name="shipping_method" id="shipping_method" class="form-control{{ $errors->has('shipping_method') ? ' is-invalid' : '' }}">
                <option value="1" {{ ($customershipping->shipping_method ?? 1) == 1 ? 'selected' : '' }}>🚢 ทางเรือ</option>
                <option value="2" {{ ($customershipping->shipping_method ?? 1) == 2 ? 'selected' : '' }}>✈️ ทางเครื่องบิน</option>
            </select>
            {!! $errors->first('shipping_method', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('etd', ($customershipping->shipping_method ?? 1) == 2 ? 'วันที่(รอบเที่ยวบิน)' : 'วันที่(ปิดตู้)', ['id' => 'etd_label']) }}
            {{ Form::date('etd', $customershipping->etd??now(), ['class' => 'form-control' . ($errors->has('etd') ? ' is-invalid' : ''), 'placeholder' => '']) }}
            {{--            {{ Form::text('etd', $customershipping->etd, ['class' => 'form-control' . ($errors->has('etd') ? ' is-invalid' : ''), 'placeholder' => '']) }}--}}
            {!! $errors->first('etd', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('สถานะ') }}
            {{ Form::select('status', \App\Models\ShippingStatus::where('id','>',1)->where('id','!=',5)->pluck('name','id'), $customershipping->status??2, ['class' => 'form-control' . ($errors->has('status') ? ' is-invalid' : ''), 'placeholder' => 'เลือกสถานะ']) }}
            {!! $errors->first('status', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('สถานะชำระเงิน') }}
            {{ Form::select('pay_status', \App\Models\PayStatus::where('id','<',3)->orWhere('id',5)->pluck('name','id'), $customershipping->pay_status??1, ['class' => 'form-control' . ($errors->has('pay_status') ? ' is-invalid' : ''), 'placeholder' => 'เลือกสถานะชำระเงิน','required'=>true]) }}
{{--            {{ Form::select('pay_status', \App\Models\PayStatus::where('id','<',3)->pluck('name','id'),$customershipping->pay_status??1, ['class' => 'form-control' . ($errors->has('pay_status') ? ' is-invalid' : ''), 'placeholder' => 'เลือกสถานะชำระเงิน' ,'required'=>true]) }}--}}
            {!! $errors->first('pay_status', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('delivery_type_id','การจัดส่ง') }}
            {{ Form::select('delivery_type_id', \App\Models\DeliveryType::orderBy('sortno')->pluck('name','id'), $customershipping->delivery_type_id??1, ['id'=>'delivery_type_id','class' => 'form-control' . ($errors->has('delivery_type_id') ? ' is-invalid' : ''), 'placeholder' => 'เลือกวิธีจัดสง','required'=>true]) }}
            {!! $errors->first('delivery_type_id', '<div class="invalid-feedback">กรุณากรอกข้อมูล</div>') !!}
        </div>
        <div class="form-group position-relative">
            {{ Form::label('delivery_fullname', 'ชื่อ-นามสกุล') }}
            {{ Form::text('delivery_fullname', Route::currentRouteName() === 'customershippings.create' ? '' : $customershipping->delivery_fullname, ['class' => 'form-control' . ($errors->has('delivery_fullname') ? ' is-invalid' : ''), 'placeholder' => 'ชื่อ-นามสกุล']) }}
            <div id="delivery_fullname-results" class="search-results"></div>
            {!! $errors->first('delivery_fullname', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group position-relative">
            {{ Form::label('delivery_mobile', 'เบอร์โทร') }}
            {{ Form::text('delivery_mobile', Route::currentRouteName() === 'customershippings.create' ? '' : $customershipping->delivery_mobile, ['class' => 'form-control' . ($errors->has('delivery_mobile') ? ' is-invalid' : ''), 'placeholder' => 'เบอร์โทร']) }}
            <div id="delivery_mobile-results" class="search-results"></div>
            {!! $errors->first('delivery_mobile', '<div class="invalid-feedback">:message</div>') !!}
        </div>
        <div class="form-group">
            {{ Form::label('delivery_address','ที่อยู่') }}
            {{ Form::text('delivery_address', Route::currentRouteName() === 'customershippings.create' ? '' : $customershipping->delivery_address, ['class' => 'form-control' . ($errors->has('delivery_address') ? ' is-invalid' : ''), 'placeholder' => '']) }}
            {!! $errors->first('delivery_address', '<div class="invalid-feedback">:message</div>') !!}
        </div>

        <div class="form-group position-relative">
            <label for="delivery_subdistrict" class="form-label">แขวง/ตำบล</label>
            <input type="text" 
                   class="form-control @error('delivery_subdistrict') is-invalid @enderror" 
                   id="delivery_subdistrict" 
                   name="delivery_subdistrict"
                   value="{{ Route::currentRouteName() === 'customershippings.create' ? '' : ($customershipping->delivery_subdistrict ?? '') }}"
                   placeholder="พิมพ์เพื่อค้นหาตำบล"
                   required>
            <div id="delivery_subdistrict-results" class="search-results"></div>
            @error('delivery_subdistrict')
                <label class="error invalid-feedback">กรุณากรอก แขวง/ตำบล</label>
            @enderror
        </div>

        <div class="form-group position-relative">
            <label for="delivery_district" class="form-label">เขต/อำเภอ</label>
            <input type="text" 
                   class="form-control @error('delivery_district') is-invalid @enderror" 
                   id="delivery_district" 
                   name="delivery_district"
                   value="{{ Route::currentRouteName() === 'customershippings.create' ? '' : ($customershipping->delivery_district ?? '') }}"
                   placeholder="พิมพ์เพื่อค้นหาอำเภอ"
                   required>
            <div id="delivery_district-results" class="search-results"></div>
            @error('delivery_district')
                <label class="error invalid-feedback">กรุณากรอก เขต/อำเภอ</label>
            @enderror
        </div>

        <div class="form-group position-relative">
            <label for="delivery_province" class="form-label">จังหวัด</label>
            <input type="text" 
                   class="form-control @error('delivery_province') is-invalid @enderror" 
                   id="delivery_province" 
                   name="delivery_province"
                   value="{{ Route::currentRouteName() === 'customershippings.create' ? '' : ($customershipping->delivery_province ?? '') }}"
                   placeholder="พิมพ์เพื่อค้นหาจังหวัด"
                   required>
            <div id="delivery_province-results" class="search-results"></div>
            @error('delivery_province')
                <label class="error invalid-feedback">กรุณากรอก จังหวัด</label>
            @enderror
        </div>

        <div class="form-group position-relative">
            <label for="delivery_postcode" class="form-label">รหัสไปรษณีย์</label>
            <input type="text" 
                   class="form-control @error('delivery_postcode') is-invalid @enderror" 
                   id="delivery_postcode" 
                   name="delivery_postcode"
                   value="{{ Route::currentRouteName() === 'customershippings.create' ? '' : ($customershipping->delivery_postcode ?? '') }}"
                   placeholder="พิมพ์เพื่อค้นหารหัสไปรษณีย์"
                   required>
            <div id="delivery_postcode-results" class="search-results"></div>
            @error('delivery_postcode')
                <label class="error invalid-feedback">กรุณากรอก รหัสไปรษณีย์</label>
            @enderror
        </div>

        <div class="form-group">
            {{ Form::label('note') }}
            {{ Form::textarea('note', $customershipping->note, ['class' => 'form-control' . ($errors->has('note') ? ' is-invalid' : ''), 'placeholder' => '']) }}
            {!! $errors->first('note', '<div class="invalid-feedback">:message</div>') !!}
        </div>

    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
        <button type="button" class="btn btn-danger" onclick="window.history.back();">Cancel</button>
    </div>
</div>
@section('extra-script')
    <script src="{{ asset('js/thai-address-search.js') }}"></script>
    <script>

        //EVENTS
        document.querySelector('#delivery_province').addEventListener('change', (event) => {
            showAmphoes("#delivery_province","#delivery_district");
        });
        document.querySelector('#delivery_district').addEventListener('change', (event) => {
            showTambons("#delivery_province","#delivery_district","#delivery_subdistrict");
        });
        document.querySelector('#delivery_subdistrict').addEventListener('change', (event) => {
            showZipcode("#delivery_province","#delivery_district","#delivery_subdistrict","#delivery_postcode");
        });
        $(function () {
            //itemno
            // $('#customerno').on('blur', function() {
            //     var customerno = $(this).val();
            //     $.ajax({
            //         type: 'GET',
            //         url: '/get-new-itemno',
            //         data: { customerno: customerno },
            //         success: function(data) {
            //             $('#itemno').val(data);
            //         }
            //     });
            // });


            // check auto select importcost_select
            var importcost_values = $('#importcost_select option').map(function() {
                return $(this).val();
            }).get();
            var unit_price =parseInt($('#unit_price').val()).toString();

            if ('{{Route::currentRouteName()}}' !== 'customershippings.create') {
                if (!importcost_values.includes(unit_price)) {
                    // ทำอะไรก็ตามที่ต้องการ เมื่อค่าไม่ตรงกับ importcost_values
                    // console.log(importcost_values,unit_price)
                    $('#unit_price').attr('readonly',false);
                    $('#importcost_select').val(0);
                }
            }else{
                $('#unit_price').val( $('#importcost_select').val());
            }


            $('#importcost_select').on('change',function (e) {
                let thisval = $(this).val();

                if(thisval!==''&&thisval>0){
                    $('#unit_price').val(thisval);
                }else{ // ราคาเหมา
                    $('#unit_price').attr('readonly',false);
                }
            });

            // === ราคาเหมา: แสดง/ซ่อนแถวคำนวณ + คำนวณอัตโนมัติ ===
            function calcWholeprice(fillImport) {
                var w = parseFloat($('#wp_width').val()) || 0;
                var l = parseFloat($('#wp_length').val()) || 0;
                var h = parseFloat($('#wp_height').val()) || 0;
                var result = w * l * h * 0.01;
                $('#wp_result').text(result.toFixed(2));
                // ใส่ค่านำเข้าเฉพาะเมื่อ user พิมพ์ขนาดเอง (fillImport=true)
                if (fillImport && w > 0 && l > 0 && h > 0) {
                    $('#import_cost').val(result.toFixed(2));
                }
                // ใส่ขนาดลงในช่อง Note ให้ลูกค้าเห็น
                var noteEl = $('textarea[name="note"]');
                var noteVal = noteEl.val();
                // ลบขนาดเก่าออกก่อน (format: 999*999*999cm)
                noteVal = noteVal.replace(/\d+(\.\d+)?\*\d+(\.\d+)?\*\d+(\.\d+)?cm/g, '').trim();
                if (w > 0 && l > 0 && h > 0) {
                    var dimText = w + '*' + l + '*' + h + 'cm';
                    noteEl.val(noteVal ? noteVal + '\n' + dimText : dimText);
                } else {
                    noteEl.val(noteVal);
                }
            }

            // sync ค่าขนาดจาก wp_ กลับไปช่องหลักก่อน submit
            $('form').on('submit', function() {
                if ($('#iswholeprice').is(':checked')) {
                    var w = parseFloat($('#wp_width').val()) || 0;
                    var l = parseFloat($('#wp_length').val()) || 0;
                    var h = parseFloat($('#wp_height').val()) || 0;
                    if (w > 0) $('input[name="width"]').val(w);
                    if (l > 0) $('input[name="length"]').val(l);
                    if (h > 0) $('input[name="height"]').val(h);
                }
            });

            $('#iswholeprice').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#wholeprice_calc_row').slideDown(200);
                    calcWholeprice(false); // แสดงผลคำนวณ แต่ไม่เขียนทับค่านำเข้า
                } else {
                    $('#wholeprice_calc_row').slideUp(200);
                }
            });

            // พิมพ์ขนาด → คำนวณ + ใส่ค่านำเข้าให้ + sync ช่องหลัก
            $('#wp_width, #wp_length, #wp_height').on('input', function() {
                calcWholeprice(true);
                // sync ค่ากลับช่องหลัก (ถ้าลบออก ช่องหลักก็ลบด้วย)
                $('input[name="width"]').val($('#wp_width').val());
                $('input[name="length"]').val($('#wp_length').val());
                $('input[name="height"]').val($('#wp_height').val());
            });

            // โหลดครั้งแรก: แสดงผลคำนวณ แต่ไม่เขียนทับค่านำเข้าที่บันทึกไว้
            if ($('#iswholeprice').is(':checked')) {
                calcWholeprice(false);
            }

            // === Shipping Method: เปลี่ยน label ETD และ unit price ตามประเภทขนส่ง ===
            $('#shipping_method').on('change', function() {
                var method = $(this).val();
                var label = method == 2 ? 'วันที่(รอบเที่ยวบิน)' : 'วันที่(ปิดตู้)';
                $('label[for="etd"]').text(label);
                
                // เปลี่ยนรายการราคาต่อหน่วย
                var seaPrices = ['180','170','160','150','140','130','120','115','110','100','90','0'];
                var airPrices = ['400','380','360','339','320','300','0'];
                var prices = method == 2 ? airPrices : seaPrices;
                var defaultPrice = method == 2 ? '339' : '150';
                
                var $select = $('#importcost_select');
                var currentVal = $select.val();
                $select.empty();
                $select.append('<option value="">เลือกราคาต่อหน่วย</option>');
                prices.forEach(function(p) {
                    $select.append('<option value="' + p + '"' + (p == currentVal ? ' selected' : '') + '>' + p + '</option>');
                });
                
                // ถ้าเป็นหน้าสร้างใหม่ ให้ตั้ง default
                if ('{{Route::currentRouteName()}}' === 'customershippings.create') {
                    $select.val(defaultPrice);
                    $('#unit_price').val(defaultPrice);
                }
            });

            deliveryOnLoad($('select[name="delivery_type_id"]').val());
            $('select[name="delivery_type_id"]').on('change',function (e) {

                $('input[name="delivery_fullname"]').val('');
                $('input[name="delivery_address"]').val('');
                $('input[name="delivery_province"]').val('');
                $('input[name="delivery_district"]').val('');
                $('input[name="delivery_subdistrict"]').val('');
                $('input[name="delivery_mobile"]').val('');
                $('input[name="delivery_postcode"]').val('');
                deliveryLoadChange($(this).val());
            });
        });

        function deliveryOnLoad(_delivery_type_id) {
            if(_delivery_type_id==1){
                $('[name^="delivery"]').not('[name="delivery_type_id"]').addClass('d-none');
                $('[name^="delivery"]').not('[name="delivery_type_id"]').removeAttr('required');
                $('[for^="delivery"]').addClass('d-none');
            } else if(_delivery_type_id==2){
                // เมื่อโหลดหน้าแรกและเป็นที่อยู่ปัจจุบัน ให้ disable ฟิลด์
                $('input[name="delivery_fullname"]').prop('readonly', true);
                $('input[name="delivery_address"]').prop('readonly', true);
                $('input[name="delivery_province"]').prop('readonly', true);
                $('input[name="delivery_district"]').prop('readonly', true);
                $('input[name="delivery_subdistrict"]').prop('readonly', true);
                $('input[name="delivery_mobile"]').prop('readonly', true);
                $('input[name="delivery_postcode"]').prop('readonly', true);
            }
        }

        function deliveryLoadChange(_delivery_type_id) {





            if(_delivery_type_id>1){
                $('[name^="delivery"]').not('[name="delivery_type_id"]').removeClass('d-none');
                $('[name^="delivery"]').not('[name="delivery_type_id"]').attr('required',true);
                $('[for^="delivery"]').removeClass('d-none');
            }
            switch (_delivery_type_id)
            {
                case '1':
                    // case '2':
                    $('[name^="delivery"]').not('[name="delivery_type_id"]').addClass('d-none');
                    $('[name^="delivery"]').not('[name="delivery_type_id"]').removeAttr('required');
                    $('[for^="delivery"]').addClass('d-none');
                    break;
                case '2':
                    $('input[name="delivery_fullname"]').val($('#cur_addr>input#fullname').val());
                    $('input[name="delivery_address"]').val($('#cur_addr>input#address').val());
                    $('input[name="delivery_province"]').val($('#cur_addr>input#addr_province').val());
                    $('input[name="delivery_district"]').val($('#cur_addr>input#addr_district').val());
                    $('input[name="delivery_subdistrict"]').val($('#cur_addr>input#addr_subdistrict').val());
                    $('input[name="delivery_mobile"]').val($('#cur_addr>input#addr_mobile').val());
                    $('input[name="delivery_postcode"]').val($('#cur_addr>input#addr_postcode').val());
                    
                    // Disable ฟิลด์ที่อยู่ทั้งหมดเมื่อเลือกที่อยู่ปัจจุบัน
                    $('input[name="delivery_fullname"]').prop('readonly', true);
                    $('input[name="delivery_address"]').prop('readonly', true);
                    $('input[name="delivery_province"]').prop('readonly', true);
                    $('input[name="delivery_district"]').prop('readonly', true);
                    $('input[name="delivery_subdistrict"]').prop('readonly', true);
                    $('input[name="delivery_mobile"]').prop('readonly', true);
                    $('input[name="delivery_postcode"]').prop('readonly', true);
                    break;
                default:
                    // สำหรับ delivery_type_id อื่นๆ ให้เปิดใช้งานฟิลด์
                    $('input[name="delivery_fullname"]').prop('readonly', false);
                    $('input[name="delivery_address"]').prop('readonly', false);
                    $('input[name="delivery_province"]').prop('readonly', false);
                    $('input[name="delivery_district"]').prop('readonly', false);
                    $('input[name="delivery_subdistrict"]').prop('readonly', false);
                    $('input[name="delivery_mobile"]').prop('readonly', false);
                    $('input[name="delivery_postcode"]').prop('readonly', false);
                    break;


            }
        }

        $(function() {
            // เพิ่มการจัดการปุ่มลบรูปภาพ
            $('#delete_product_image').on('click', function() {
                if(confirm('คุณต้องการลบรูปภาพนี้ใช่หรือไม่?')) {
                    $('#product_image_preview').hide();
                    $('#product_image_input').val('');
                    $(this).hide();
                    
                    // เพิ่ม input hidden เพื่อบอก controller ว่าต้องการลบรูป
                    if(!$('input[name="delete_product_image"]').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'delete_product_image',
                            value: '1'
                        }).appendTo('form');
                    }
                }
            });

            // เพิ่มการจัดการปุ่มลบรูปหน้ากล่อง
            $('#delete_box_image').on('click', function() {
                if(confirm('คุณต้องการลบรูปภาพนี้ใช่หรือไม่?')) {
                    $('#box_image_preview').hide();
                    $('#box_image_input').val('');
                    $(this).hide();
                    
                    // เพิ่ม input hidden เพื่อบอก controller ว่าต้องการลบรูป
                    if(!$('input[name="delete_box_image"]').length) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'delete_box_image',
                            value: '1'
                        }).appendTo('form');
                    }
                }
            });

            // แสดงตัวอย่างรูปภาพเมื่อเลือกไฟล์ใหม่
            $('#product_image_input').on('change', function() {
                const file = this.files[0];
                if(file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#product_image_preview').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(file);
                }
            });

            // แสดงตัวอย่างรูปหน้ากล่องเมื่อเลือกไฟล์ใหม่
            $('#box_image_input').on('change', function() {
                const file = this.files[0];
                if(file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#box_image_preview').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(file);
                }
            });
        });

        $(document).ready(function() {
            // เริ่มต้นใช้งาน Thai Address Search
            initThaiAddressSearch({
                formId: '#shipping-form',
                provinceField: '#delivery_province',
                amphoeField: '#delivery_district',
                tambonField: '#delivery_subdistrict',
                zipcodeField: '#delivery_postcode',
                onAddressSelect: function(address) {
                    console.log('เลือกที่อยู่:', address);
                    var currentAddress = $('#delivery_address').val();
                    var currentFullname = $('#delivery_fullname').val();
                    var currentMobile = $('#delivery_mobile').val();
                
            
                // คืนค่าที่อยู่เดิมหลังจาก update ข้อมูลอื่น
                if(currentAddress) {
                    setTimeout(function() {
                        console.log('test123');
                        $('#delivery_address').val(currentAddress);
                        $('#delivery_fullname').val(currentFullname);
                        $('#delivery_mobile').val(currentMobile);
                    }, 100);
                }
                }
            });

            
            // ระบบค้นหาลูกค้า
            initCustomerShippingSearch({
                fullnameField: '#delivery_fullname',
                mobileField: '#delivery_mobile',
                customernoField: '#customerno',
                apiPrefix: '/skjtrack',
                onSelect: function(customer) {
                    $('#delivery_address').val(customer.address);
                    $('#delivery_province').val(customer.province);
                    $('#delivery_district').val(customer.amphoe);
                    $('#delivery_subdistrict').val(customer.tambon);
                    $('#delivery_postcode').val(customer.zipcode);
                    
                    // ถ้าเป็นที่อยู่ปัจจุบัน ให้ disable ฟิลด์หลังจาก bind เสร็จ
                    var deliveryTypeId = $('#delivery_type_id').val();
                    if (deliveryTypeId == 2) {
                        setTimeout(function() {
                            $('input[name="delivery_fullname"]').prop('readonly', true);
                            $('input[name="delivery_address"]').prop('readonly', true);
                            $('input[name="delivery_province"]').prop('readonly', true);
                            $('input[name="delivery_district"]').prop('readonly', true);
                            $('input[name="delivery_subdistrict"]').prop('readonly', true);
                            $('input[name="delivery_mobile"]').prop('readonly', true);
                            $('input[name="delivery_postcode"]').prop('readonly', true);
                        }, 100);
                    }
                }
            });
        });
    </script>
@endsection

@section('extra-css')
<style>
.position-relative {
    position: relative;
}
.search-results {
    position: absolute;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    z-index: 1000;
    display: none;
    top: 100%;
}
.search-item {
    padding: 8px 12px;
    cursor: pointer;
}
.search-item:hover {
    background-color: #f8f9fa;
}

/* สไตล์สำหรับฟิลด์ readonly */
input[name^="delivery"][readonly] {
    background-color: #f8f9fa !important;
    cursor: not-allowed;
    opacity: 0.8;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // เมื่อมีการเปลี่ยนแปลงรหัสลูกค้า
    document.getElementById('customerno').addEventListener('blur', function() {
        var customerno = this.value;
        if (customerno) {
            // ส่ง AJAX เพื่อดึงข้อมูล delivery_type_id ของลูกค้า
            var url = window.location.hostname === 'localhost' ? 
                '/get-customer-delivery-type' : 
                '/skjtrack/get-customer-delivery-type';
            
            fetch(url + '?customerno=' + encodeURIComponent(customerno))
                .then(response => response.json())
                .then(data => {
                    if (data.delivery_type_id) {
                        // ตั้งค่าวิธีการจัดส่ง
                        document.getElementById('delivery_type_id').value = data.delivery_type_id;
                        
                        // ตั้งค่าข้อมูลที่อยู่
                        if (data.name) {
                            document.querySelector('input[name="delivery_fullname"]').value = data.name;
                            // อัพเดท cur_addr ด้วย
                            document.getElementById('fullname').value = data.name;
                        }
                        if (data.addr) {
                            document.querySelector('input[name="delivery_address"]').value = data.addr;
                            // อัพเดท cur_addr ด้วย
                            document.getElementById('address').value = data.addr;
                        }
                        if (data.province) {
                            document.querySelector('input[name="delivery_province"]').value = data.province;
                            // อัพเดท cur_addr ด้วย
                            document.getElementById('addr_province').value = data.province;
                        }
                        if (data.distrinct) {
                            document.querySelector('input[name="delivery_district"]').value = data.distrinct;
                            // อัพเดท cur_addr ด้วย
                            document.getElementById('addr_district').value = data.distrinct;
                        }
                        if (data.subdistrinct) {
                            document.querySelector('input[name="delivery_subdistrict"]').value = data.subdistrinct;
                            // อัพเดท cur_addr ด้วย
                            document.getElementById('addr_subdistrict').value = data.subdistrinct;
                        }
                        if (data.postcode) {
                            document.querySelector('input[name="delivery_postcode"]').value = data.postcode;
                            // อัพเดท cur_addr ด้วย
                            document.getElementById('addr_postcode').value = data.postcode;
                        }
                        if (data.mobile) {
                            document.querySelector('input[name="delivery_mobile"]').value = data.mobile;
                            // อัพเดท cur_addr ด้วย
                            document.getElementById('addr_mobile').value = data.mobile;
                        }
                        
                        // เรียกฟังก์ชัน deliveryLoadChange เพื่ออัพเดท UI
                        deliveryLoadChange(data.delivery_type_id);
                        
                        // ถ้าเป็นที่อยู่ปัจจุบัน (delivery_type_id = 2) ให้ disable ฟิลด์หลังจาก bind เสร็จ
                        if (data.delivery_type_id == 2) {
                            setTimeout(function() {
                                $('input[name="delivery_fullname"]').prop('readonly', true);
                                $('input[name="delivery_address"]').prop('readonly', true);
                                $('input[name="delivery_province"]').prop('readonly', true);
                                $('input[name="delivery_district"]').prop('readonly', true);
                                $('input[name="delivery_subdistrict"]').prop('readonly', true);
                                $('input[name="delivery_mobile"]').prop('readonly', true);
                                $('input[name="delivery_postcode"]').prop('readonly', true);
                            }, 100);
                        }
                    }
                })
                .catch(error => {
                    console.log('ไม่พบข้อมูลลูกค้า');
                });
        }
    });
});
</script>
@endsection

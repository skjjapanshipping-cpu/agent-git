@extends('layouts.app')

@section('title')
    SKJ JAPAN
@endsection
@section('extra-script')
    <script>$(function () {
            var dataTable=$('#dt-mant-table-1').DataTable({
                "columnDefs": [
                    { "orderable": false, "targets": 0 } // 0 คือลำดับของคอลัมน์ที่คุณต้องการปิดการใช้งานการเรียงลำดับ
                ],
                "lengthMenu": [10,20,30,50,100,200, 300, 400, 500,600,10000], // ตัวเลือกที่สามารถเลือกได้
                "pageLength": 10000,
            });
            
            // ฟังก์ชันเช็ค itemno ซ้ำ
            function checkDuplicateItemnos() {
                var itemnoCustomernoCounts = {};
                var duplicateRows = [];
                var duplicateItems = [];
                
                // วนลูปผ่านทุกแถวในตาราง
                $('#dt-mant-table-1 tbody tr').each(function(index) {
                    var itemno = $(this).find('td:last').text().trim(); // itemno อยู่ในคอลัมน์สุดท้าย
                    var customerno = $(this).find('td:nth-child(4)').text().trim(); // customerno อยู่ในคอลัมน์ที่ 4
                    
                    if (itemno && customerno) {
                        var key = itemno + '|' + customerno; // สร้าง key รวม itemno และ customerno
                        
                        if (!itemnoCustomernoCounts[key]) {
                            itemnoCustomernoCounts[key] = [];
                        }
                        itemnoCustomernoCounts[key].push(index);
                    }
                });
                
                // หา itemno + customerno ที่ซ้ำในข้อมูลที่ import
                Object.keys(itemnoCustomernoCounts).forEach(function(key) {
                    if (itemnoCustomernoCounts[key].length > 1) {
                        duplicateRows = duplicateRows.concat(itemnoCustomernoCounts[key]);
                        var parts = key.split('|');
                        duplicateItems.push(parts[0] + ' (' + parts[1] + ')');
                    }
                });
                
                // เปลี่ยนสีแถวที่ซ้ำในข้อมูลที่ import
                $('#dt-mant-table-1 tbody tr').each(function(index) {
                    if (duplicateRows.includes(index)) {
                        $(this).css('background-color', '#f44336'); // สีแดงเข้ม
                    } else {
                        $(this).css('background-color', ''); // รีเซ็ตสี
                    }
                });
                
                // เก็บข้อมูล duplicateItems ไว้ในตัวแปร global
                window.duplicateItems = duplicateItems;
                
                // เช็คกับ customershipping ที่มีอยู่แล้ว
                checkWithExistingCustomershippings();
            }
            
            // ฟังก์ชันแสดง modal สำหรับ itemno ที่ซ้ำในไฟล์ Excel
            window.showDuplicateInFileModal = function(duplicateItems) {
                var modalHtml = `
                    <div id="customAlertModal3" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
                        <div style="background: white; padding: 20px; border-radius: 8px; max-width: 600px; width: 90%; max-height: 80vh; display: flex; flex-direction: column;">
                            <h4 style="margin-top: 0; color: #d32f2f;">แจ้งเตือน - รหัสซ้ำในไฟล์</h4>
                            <p>พบรหัส item ที่ซ้ำกันในไฟล์ Excel:</p>
                            <textarea readonly style="width: 100%; min-height: 150px; max-height: 400px; border: 1px solid #ccc; padding: 8px; font-family: monospace; font-size: 12px; resize: vertical; flex: 1;">${duplicateItems.join('\n')}</textarea>
                            <div style="margin-top: 15px; text-align: right; flex-shrink: 0;">
                                <button onclick="document.getElementById('customAlertModal3').remove();" style="background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">ปิด</button>
                            </div>
                        </div>
                    </div>
                `;
                $('body').append(modalHtml);
            };
            
            // ฟังก์ชันเช็คกับ customershipping ที่มีอยู่แล้ว
            function checkWithExistingCustomershippings() {
                var itemnos = [];
                var customernos = [];
                
                // รวบรวม itemno และ customerno ทั้งหมด
                $('#dt-mant-table-1 tbody tr').each(function(index) {
                    var itemno = $(this).find('td:last').text().trim();
                    var customerno = $(this).find('td:nth-child(4)').text().trim(); // customerno อยู่ในคอลัมน์ที่ 4
                    
                    console.log('Row ' + index + ': itemno=' + itemno + ', customerno=' + customerno);
                    
                    if (itemno) {
                        itemnos.push(itemno);
                        customernos.push(customerno);
                    }
                });
                
                console.log('Sending itemnos:', itemnos);
                console.log('Sending customernos:', customernos);
                
                if (itemnos.length > 0) {
                    // ส่ง AJAX เพื่อเช็คกับ customershipping
                    $.ajax({
                        url: window.location.hostname === 'localhost' ? '/check-existing-itemnos' : '/skjtrack/check-existing-itemnos',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            itemnos: itemnos,
                            customernos: customernos
                        },
                        success: function(response) {
                            console.log('Response from server:', response);
                            
                            if (response.existingItems && response.existingItems.length > 0) {
                                console.log('Found existing items:', response.existingItems);
                                
                                // สร้าง Set เพื่อเช็คได้เร็วขึ้น (ใช้ key: itemno|customerno)
                                var existingSet = new Set();
                                response.existingItems.forEach(function(item) {
                                    existingSet.add(item.itemno + '|' + item.customerno);
                                });
                                
                                // เปลี่ยนสีแถวที่มี itemno + customerno ซ้ำกับ customershipping
                                var existingList = [];
                                $('#dt-mant-table-1 tbody tr').each(function(index) {
                                    var itemno = $(this).find('td:last').text().trim();
                                    var customerno = $(this).find('td:nth-child(4)').text().trim();
                                    
                                    console.log('Checking row:', index, 'itemno:', itemno, 'customerno:', customerno);
                                    
                                    var key = itemno + '|' + customerno;
                                    if (existingSet.has(key)) {
                                        $(this).css('background-color', '#f44336'); // สีแดงเข้มมาก
                                        existingList.push(itemno + ' (' + customerno + ')');
                                        
                                        // แสดงข้อความแจ้งเตือน
                                        console.log('แจ้งเตือน: Itemno ' + itemno + ' ของลูกค้า ' + customerno + ' มีในระบบแล้ว');
                                    }
                                });
                                
                                // แสดง alert พร้อมรหัสลูกค้า
                                if (existingList.length > 0) {
                                    // สร้าง modal แบบ custom ที่สามารถ copy ได้
                                    var modalHtml = `
                                        <div id="customAlertModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
                                            <div style="background: white; padding: 20px; border-radius: 8px; max-width: 600px; width: 90%; max-height: 80vh; display: flex; flex-direction: column;">
                                                <h4 style="margin-top: 0; color: #d32f2f;">แจ้งเตือน</h4>
                                                <p>มีรหัส item นี้อยู่ในระบบแล้ว:</p>
                                                <textarea readonly style="width: 100%; min-height: 150px; max-height: 400px; border: 1px solid #ccc; padding: 8px; font-family: monospace; font-size: 12px; resize: vertical; flex: 1;">${existingList.join('\n')}</textarea>
                                                <div style="margin-top: 15px; text-align: right; flex-shrink: 0;">
                                                    <button onclick="document.getElementById('customAlertModal').remove(); window.checkWithCustomerorder();" style="background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">ปิด</button>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                    $('body').append(modalHtml);
                                } else {
                                    // ถ้าไม่มี itemno ซ้ำ ให้ตรวจสอบ customerorder ทันที
                                    checkWithCustomerorder();
                                }
                            } else {
                                console.log('No existing itemnos found');
                                // ถ้าไม่มี itemno ซ้ำ ให้ตรวจสอบ customerorder ทันที
                                checkWithCustomerorder();
                            }
                        },
                        error: function() {
                            console.log('เกิดข้อผิดพลาดในการเช็ค itemno');
                        }
                    });
                }
            }
            
            // ฟังก์ชันเช็คกับ customerorder ที่มีอยู่แล้ว
            window.checkWithCustomerorder = function() {
                var itemnos = [];
                var customernos = [];
                
                // รวบรวม itemno และ customerno ทั้งหมด
                $('#dt-mant-table-1 tbody tr').each(function(index) {
                    var itemno = $(this).find('td:last').text().trim();
                    var customerno = $(this).find('td:nth-child(4)').text().trim(); // customerno อยู่ในคอลัมน์ที่ 4
                    
                    if (itemno) {
                        itemnos.push(itemno);
                        customernos.push(customerno);
                    }
                });
                
                if (itemnos.length > 0) {
                    // ส่ง AJAX เพื่อเช็คกับ customerorder
                    $.ajax({
                        url: window.location.hostname === 'localhost' ? '/check-customerorder-exists' : '/skjtrack/check-customerorder-exists',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            itemnos: itemnos,
                            customernos: customernos
                        },
                        success: function(response) {
                            console.log('Response from customerorder check:', response);
                            
                            if (response.missingItemnos && response.missingItemnos.length > 0) {
                                console.log('Found missing itemnos in customerorder:', response.missingItemnos);
                                
                                // เปลี่ยนสีแถวที่ itemno ไม่มีใน customerorder
                                $('#dt-mant-table-1 tbody tr').each(function(index) {
                                    var itemno = $(this).find('td:last').text().trim();
                                    var customerno = $(this).find('td:nth-child(4)').text().trim();
                                    
                                    // ตรวจสอบว่า itemno นี้ไม่มีใน customerorder
                                    var isMissing = response.missingItemnos.some(function(missing) {
                                        return missing.itemno === itemno && missing.customerno === customerno;
                                    });
                                    
                                    if (isMissing) {
                                        $(this).css('background-color', '#f44336'); // สีแดง
                                        
                                        // แสดงข้อความแจ้งเตือน
                                        console.log('แจ้งเตือน: Itemno ' + itemno + ' ของลูกค้า ' + customerno + ' ไม่มีในตาราง customerorder');
                                    }
                                });
                                
                                // สร้าง modal แบบ custom สำหรับ customerorder
                                var missingList = response.missingItemnos.map(function(item) {
                                    return item.itemno + ' (' + item.customerno + ')';
                                });
                                var modalHtml = `
                                    <div id="customAlertModal2" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
                                        <div style="background: white; padding: 20px; border-radius: 8px; max-width: 600px; width: 90%; max-height: 80vh; display: flex; flex-direction: column;">
                                            <h4 style="margin-top: 0; color: #d32f2f;">แจ้งเตือน</h4>
                                            <p>ยังไม่ได้อัพสินค้า:</p>
                                            <textarea readonly style="width: 100%; min-height: 150px; max-height: 400px; border: 1px solid #ccc; padding: 8px; font-family: monospace; font-size: 12px; resize: vertical; flex: 1;">${missingList.join('\n')}</textarea>
                                            <div style="margin-top: 15px; text-align: right; flex-shrink: 0;">
                                                <button onclick="document.getElementById('customAlertModal2').remove(); if(window.duplicateItems && window.duplicateItems.length > 0) { window.showDuplicateInFileModal(window.duplicateItems); }" style="background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">ปิด</button>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                $('body').append(modalHtml);
                            } else {
                                console.log('All itemnos exist in customerorder');
                                // แสดง modal สำหรับรายการซ้ำในไฟล์ Excel ถ้ามี
                                if (window.duplicateItems && window.duplicateItems.length > 0) {
                                    window.showDuplicateInFileModal(window.duplicateItems);
                                }
                            }
                        },
                        error: function() {
                            console.log('เกิดข้อผิดพลาดในการเช็ค customerorder');
                        }
                    });
                }
            };
            
            // เรียกใช้ฟังก์ชันเช็คเมื่อโหลดหน้าเสร็จ (เพิ่ม delay เพื่อให้ตาราง render เสร็จ)
            dataTable.on('draw.dt', function() {
                setTimeout(function() {
                    checkDuplicateItemnos();
                }, 500);
            });
            
            // เรียกใช้ครั้งแรกเมื่อโหลดหน้า
            setTimeout(function() {
                checkDuplicateItemnos();
            }, 1500);
            // // เพิ่มฟีเจอร์ Check All
            // $('#checkAllButton').on('click', function() {
            //     $(':checkbox', dataTable.rows().nodes()).prop('checked', true);
            // });
            //
            // $('#uncheckAllButton').on('click', function() {
            //     $(':checkbox', dataTable.rows().nodes()).prop('checked', false);
            // });
            //
            $('#checkAll').on('change', function() {
                $(':checkbox', dataTable.rows().nodes()).prop('checked', $(this).prop('checked'));
            });

            // หากมีการเลือก checkbox ใดๆ, ตรวจสอบว่าควรเปิดหรือปิดปุ่ม Check All
            $('#dt-mant-table-1 tbody').on('change', ':checkbox', function() {
                var allChecked = $(':checkbox', dataTable.rows().nodes()).length === $(':checkbox:checked', dataTable.rows().nodes()).length;
                $('#checkAll').prop('checked', allChecked);
            });


            $('#confirmimport').on('submit', function(e) {

                var selectedRows = $('tbody').find(':checkbox:checked');
                console.log(selectedRows.length);
                if (selectedRows.length > 0) {
                    var selectedIds = [];
                    selectedRows.each(function() {
                        selectedIds.push($(this).val());
                    });
                    $('#trackIdsInput').val(selectedIds.join(','));

                } else {
                    e.preventDefault();
                    alert("กรุณาเลือกรายการที่ต้องการเพิ่มเข้าระบบ");
                    return false;
                }
            });

            $('#delAll').on('submit', function(e) {
                if (!confirm('ต้องการลบข้อมูลที่จะนำเข้าทั้งหมดใช่หรือไม่?')) {
                    e.preventDefault();
                    return false;
                }
            });
        });

        function showImage(imageUrl) {
            // สร้าง element สำหรับแสดงภาพใหญ่
            var overlay = document.createElement('div');
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
            overlay.style.zIndex = '9999';
            overlay.style.display = 'flex';
            overlay.style.alignItems = 'center';
            overlay.style.justifyContent = 'center';

            var img = document.createElement('img');
            img.src = imageUrl;
            img.style.maxWidth = '80%';
            img.style.maxHeight = '80%';

            // เพิ่มภาพลงใน overlay
            overlay.appendChild(img);

            // เพิ่ม overlay ลงใน body
            document.body.appendChild(overlay);

            // เมื่อคลิกที่ overlay ให้ซ่อนภาพใหญ่
            overlay.onclick = function() {
                document.body.removeChild(overlay);
            }
        }
    </script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Customer Shipping Confirm') }}
                                <a href="{{ route('customershippings.index') }}" class="btn btn-primary btn-sm mr-2"  data-placement="left">
                                     {{ __('หน้าหลัก') }}
                                 </a>
                            </span>

                            <div class="float-right">

                            </div>

                             <div class="float-right">
                                 <form method="POST" id="confirmimport" action="{{route('updatecustomershippings-confirmimport')}}">
                                     @csrf
                                     <input type="hidden" name="debug_track_ids" value="" id="debug_track_ids">
                                     <input type="hidden" name="track_ids" id="trackIdsInput" value="">
                                     <input type="submit" class="btn btn-sm btn-outline-success  mr-2" id="updateSelected" value="ยืนยันเพิ่มข้อมูล">
                                 </form>
                                 <form method="POST" id="delAll" action="{{route('delcustomershippings-confirmimport')}}">
                                     @csrf

                                     <input type="submit" class="btn btn-sm btn-outline-danger  mr-2" id="del" value="ลบข้อมูลทั้งหมด">
                                 </form>

                              </div>

                        </div>
                    </div>

                    @if ($message = Session::get('success'))
                        <div class="alert alert-success">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="dt-mant-table-1">
                                <thead class="thead">
                                    <tr>
                                        <th><input type="checkbox" id="checkAll"></th>
                                        <th>No</th>
                                        <th>วันที่</th>

                                        <th>รหัสลูกค้า</th>
                                        <th>เลขพัสดุ</th>
                                        <th>Cod</th>
                                        <th>น้ำหนัก</th>
                                        <th>หน่วยละ</th>
                                        <th>ค่านำเข้า</th>
                                        <th>รูปหน้ากล่อง</th>
                                        <th>รูปสินค้า</th>
                                        <th>เลขกล่อง</th>
                                        <th class="d-none">โกดัง</th>
                                        <th>วันที่ปิดตู้</th>
                                        <th class="d-none">สถานะ</th>
                                        <th class="d-none">ที่อยู่จัดส่งในไทย</th>
                                        <th>หมายเหตุ</th>
                                        <th>Note Admin</th>
                                        <th class="d-none">กว้าง</th>
                                        <th class="d-none">ยาว</th>
                                        <th class="d-none">สูง</th>
                                        <th>Item</th>

                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($customershippings as $customershipping)
                                    <tr>
                                        <td><input type="checkbox" value="{{$customershipping->id}}"></td>
                                        <td>{{ ++$i }}</td>

                                        <td>
                                            {{ $customershipping->ship_date?\Carbon\Carbon::parse($customershipping->ship_date)->format('d/m/Y'):'' }}
                                        </td>
                                        <td>{{ $customershipping->customerno }}</td>
                                        <td>{{ $customershipping->track_no }}</td>
                                        <td>{{ $customershipping->cod }}</td>
                                        <td>{{ $customershipping->weight }}</td>
                                        <td>
                                            @if($customershipping->iswholeprice == 1)
                                               ราคาเหมา
                                            @else
                                                {{ $customershipping->unit_price }}
                                            @endif</td>
                                        <td>{{ $customershipping->import_cost }}</td>
                                        {{-- <td><img src="{{ asset($customershipping->box_image) }}" class="img-thumbnail" width="50" height="50" onclick="showImage('{{ asset($customershipping->box_image) }}')" style="cursor: pointer;" /></td> --}}
                                        <td><img src="{{ $customershipping->box_image }}" class="img-thumbnail" width="50" height="50" onclick="showImage('{{ $customershipping->box_image }}')" style="cursor: pointer;" /></td>
                       
                                        <td><img src="{{ asset($customershipping->product_image) }}" class="img-thumbnail" width="50" height="50" onclick="showImage('{{ asset($customershipping->product_image) }}')" style="cursor: pointer;" /></td>
                                        <td>{{ $customershipping->box_no }}</td>
                                        <td class="d-none">{{ $customershipping->warehouse }}</td>
                                        <td> {{ $customershipping->etd?\Carbon\Carbon::parse($customershipping->etd)->format('d/m/Y'):'' }}</td>

                                        <td class="d-none">{{ \App\Models\ShippingStatus::getNameById($customershipping->status) }}</td>
                                        <td class="d-none">{{ $customershipping->delivery_address }}</td>
                                        <td>{{ $customershipping->note }}</td> 
                                        <td>{{ $customershipping->note_admin }}</td>
                                        <td class="d-none">{{ $customershipping->width }}</td>
                                        <td class="d-none">{{ $customershipping->length }}</td>
                                        <td class="d-none">{{ $customershipping->height }}</td>
                                        <td>{{ $customershipping->itemno }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
{{--                {!! $tracks->links() !!}--}}
            </div>
        </div>
    </div>
@endsection

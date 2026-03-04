@extends('layouts.app')

@section('title')
    SKJ JAPAN
@endsection
@section('extra-script')
    <script>
        $(function () {
            // var dataTable=$('#dt-mant-table-1').DataTable({
            //     "columnDefs": [
            //         {
            //             "targets": [0, 1, 4, 5, 6, 8, 9,11], // เป็นลำดับของคอลัมน์ที่ไม่ต้องการให้ค้นหา
            //             "searchable": false,
            //             "orderable": true
            //         },
            //         {
            //             "targets": [2, 3, 7,10], // เป็นลำดับของคอลัมน์ที่ต้องการให้ค้นหา
            //             "searchable": true,
            //             "orderable": true
            //         }
            //     ],
            //     "lengthMenu": [10,20,30,50,100,200, 300, 400, 500,600], // ตัวเลือกที่สามารถเลือกได้
            //     "pageLength": 100
            // });


            var dataTable=$('#dt-mant-table-1').DataTable({
                "processing": true,
                "serverSide": true,
                "language": {
                    "processing": "กำลังโหลด..."
                },
                "ajax": {
                    "url": "{{ route('fetch.track') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function (d){

                        d.search=$("input[type='search']").val();
                        // d.status = $("select.status").val();
                        // d.shipping_status = $("select.shipping_status").val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d._token = "{{ csrf_token() }}";




                    }
                },
                "lengthMenu": [10,20,30,50,100,200, 300, 400, 500,600,1000,5000,10000], // ตัวเลือกที่สามารถเลือกได้
                "pageLength": 1000,
                "initComplete": function () {


                },
                "columnDefs": [
                    { "targets": 0, "data": null,"orderable": false, "render": function (data, type, full, meta) {

                            return `<input type="checkbox" value="${full.id}">`;

                        }
                    },
                    { "targets": 1, "data": null,title:"No","orderable": false, "render": function (data, type, full, meta) {
                            return meta.row + 1;
                        } },
                    { "targets": 2, "data": "customer_name",
                        "render": function (data, type, full, meta) {

                            return `
                            <div>${data}</div>
            <form action="${full.action_del}" method="POST">
                <a class="btn btn-sm btn-success" href="${full.edit_url}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
                @csrf

                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('คุณแน่ใจว่าต้องการจะลบข้อมูลรายการนี้?') }}')" ><i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}</button>
            </form>
        `;
                        }}, // คอลัมน์ที่ 1
                    { "targets": 3, "data": "track_no" },
                    { "targets": 4, "data": "cod" },
                    { "targets": 5, "data": "weight" },

                    { "targets": 6, "data": "source_date" },
                    { "targets": 7, "data": "ship_date" },
                    { "targets": 8, "data": "destination_date" },
                    { "targets": 9, "data": "note" }


                ],
            });

            $('#start_date,#end_date').on('change', function () {
                console.log('start_date end_date change');
                dataTable.ajax.reload();

            });


            // สร้างตัวแปรเพื่อเก็บสถานะการโหลดครั้งแรกและค่าการค้นหาก่อนหน้า
            var initialLoad = true;
            var previousSearchValue = ''; // เก็บค่าการค้นหาก่อนหน้า

            dataTable.on('xhr.dt', function(e, settings, json, xhr) {
                // ดึงข้อมูลที่ส่งกลับมาจากการเรียกใช้ AJAX
                if (initialLoad || settings.oPreviousSearch.sSearch !== previousSearchValue || $('#start_date').val() !== previousStartDate) {
                    initialLoad = false;
                    previousSearchValue = settings.oPreviousSearch.sSearch; // อัปเดตค่าการค้นหาก่อนหน้า
                    previousStartDate = $('#start_date').val(); // อัปเดตค่า start_date ก่อนหน้า
                    initialLoad = false; // ตั้งค่า initialLoad เป็น false หลังจากการโหลดครั้งแรก

                }

            });



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


            $('#updateSelected,#updateSelected2').on('click', function() {

                var selectedRows = $('tbody').find(':checkbox:checked');
                console.log(selectedRows.length);
                if (selectedRows.length > 0) {
                    var selectedIds = [];
                    selectedRows.each(function() {
                        selectedIds.push($(this).val());
                    });
                    $('#trackIdsInput').val(selectedIds.join(','));
                    $('#trackIdsInput2').val(selectedIds.join(','));

                } else {
                    alert("กรุณาเลือกรายการที่ต้องการลบ");
                }
            });
        });</script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Track') }}
                            </span>

                            <div class="float-right">
                                <form method="POST" action="{{route('update-status2')}}">
                                    @csrf
                                    <input type="hidden" name="track_ids2" id="trackIdsInput2" value="">
                                    <input type="date" class="form-control col-11  mr-2" id="date2" name="date2" value="{{ \Carbon\Carbon::now()->toDateString() }}">
                                    <input type="submit" class="btn btn-sm btn-outline-info  mr-2" id="updateSelected2" value="อัพเดทETD">
                                </form>
                            </div>
                            <div class="float-right">
                                <form method="POST" action="{{route('update-status')}}">
                                    @csrf
                                    <input type="hidden" name="track_ids" id="trackIdsInput" value="">
                                <input type="date" class="form-control col-11  mr-2" id="date" name="date" value="{{ \Carbon\Carbon::now()->toDateString() }}">
                                <input type="submit" class="btn btn-sm btn-outline-success  mr-2" id="updateSelected" value="อัพเดทสถานะ สินค้าถึงไทยแล้ว">
                                </form>
                            </div>
                             <div class="float-right">

                                <a href="{{ route('tracks.create') }}" class="btn btn-primary btn-sm float-right mr-2"  data-placement="left">
                                  {{ __('Create New') }}
                                </a>
                                 <a href="{{url('tracksimport')}}" class="btn btn-warning btn-sm float-right mr-2"  data-placement="left">
                                     {{ __('IMPORT EXCEL') }}
                                 </a>
                                 <a href="{{ route('welcome') }}" class="btn btn-default btn-sm float-right mr-2"  data-placement="left">
                                     {{ __('Dashboard') }}
                                 </a>

{{--                                 <button class="btn btn-sm btn-secondary" id="checkAllButton">Check All</button>--}}
{{--                                 <button class="btn btn-sm btn-secondary" id="uncheckAllButton">Uncheck All</button>--}}
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
                            <div id="dateFilters" class="mb-3 col-12 right">
                                <label for="start_date" class="control-label"> วันที่เริ่มต้น (ETD)</label>
                                <input type="date" id="start_date" class="form-control col-3" placeholder="วันที่เริ่มต้น">
                                <label for="end_date" class="control-label"> วันที่สิ้นสุด</label>
                                <input type="date" id="end_date" class="form-control col-3" placeholder="วันที่สิ้นสุด">
                            </div>
                            <table class="table table-striped table-hover" id="dt-mant-table-1">
                                <thead class="thead">
                                    <tr>
                                        <th><input type="checkbox" id="checkAll"></th>
                                        <th>No</th>
										<th>Customer Name</th>
										<th>Track No</th>
										<th>Cod</th>
										<th>Weight</th>
										<th>Source Date</th>
										<th>ETD</th>
										<th>Destination Date</th>
										<th>Note</th>

                                    </tr>
                                </thead>
                                <tbody>
{{--                                    @foreach ($tracks as $track)--}}
{{--                                        <tr>--}}
{{--                                            <td><input type="checkbox" value="{{$track->id}}"></td>--}}
{{--                                            <td>{{ ++$i }}</td>--}}

{{--											<td>{{ $track->customer_name }}</td>--}}
{{--											<td>{{ $track->track_no }}</td>--}}
{{--											<td>{{ $track->cod }}</td>--}}
{{--											<td>{{ $track->weight }} kg</td>--}}
{{--											<td>{{ $track->source_date?\Carbon\Carbon::parse($track->source_date)->format('d/m/Y'):'' }}</td>--}}
{{--											<td>{{ $track->ship_date?\Carbon\Carbon::parse($track->ship_date)->format('d/m/Y'):'' }}</td>--}}
{{--											<td>{{ $track->destination_date?\Carbon\Carbon::parse($track->destination_date)->format('d/m/Y'):'' }}</td>--}}
{{--											<td>{{ $track->note }}</td>--}}
{{--                                            <td class="d-none">{{ $track->tracknodash }}</td>--}}
{{--                                            <td>--}}
{{--                                                <form action="{{ route('tracks.destroy',$track->id) }}" method="POST">--}}
{{--                                                    <a class="btn btn-sm btn-primary " href="{{ route('tracks.show',$track->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>--}}
{{--                                                    <a class="btn btn-sm btn-success" href="{{ route('tracks.edit',$track->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>--}}
{{--                                                    @csrf--}}
{{--                                                    @method('DELETE')--}}
{{--                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('คุณต้องการลบข้อมูลนี้หรือไม่?')"><i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}</button>--}}
{{--                                                </form>--}}
{{--                                            </td>--}}

{{--                                        </tr>--}}
{{--                                    @endforeach--}}
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

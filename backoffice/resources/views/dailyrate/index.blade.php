@extends('layouts.app')

@section('template_title')
    Dailyrate
@endsection
@section('extra-script')
    <script>$(function () {
            var dataTable=$('#dt-mant-table-1').DataTable({
                // "columnDefs": [
                //     {
                //         "targets": [0, 1, 4, 5, 6, 8, 9, 10], // เป็นลำดับของคอลัมน์ที่ไม่ต้องการให้ค้นหา
                //         "searchable": false,
                //         "orderable": true
                //     },
                //     {
                //         "targets": [2, 3, 7], // เป็นลำดับของคอลัมน์ที่ต้องการให้ค้นหา
                //         "searchable": true,
                //         "orderable": true
                //     }
                // ],
                "lengthMenu": [10,20,30,50,100,200, 300, 400, 500,600], // ตัวเลือกที่สามารถเลือกได้
                "pageLength": 100,
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
                    // ทำ AJAX request เพื่ออัปเดตสถานะ
                    {{--$.ajax({--}}
                    {{--    url: '/update-status',--}}
                    {{--    type: 'POST',--}}
                    {{--    data: {--}}
                    {{--        track_ids: selectedIds,--}}
                    {{--        destination_date: $('#date').val(),--}}
                    {{--        _token: '{{ csrf_token() }}',--}}
                    {{--    },--}}
                    {{--    success: function(response) {--}}
                    {{--        console.log(response.message);--}}
                    {{--        // ทำสิ่งที่คุณต้องการหลังจากอัปเดตสถานะเรียบร้อย--}}
                    {{--    },--}}
                    {{--    error: function(error) {--}}
                    {{--        console.error('Error:', error);--}}
                    {{--    }--}}
                    {{--});--}}
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
                                {{ __('Dailyrate') }}
                            </span>

                             <div class="float-right">
                                <a href="{{ route('dailyrates.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">
                                  {{ __('Create New') }}
                                </a>
                                 <a href="{{ route('welcome') }}" class="btn btn-default btn-sm float-right mr-2"  data-placement="left">
                                     {{ __('Dashboard') }}
                                 </a>
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
                                        <th>No</th>

{{--										<th>Name</th>--}}
										<th>Order Rate (เยน/บาท)</th>
										<th>COD Rate (เยน/บาท)</th>
										<th>Datetimerate</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dailyrates as $dailyrate)
                                        <tr>
                                            <td>{{ ++$i }}</td>

{{--											<td>{{ $dailyrate->name }}</td>--}}
											<td>{{ $dailyrate->rateprice }}</td>
											<td>{{ $dailyrate->cod_rate ?? '-' }}</td>
                                            <td>{{ $dailyrate->datetimerate?\Carbon\Carbon::parse($dailyrate->datetimerate)->format('d/m/Y H:i:s'):'' }}</td>
                                            <td>
                                                <form action="{{ route('dailyrates.destroy',$dailyrate->id) }}" method="POST">
{{--                                                    <a class="btn btn-sm btn-primary " href="{{ route('dailyrates.show',$dailyrate->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>--}}
                                                    <a class="btn btn-sm btn-success" href="{{ route('dailyrates.edit',$dailyrate->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('คุณแน่ใจว่าต้องการจะลบข้อมูลรายการนี้?') }}')"><i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {!! $dailyrates->links() !!}
            </div>
        </div>
    </div>
@endsection

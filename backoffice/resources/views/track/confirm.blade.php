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
                                {{ __('Track Confirm') }}
                                <a href="{{ route('tracks.index') }}" class="btn btn-primary btn-sm mr-2"  data-placement="left">
                                     {{ __('หน้าหลัก') }}
                                 </a>
                            </span>

                            <div class="float-right">
{{--                                <form method="POST" action="{{route('update-status2')}}">--}}
{{--                                    @csrf--}}
{{--                                    <input type="hidden" name="track_ids2" id="trackIdsInput2" value="">--}}
{{--                                    <input type="date" class="form-control col-11  mr-2" id="date2" name="date2" value="{{ \Carbon\Carbon::now()->toDateString() }}">--}}
{{--                                    <input type="submit" class="btn btn-sm btn-outline-info  mr-2" id="updateSelected2" value="อัพเดทETD">--}}
{{--                                </form>--}}
                            </div>

                             <div class="float-right">
                                 <form method="POST" id="confirmimport" action="{{route('update-confirmimport')}}">
                                     @csrf
                                     <input type="hidden" name="track_ids" id="trackIdsInput" value="">
                                     <input type="submit" class="btn btn-sm btn-outline-success  mr-2" id="updateSelected" value="ยืนยันเพิ่มข้อมูล">
                                 </form>
                                 <form method="POST" id="delAll" action="{{route('del-confirmimport')}}">
                                     @csrf

                                     <input type="submit" class="btn btn-sm btn-outline-danger  mr-2" id="del" value="ลบข้อมูลทั้งหมด">
                                 </form>

{{--                                <a href="{{ route('tracks.create') }}" class="btn btn-success btn-sm float-right mr-2"  data-placement="left">--}}
{{--                                  {{ __('ยืนยันเพิ่มข้อมูล') }}--}}
{{--                                </a>--}}
{{--                                 <a href="{{url('tracksimport')}}" class="btn btn-warning btn-sm float-right mr-2"  data-placement="left">--}}
{{--                                     {{ __('IMPORT EXCEL') }}--}}
{{--                                 </a>--}}


{{--                                 <button class="btn btn-sm btn-secondary" id="checkAllButton">Check All</button>--}}
{{--                                 <button class="btn btn-sm btn-secondary" id="uncheckAllButton">Uncheck All</button>--}}
                              </div>
{{--                            <div class="float-right">--}}
{{--                                <a href="{{ route('tracks.index') }}" class="btn btn-primary btn-sm float-right mr-2"  data-placement="left">--}}
{{--                                    {{ __('หน้าหลัก') }}--}}
{{--                                </a>--}}
{{--                            </div>--}}
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

										<th>Customer Name</th>
										<th>Track No</th>
										<th>Cod</th>
										<th>Weight</th>
										<th>Source Date</th>
										<th>ETD</th>
										<th>Destination Date</th>
										<th>Note</th>

{{--                                        <th></th>--}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tracks as $track)
                                        <tr>
                                            <td><input type="checkbox" value="{{$track->id}}"></td>
                                            <td>{{ ++$i }}</td>

											<td>{{ $track->customer_name }}</td>
											<td>{{ $track->track_no }}</td>
											<td>{{ $track->cod }}</td>
											<td>{{ $track->weight }} kg</td>
											<td>{{ $track->source_date?\Carbon\Carbon::parse($track->source_date)->format('d/m/Y'):'' }}</td>
											<td>{{ $track->ship_date?\Carbon\Carbon::parse($track->ship_date)->format('d/m/Y'):'' }}</td>
											<td>{{ $track->destination_date?\Carbon\Carbon::parse($track->destination_date)->format('d/m/Y'):'' }}</td>
											<td>{{ $track->note }}</td>

{{--                                            <td>--}}
{{--                                                <form action="{{ route('tracks.destroy',$track->id) }}" method="POST">--}}
{{--                                                    <a class="btn btn-sm btn-primary " href="{{ route('tracks.show',$track->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>--}}
{{--                                                    <a class="btn btn-sm btn-success" href="{{ route('tracks.edit',$track->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>--}}
{{--                                                    @csrf--}}
{{--                                                    @method('DELETE')--}}
{{--                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('คุณต้องการลบข้อมูลนี้หรือไม่?')"><i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}</button>--}}
{{--                                                </form>--}}
{{--                                            </td>--}}
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

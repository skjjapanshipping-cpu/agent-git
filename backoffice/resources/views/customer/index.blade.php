@extends('layouts.app')

@section('template_title')
    รายการลูกค้า
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap:wrap; gap:8px;">
                            <span id="card_title" style="font-weight:600; font-size:18px;">
                                <i class="fa fa-address-book"></i> รายการลูกค้า
                                <span class="badge badge-light" style="margin-left:8px;">{{ count($customers) }}</span>
                            </span>

                            <div style="display:flex; gap:8px;">
                                <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm" style="background:#1D8AC9; border-color:#1D8AC9;">
                                    <i class="fa fa-user-plus"></i> เพิ่มสมาชิกใหม่
                                </a>
                                <a href="{{ route('welcome') }}" class="btn btn-default btn-sm">
                                    <i class="fa fa-dashboard"></i> Dashboard
                                </a>
                            </div>
                        </div>
                    </div>

                    @if ($message = Session::get('success'))
                        <div class="alert alert-success" style="margin:12px;">
                            <i class="fa fa-check-circle"></i> {{ $message }}
                        </div>
                    @endif

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="dt-mant-table-fix-showall">
                                <thead class="thead">
                                    <tr>
                                        <th>No</th>
                                        <th>รหัสลูกค้า</th>
                                        <th>ชื่อลูกค้า</th>
                                        <th>เบอร์โทร</th>
                                        <th>อีเมล</th>
                                        <th>จังหวัด</th>
                                        <th>อำเภอ</th>
                                        <th>ตำบล</th>
                                        <th>รหัสปณ.</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($customers as $customer)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            <td>
                                                <strong style="color:#1D8AC9; font-family:'SF Mono','Menlo',monospace;">{{ strtoupper($customer->customerno) }}</strong>
                                                @if(in_array($customer->id, $lineUserIds))
                                                    <span style="color:#06C755; font-size:14px;" title="เชื่อมต่อ LINE แล้ว"><i class="fa fa-commenting"></i></span>
                                                @endif
                                            </td>
                                            <td>{{ $customer->name }}</td>
                                            <td>{{ $customer->mobile }}</td>
                                            <td><small>{{ $customer->email }}</small></td>
                                            <td>{{ $customer->province }}</td>
                                            <td>{{ $customer->distrinct }}</td>
                                            <td>{{ $customer->subdistrinct }}</td>
                                            <td>{{ $customer->postcode }}</td>
                                            <td style="white-space:nowrap;">
                                                <a class="btn btn-sm btn-success" href="{{ route('customers.edit',$customer->id) }}" title="แก้ไข">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('customers.resetPassword', $customer->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('ยืนยันรีเซ็ตรหัสผ่านของ {{ strtoupper($customer->customerno) }} และส่งอีเมลใหม่?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" title="รีเซ็ตรหัสผ่าน + ส่งอีเมล">
                                                        <i class="fa fa-key"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('customers.destroy',$customer->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('ต้องการลบลูกค้า {{ strtoupper($customer->customerno) }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="ลบ">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

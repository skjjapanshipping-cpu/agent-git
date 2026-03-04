@extends('layouts.app')

@section('template_title')
    Customer
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Customer') }}
                            </span>

{{--                             <div class="float-right">--}}
{{--                                <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm float-right"  data-placement="left">--}}
{{--                                  {{ __('Create New') }}--}}
{{--                                </a>--}}
{{--                              </div>--}}

                            <div class="float-right">

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




                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($customers as $customer)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            <td>{{ strtoupper($customer->customerno) }} @if(in_array($customer->id, $lineUserIds))<span style="color:#06C755; font-size:14px;" title="เชื่อมต่อ LINE แล้ว"><i class="fa fa-commenting"></i></span>@endif</td>
                                            <td>{{ $customer->name }}</td>
                                            <td>{{ $customer->mobile }}</td>
                                            <td>{{ $customer->email }}</td>
											<td>{{ $customer->province }}</td>
											<td>{{ $customer->distrinct }}</td>
											<td>{{ $customer->subdistrinct }}</td>
											<td>{{ $customer->postcode }}</td>




                                            <td>
                                                <form action="{{ route('customers.destroy',$customer->id) }}" method="POST">
{{--                                                    <a class="btn btn-sm btn-primary " href="{{ route('customers.show',$customer->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a>--}}
                                                    <a class="btn btn-sm btn-success" href="{{ route('customers.edit',$customer->id) }}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="confirm('ต้องการลบข้อมูลนี้?')" class="btn btn-danger btn-sm"><i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
{{--                {!! $customers->links() !!}--}}
            </div>
        </div>
    </div>
@endsection

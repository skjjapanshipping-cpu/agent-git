@extends('home')

@section('title')
	User
@endsection

@section('extra-css')
<style>
    .user-customer-code {
        display: inline-block;
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.3px;
        color: #0c5e8e;
        background: linear-gradient(135deg, #ecfeff 0%, #eff6ff 100%);
        border: 1px solid #bae6fd;
        padding: 3px 9px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s;
        white-space: nowrap;
        user-select: all;
    }
    .user-customer-code:hover {
        background: linear-gradient(135deg, #cffafe 0%, #dbeafe 100%);
        border-color: #0ea5e9;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(14,165,233,0.18);
    }
    .user-customer-code:active { transform: translateY(0); }
    .user-code-toast {
        position: fixed; bottom: 24px; left: 50%;
        transform: translateX(-50%) translateY(20px);
        background: #1e293b; color: #fff;
        padding: 10px 18px; border-radius: 8px;
        font-size: 13px; font-weight: 600;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        opacity: 0; pointer-events: none;
        transition: opacity 0.2s, transform 0.2s;
        z-index: 9999;
    }
    .user-code-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
</style>
@endsection

@section('index')
<div class="content">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="card">
                <div class="">
                    <h3>User Details</h3>
                    <a href="{{route('users.create')}}" class="btn btn-success btn-sm">Add New User</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dt-mant-table-fix-showall">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Customer No</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $row)
                                <tr>
                                    <td>{{ $row->id }}</td>
                                    <td>
                                        @if($row->customercode)
                                            <span class="user-customer-code" title="คัดลอก" data-copy="{{ $row->customercode }}">{{ $row->customercode }}</span>
                                        @else
                                            <span class="text-muted" style="font-size:11px;">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $row->name }}</td>
                                    <td>{{ $row->email }}</td>
                                    <td>{{ $row->mobile }}</td>
                                    <td>
                                        @foreach($row->roles()->pluck('name') as $role)
                                            {{ $role }}
                                        @endforeach
                                    </td>
                                    <td>
                                        <div style="display:flex;">
                                        <a href="{{route('users.edit',$row->id)}}" class="btn btn-warning btn-sm">Edit</a>
                                            &nbsp;
                                        <form id="delete_form{{$row->id}}" method="POST" action="{{ route('users.destroy',$row->id) }}" onclick="return confirm('Are you sure?')">
                                            @csrf
                                            <input name="_method" type="hidden" value="DELETE">
                                            <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                                        </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
{{--                        {{ $users->links() }}--}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-script')
<script>
    (function(){
        var toast = null;
        function showToast(msg) {
            if (!toast) {
                toast = document.createElement('div');
                toast.className = 'user-code-toast';
                document.body.appendChild(toast);
            }
            toast.textContent = msg;
            toast.classList.add('show');
            clearTimeout(toast._t);
            toast._t = setTimeout(function(){ toast.classList.remove('show'); }, 1600);
        }
        document.addEventListener('click', function(e) {
            var el = e.target.closest('.user-customer-code');
            if (!el) return;
            var code = el.getAttribute('data-copy') || el.textContent.trim();
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(code).then(function(){
                    showToast('คัดลอกแล้ว: ' + code);
                });
            } else {
                var ta = document.createElement('textarea');
                ta.value = code; document.body.appendChild(ta);
                ta.select(); try { document.execCommand('copy'); } catch(_){}
                document.body.removeChild(ta);
                showToast('คัดลอกแล้ว: ' + code);
            }
        });
    })();
</script>
@endsection

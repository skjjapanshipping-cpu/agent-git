<?php

namespace App\Http\Controllers;

use App\Mail\CustomerCredentialsMail;
use App\Models\Customer;
use App\Models\SystemSetting;
use App\MyAuthProvider;
use App\Tambon;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = User::whereHas('roles', function ($q) {
            $q->where('name', 'user');
        })->orderBy('id', 'desc')->get();

        $lineUserIds = MyAuthProvider::where('provider', 'line')->pluck('userid')->toArray();
        return view('customer.index', compact('customers', 'lineUserIds'))->with('i', 0);
    }

    /**
     * แสดงฟอร์มเพิ่มลูกค้าใหม่ — admin เป็นผู้เปิดบัญชีให้
     */
    public function create()
    {
        $customer = new User();
        $provinces = Tambon::getCachedProvinces();
        $amphoes = Tambon::getCachedAmphoes();
        $tambons = Tambon::getCachedTambons();
        $deliveryTypes = \App\Models\DeliveryType::all();
        $suggestedCustomerno = strtoupper(User::generateNextCustomerno());

        return view('customer.create', compact(
            'customer', 'provinces', 'amphoes', 'tambons', 'deliveryTypes', 'suggestedCustomerno'
        ));
    }

    /**
     * บันทึกลูกค้าใหม่ + สุ่มรหัสผ่าน + ส่งอีเมล + แสดงหน้า credentials
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'mobile'       => ['required', 'string', 'max:20', 'unique:users,mobile'],
            'customerno'   => ['nullable', 'string', 'max:32'],
            'addr'         => ['nullable', 'string', 'max:255'],
            'province'     => ['nullable', 'string', 'max:100'],
            'distrinct'    => ['nullable', 'string', 'max:100'],
            'subdistrinct' => ['nullable', 'string', 'max:100'],
            'postcode'     => ['nullable', 'string', 'max:10'],
        ], [
            'name.required'   => 'กรุณากรอกชื่อ-นามสกุล',
            'email.required'  => 'กรุณากรอกอีเมล',
            'email.email'     => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique'    => 'อีเมลนี้ถูกใช้งานแล้ว',
            'mobile.required' => 'กรุณากรอกเบอร์โทรศัพท์',
            'mobile.unique'   => 'เบอร์โทรศัพท์นี้ถูกใช้งานแล้ว',
        ]);

        // ใช้ customerno ที่ admin ระบุ ถ้าไม่ได้ระบุให้ระบบสร้างอัตโนมัติ
        $customerno = trim(strtolower($request->input('customerno', '')));
        if ($customerno === '' || !preg_match('/^anw-\d+$/', $customerno)) {
            $customerno = User::generateNextCustomerno();
        }

        // ป้องกัน customerno ซ้ำ
        $exists = User::where('customerno', $customerno)->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['customerno' => 'รหัสลูกค้านี้ถูกใช้งานแล้ว: ' . strtoupper($customerno)]);
        }

        // สุ่มรหัสผ่านปลอดภัย (10 ตัว: อักษรใหญ่ พิมพ์เล็ก ตัวเลข)
        $plainPassword = self::generateSecurePassword();

        $customer = User::create([
            'name'         => $request->input('name'),
            'email'        => $request->input('email'),
            'mobile'       => $request->input('mobile'),
            'password'     => Hash::make($plainPassword),
            'customerno'   => $customerno,
            'addr'         => $request->input('addr'),
            'province'     => $request->input('province'),
            'distrinct'    => $request->input('distrinct'),
            'subdistrinct' => $request->input('subdistrinct'),
            'postcode'     => $request->input('postcode'),
        ]);

        $customer->assignRole('user');

        // ส่งอีเมล welcome + credentials
        $emailStatus = 'skipped';
        $emailError = null;
        try {
            $warehouses = SystemSetting::warehouses($customer->customerno);
            $contactNote = SystemSetting::contactNote();
            $support = SystemSetting::support();
            Mail::to($customer->email)->send(new CustomerCredentialsMail($customer, $plainPassword, $warehouses, $support, $contactNote));
            $emailStatus = 'sent';
        } catch (\Throwable $e) {
            $emailStatus = 'failed';
            $emailError = $e->getMessage();
            Log::warning('[customers.store] send welcome email failed', [
                'customer_id' => $customer->id,
                'email' => $customer->email,
                'error' => $emailError,
            ]);
        }

        // เก็บข้อมูล credentials ใน session ครั้งเดียว (one-time view)
        session()->flash('new_customer_credentials', [
            'id'             => $customer->id,
            'name'           => $customer->name,
            'email'          => $customer->email,
            'mobile'         => $customer->mobile,
            'customerno'     => strtoupper($customer->customerno),
            'plain_password' => $plainPassword,
            'email_status'   => $emailStatus,
            'email_error'    => $emailError,
        ]);

        return redirect()->route('customers.credentials', $customer->id);
    }

    /**
     * แสดงหน้า credentials ครั้งเดียวหลังเปิดบัญชี (พร้อมปุ่ม copy + print + ส่งซ้ำ)
     */
    public function showCredentials($id)
    {
        $customer = User::findOrFail($id);
        $credentials = session('new_customer_credentials');

        // แสดง credentials เฉพาะของลูกค้าที่ตรงกับ session เท่านั้น (กันโชว์รหัสผ่านลูกค้าคนอื่น)
        if (!$credentials || (int) ($credentials['id'] ?? 0) !== (int) $id) {
            $credentials = null;
        } else {
            // ใช้ครั้งเดียวแล้วล้างทิ้ง (รหัสผ่าน plaintext ไม่ควรค้างใน session)
            session()->forget('new_customer_credentials');
        }

        $warehouses = SystemSetting::warehouses($customer->customerno);
        $contactNote = SystemSetting::contactNote();
        $support = SystemSetting::support();

        return view('customer.credentials', compact('customer', 'credentials', 'warehouses', 'contactNote', 'support'));
    }

    /**
     * รีเซ็ตรหัสผ่านลูกค้า + ส่งอีเมลใหม่ (admin only)
     */
    public function resetPassword($id)
    {
        $customer = User::findOrFail($id);
        $plainPassword = self::generateSecurePassword();
        $customer->password = Hash::make($plainPassword);
        $customer->save();

        $emailStatus = 'skipped';
        $emailError = null;
        try {
            $warehouses = SystemSetting::warehouses($customer->customerno);
            $contactNote = SystemSetting::contactNote();
            $support = SystemSetting::support();
            Mail::to($customer->email)->send(new CustomerCredentialsMail($customer, $plainPassword, $warehouses, $support, $contactNote));
            $emailStatus = 'sent';
        } catch (\Throwable $e) {
            $emailStatus = 'failed';
            $emailError = $e->getMessage();
            Log::warning('[customers.resetPassword] email failed', [
                'customer_id' => $customer->id,
                'error' => $emailError,
            ]);
        }

        session()->flash('new_customer_credentials', [
            'id'             => $customer->id,
            'name'           => $customer->name,
            'email'          => $customer->email,
            'mobile'         => $customer->mobile,
            'customerno'     => strtoupper($customer->customerno),
            'plain_password' => $plainPassword,
            'email_status'   => $emailStatus,
            'email_error'    => $emailError,
            'is_reset'       => true,
        ]);

        return redirect()->route('customers.credentials', $customer->id);
    }

    public function show($id)
    {
        $customer = User::findOrFail($id);
        return view('customer.show', compact('customer'));
    }

    public function edit($id)
    {
        $customer = User::findOrFail($id);
        $provinces = Tambon::getCachedProvinces();
        $amphoes = Tambon::getCachedAmphoes();
        $tambons = Tambon::getCachedTambons();
        $deliveryTypes = \App\Models\DeliveryType::all();
        return view('customer.edit', compact('customer', 'provinces', 'amphoes', 'tambons', 'deliveryTypes'));
    }

    public function update(Request $request, User $customer)
    {
        // ห้าม mass-assign password แบบ plaintext — แยกจัดการ + hash เสมอ
        $data = $request->except(['password', 'password_confirmation', '_token', '_method', 'id', 'roles']);
        $customer->update($data);

        if ($request->filled('password')) {
            $customer->password = \Illuminate\Support\Facades\Hash::make($request->input('password'));
            $customer->save();
        }

        return redirect()->route('customers.index')->with('success', 'อัปเดตลูกค้าสำเร็จ');
    }

    public function destroy($id)
    {
        $customer = User::findOrFail($id);
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'ลบลูกค้าสำเร็จ');
    }

    /**
     * สุ่มรหัสผ่านปลอดภัย: ประกอบด้วยอักษรใหญ่ + เล็ก + ตัวเลข อย่างน้อยอย่างละ 1 ตัว
     * ความยาว 10 ตัว ไม่ใช้อักขระสับสน (0/O/l/1/I)
     */
    public static function generateSecurePassword(int $length = 10): string
    {
        $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // ไม่มี I, O
        $lower = 'abcdefghjkmnpqrstuvwxyz';  // ไม่มี i, l, o
        $digit = '23456789';                 // ไม่มี 0, 1
        $all   = $upper . $lower . $digit;

        $chars = [
            $upper[random_int(0, strlen($upper) - 1)],
            $lower[random_int(0, strlen($lower) - 1)],
            $digit[random_int(0, strlen($digit) - 1)],
        ];

        for ($i = count($chars); $i < $length; $i++) {
            $chars[] = $all[random_int(0, strlen($all) - 1)];
        }

        shuffle($chars);
        return implode('', $chars);
    }
}

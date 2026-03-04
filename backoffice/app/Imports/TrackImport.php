<?php

namespace App\Imports;

use App\Models\Track;
use Illuminate\Database\QueryException;
use Maatwebsite\Excel\Concerns\ToModel;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class TrackImport implements ToModel,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    private $loggedKeys = false;

    public function model(array $row)
    {
        // Debug: log keys ของแถวแรกเพื่อตรวจสอบว่า header ตรงกับที่คาดหวัง
        if (!$this->loggedKeys) {
            Log::info('TrackImport: row keys from Excel', ['keys' => array_keys($row), 'first_row' => $row]);
            $this->loggedKeys = true;
        }

        // ตรวจสอบว่ามีค่า null ในแถวหรือไม่
        if (empty($row['name'] ?? null)) {
            Log::warning('TrackImport: row skipped (name is empty)', ['row_keys' => array_keys($row), 'row' => $row]);
            return null;
        }
    try{
    $sourceDate = !empty($row['date'])
        ? (is_numeric($row['date'])
            ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['date']))->format('Y-m-d')
            : Carbon::createFromFormat('d/m/Y', $row['date'])->format('Y-m-d'))
        : null;

    $customer_name =is_null($row['name'])?null:$row['name'];

    $shipdate = !empty($row['etd'])
        ? (is_numeric($row['etd'])
            ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['etd']))->format('Y-m-d')
            : Carbon::createFromFormat('d/m/Y', $row['etd'])->format('Y-m-d'))
        : null;

    $weight = is_null($row['weight']) ? null : $row['weight'];

    // ทำความสะอาดค่า cod โดยลบสัญลักษณ์ ¥ และ comma ออก
    $cod = is_null($row['cod']) ? null : $row['cod'];
    if ($cod !== null) {
        // แปลงเป็น string ก่อน แล้วลบสัญลักษณ์ ¥, comma, และ whitespace ออก
        $cod = str_replace(['¥', ',', ' '], '', (string)$cod);
        // แปลงเป็นตัวเลข (float) ถ้าเป็นตัวเลข
        $cod = is_numeric($cod) ? (float)$cod : null;
    }

    return new Track([
        'source_date' => $sourceDate
        , 'customer_name' =>$customer_name
        , 'track_no' => $row['tracking_no']
        , 'cod' => $cod
        , 'weight' => $weight
        , 'ship_date' => $shipdate
        , 'note' => $row['note']
        , 'status' => 0

    ]);
    } catch (QueryException $e) {
        Log::error('TrackImport QueryException: ' . $e->getMessage(), ['sql' => $e->getSql()]);
        return null;
    } catch (\Exception $e) {
        Log::error('TrackImport Exception: ' . $e->getMessage(), ['row' => $row]);
        return null;
    }

    }
}

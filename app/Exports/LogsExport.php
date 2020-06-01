<?php

namespace App\Exports;

use App\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LogsExport implements FromCollection, WithCustomCsvSettings, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    private $date_from;
    private $date_to;
    function __construct($dateFrom,$dateTo)
    {
            $this->date_from=$dateFrom;
            $this->date_to=$dateTo;
    }
    public function collection()
    {
        $backup = Log::where('tstamp','>=',$this->date_from.' 00:00:00')->where('tstamp', '<=', $this->date_to . ' 23:59:59')->get();
        return $backup;
    }
    public function headings() : array
    {
        return ["id","tstamp", "ph", "tss","amonia","cod","flow_meter","controller_name"];
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';'
        ];
    }
}

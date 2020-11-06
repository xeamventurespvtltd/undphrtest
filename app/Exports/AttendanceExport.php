<?php

namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class AttendanceExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStrictNullComparison
{
    protected $punches;
	 protected $headerArray;

    public function __construct($punches,$headerArray)
    {
        $this->punches = $punches;
		$this->headerArray = $headerArray;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->punches;
    }

    public function headings(): array
    {
       
	   return $this->headerArray;
	   /*  return [
            '#',
            'Employee Name',
            'Employee Code',
			'Designation',
			'2020-03-26',
			'2020-03-27',
			'2020-03-28',
			'2020-03-29',
			'2020-03-30',
			'2020-03-31',
			'2020-04-01',
			'2020-04-02',
			'2020-04-03',
			'2020-04-04',
			'2020-04-05',
			'2020-04-06',
			'2020-04-07',
			'2020-04-08',
			'2020-04-09',
			'2020-04-10',
			'2020-04-11',
			'2020-04-12',
			'2020-04-13',
			'2020-04-14',
			'2020-04-15',
			'2020-04-16',
			'2020-04-17',
			'2020-04-18',
			'2020-04-19',
			'2020-04-20',
			'2020-04-21',
			'2020-04-22',
			'2020-04-23',
			'2020-04-24',
			'2020-04-25'			
        ]; */
    }
}

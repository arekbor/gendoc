<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\EmployeeSettlement;
use App\Model\EmployeeSettlementRow;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpKernel\KernelInterface;

final class EmployeeSettlementService
{
    private const START_ROW_POS = 4;

    public function __construct(
        private readonly KernelInterface $kernel
    ) {}

    public function generateExcel(EmployeeSettlement $employeeSettlement): \SplFileInfo
    {
        $temp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Formatka.xlsx';

        $filename = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . '/assets/template.xlsx';

        $spreadsheet = IOFactory::load($filename);
        $worksheet = $spreadsheet->getActiveSheet();

        $month = $employeeSettlement->getMonth();
        $year = $employeeSettlement->getYear();

        $daysInMonth = $this->getDays($month, $year);

        $rows = $employeeSettlement->getEmployeeSettlementRows();

        foreach ($daysInMonth as $key => $day) {
            $worksheet->setCellValueExplicit([1, self::START_ROW_POS + $key], $key + 1 . ".", DataType::TYPE_STRING);

            $dateValue = date('d', strtotime("$day-$month-$year")) . "-" . $this->intToRomanNumeral($month);

            $worksheet->setCellValueExplicit([2, self::START_ROW_POS + $key], $dateValue, DataType::TYPE_STRING);
            $worksheet->setCellValueExplicit([3, self::START_ROW_POS + $key], strtolower($this->getDayName($day, $month, $year)) . ".", DataType::TYPE_STRING);

            $employeeSettlementRow = $this->getEmployeeSettlementRowByDay($day, $rows);
            if ($employeeSettlementRow) {
                $startTimestamp = $employeeSettlementRow->getStartTime()->getTimestamp();

                $worksheet->setCellValue([4, self::START_ROW_POS + $key], date('H', $startTimestamp));
                $worksheet->setCellValue([5, self::START_ROW_POS + $key], (int)date('i', $startTimestamp));

                $endTimestamp = $employeeSettlementRow->getEndTime()->getTimestamp();

                $worksheet->setCellValue([6, self::START_ROW_POS + $key], date('H', $endTimestamp));

                $diff = $employeeSettlementRow->getStartTime()->diff($employeeSettlementRow->getEndTime());
                $worksheet->setCellValueExplicit([8, self::START_ROW_POS + $key], $diff->h . "." . $diff->i, DataType::TYPE_STRING);

                $worksheet->setCellValue([7, self::START_ROW_POS + $key], date('i', $endTimestamp));

                $worksheet->setCellValue([9, self::START_ROW_POS + $key], $employeeSettlementRow->getPlace());
                $worksheet->setCellValue([10, self::START_ROW_POS + $key], $employeeSettlementRow->getActivities());
                $worksheet->setCellValue([11, self::START_ROW_POS + $key], $employeeSettlementRow->getComment());
            } else {
                $worksheet->setCellValue([4, self::START_ROW_POS + $key], 0);
                $worksheet->setCellValue([5, self::START_ROW_POS + $key], 0);
                $worksheet->setCellValue([6, self::START_ROW_POS + $key], 0);
                $worksheet->setCellValue([7, self::START_ROW_POS + $key], 0);
                $worksheet->setCellValue([8, self::START_ROW_POS + $key], 0);
            }
        }

        $maxRowSize = count($daysInMonth) + 3;

        $worksheet->getStyle("A4:A$maxRowSize")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ]
        ]);

        $worksheet->getStyle("H4:H$maxRowSize")->getNumberFormat()->setFormatCode('#,##0.00');

        $worksheet->getStyle("B4:K$maxRowSize")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ]
        ]);

        $style = $worksheet->getStyle("A4:K$maxRowSize");

        $style->applyFromArray([
            'font' => [
                'size' => 8,
                'name' => 'Arial'
            ]
        ]);

        $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $writer = new Xlsx($spreadsheet);
        $writer->save($temp);

        return new \SplFileInfo($temp);
    }

    /**
     * @param EmployeeSettlementRow[] $rows
     */
    public function getEmployeeSettlementRowByDay(int $day, array $rows): ?EmployeeSettlementRow
    {
        $filtered = array_filter($rows, fn($row) => $row->getDay() === $day);
        return reset($filtered) ?: null;
    }

    public function getDays(int $month, int $year): array
    {
        $values = [];
        foreach (range(1, cal_days_in_month(CAL_GREGORIAN, $month, $year)) as $key => $day) {
            $values[$key] = $day;
        }

        return $values;
    }

    public function getDayName(int $day, int $month, int $year): string
    {
        $trans = [
            'Mon' => 'Pon',
            'Tue' => 'Wt',
            'Wed' => 'Śr',
            'Thu' => 'Czw',
            'Fri' => 'Pt',
            'Sat' => 'Sob',
            'Sun' => 'Nie'
        ];

        $dayName = date('D', strtotime("$day-$month-$year"));

        return $trans[$dayName];
    }

    public function getMonthName(int $month): string
    {
        $trans = [
            'January' => 'Styczeń ❄️',
            'February' => 'Luty 💘',
            'March' => 'Marzec 🌱',
            'April' => 'Kwiecień 🌧️',
            'May' => 'Maj 🌸',
            'June' => 'Czerwiec 🌞',
            'July' => 'Lipiec 🏖️',
            'August' => 'Sierpień 🍉',
            'September' => 'Wrzesień 🍂',
            'October' => 'Październik 🎃',
            'November' => 'Listopad 🍁',
            'December' => 'Grudzień 🎄',
        ];

        $englishMonth = date('F', mktime(0, 0, 0, $month));
        return $trans[$englishMonth] ?? $englishMonth;
    }

    private function intToRomanNumeral(int $num): string|false
    {
        static $nf = new \MessageFormatter('@numbers=roman', '{0, number}');
        return $nf->format([$num]);
    }
}

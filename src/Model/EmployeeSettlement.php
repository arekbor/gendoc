<?php

declare(strict_types=1);

namespace App\Model;

final class EmployeeSettlement
{
    /**
     * @var EmployeeSettlementRow[] $employeeSettlementRows
     */
    private array $employeeSettlementRows;

    private int $month;
    private int $year;

    public function getMonth(): int
    {
        return $this->month;
    }

    public function setMonth(int $month): static
    {
        $this->month = $month;

        return $this;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @return EmployeeSettlementRow[]
     */
    public function getEmployeeSettlementRows(): array
    {
        return $this->employeeSettlementRows;
    }

    /**
     * @var EmployeeSettlementRow[] $employeeSettlementRows
     */
    public function setEmployeeSettlementRows(array $employeeSettlementRows): void
    {
        $this->employeeSettlementRows = $employeeSettlementRows;
    }
}

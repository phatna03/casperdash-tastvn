<?php

namespace App\Excel;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class ImportData implements ToModel, WithCalculatedFormulas
{
    use Importable;

    public function model(array $row)
    {
        return $row;
    }
}

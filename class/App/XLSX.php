<?php

namespace App;

use PHP\Util\Lists;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class XLSX
{
    private $source;
    private $filename = "export.xlsx";
    private $columns = null;
    public function __construct($source)
    {
        $this->source = $source;
        $this->columns = new Lists([]);
    }

    public function setFilename(string $filename)
    {
        $this->filename = $filename;
    }

    public function add(string $label, $getter)
    {
        $this->columns->add([
            "label" => $label,
            "getter" => $getter
        ]);
    }

    public function getSpreadsheet(): Spreadsheet
    {
        // Create new Spreadsheet object
        $ss = new Spreadsheet();
        $sheet = $ss->setActiveSheetIndex(0);

        $sheet->fromArray($this->columns->column("label")->all());
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);
        $sheet->freezePane("A2");
        $sheet->setAutoFilter('A1:'.$sheet->getHighestColumn()."1");
        
        $i = 2;
        foreach ($this->source as $data) {

            $row = [];
            foreach ($this->columns->column("getter") as $getter) {
                if (is_callable($getter)) {
                    $row[] = $getter($data);
                } else {
                    $row[] = var_get($data, $getter);
                }
            }

            $sheet->fromArray($row, null, "A$i");
            $i++;
        }

        for ($i = 'A'; $i !=  $sheet->getHighestColumn(); $i++) {
            $sheet->getColumnDimension($i)->setAutoSize(true);
        }
        return $ss;
    }

    public function render()
    {
        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $this->filename . '"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0 

        $writer = IOFactory::createWriter($this->getSpreadsheet(), 'Xlsx');
        $writer->save('php://output');
        die();
    }
}

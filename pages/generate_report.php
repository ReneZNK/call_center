<?php

require 'C:\ospanel\domains\call\vendor\autoload.php';
require_once '../includes/db.php'; // Замените 'path/to/db.php' на правильный путь к вашему файлу db.php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Получение данных из базы данных
$sql = "
    SELECT 
        u.first_name, 
        u.last_name, 
        s.start_time AS shift_start, 
        s.end_time AS shift_end, 
        b.start_time AS break_start, 
        b.end_time AS break_end, 
        b.break_type
    FROM 
        shifts s
    LEFT JOIN 
        breaks b ON s.user_id = b.user_id AND b.start_time 
    JOIN 
        users u ON s.user_id = u.id
    ORDER BY 
        u.id, s.start_time, b.start_time
";
//BETWEEN s.start_time AND s.end_time
$stmt = $db->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Отладочная информация
echo "<pre>";
print_r($data);
echo "</pre>";

// Создание нового файла Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Заголовки столбцов
$sheet->setCellValue('A1', 'Юзер');
$sheet->setCellValue('B1', 'Время начала смены');
$sheet->setCellValue('C1', 'Время конца смены');
$sheet->setCellValue('D1', 'Тип перерыва');
$sheet->setCellValue('E1', 'Время начала перерыва');
$sheet->setCellValue('F1', 'Время конца перерыва');

// Заполнение данными
$row = 2;
foreach ($data as $row_data) {
    $userName = $row_data['first_name'] . ' ' . $row_data['last_name'];
    $shiftStart = $row_data['shift_start'] ? date('Y-m-d H:i:s', strtotime($row_data['shift_start'])) : '';
    $shiftEnd = $row_data['shift_end'] ? date('Y-m-d H:i:s', strtotime($row_data['shift_end'])) : '';
    $breakType = $row_data['break_type'] ?? '';
    $breakStart = $row_data['break_start'] ? date('Y-m-d H:i:s', strtotime($row_data['break_start'])) : '';
    $breakEnd = $row_data['break_end'] ? date('Y-m-d H:i:s', strtotime($row_data['break_end'])) : '';

    $sheet->setCellValue('A' . $row, $userName);
    $sheet->setCellValue('B' . $row, $shiftStart);
    $sheet->setCellValue('C' . $row, $shiftEnd);
    $sheet->setCellValue('D' . $row, $breakType);
    $sheet->setCellValue('E' . $row, $breakStart);
    $sheet->setCellValue('F' . $row, $breakEnd);

    $row++;
}

// Автоматическое изменение ширины столбцов
foreach (range('A', 'F') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Создание writer для Excel файла
$writer = new Xlsx($spreadsheet);

// Очистка буфера вывода
ob_end_clean();

// Установка заголовков для скачивания файла
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="report_' . date('Ymd_His') . '.xlsx"');
header('Cache-Control: max-age=0');

// Вывод файла напрямую в браузер
$writer->save('php://output');

exit;
?>
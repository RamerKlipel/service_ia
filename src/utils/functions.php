<?php
function printr(mixed $mix): void
{
    echo '<pre>';
    print_r($mix);
    echo '</pre>';
}

function formatDateDB(string $date): null|string
{
    if (empty($date)) {
        return null;
    }

    [$day, $month, $year] = explode('/', $date);
    $blValidDay = $day >= 0 && $day <= 31;
    $blValidMonth = $month >= 1 && $month <= 12;
    $blValidYear = $year >= 1500 && $year <= 9999;

    if ($blValidDay && $blValidMonth && $blValidYear) {
        $date = "$year-$month-$day";
    }
    return $date;
}

function formatDateUser(string $date): null|string
{
    if (empty($date)) {
        return null;
    }

    [$year, $month, $day] = explode('-', $date);
    $day = substr($day, 0, 2);

    $blValidDay = $day >= 0 && $day <= 31;
    $blValidMonth = $month >= 1 && $month <= 12;
    $blValidYear = $year >= 1500 && $year <= 9999;

    if ($blValidDay && $blValidMonth && $blValidYear) {
        $date = "$day/$month/$year";
    }
    return $date;
}

function formatNumberBD(mixed $number): null|string
{
    if (empty($number)) {
        return null;
    }

    $number = str_replace('.', '', $number);
    $number = str_replace(',', '.', $number);

    return $number;
}

function formatNumberUser(mixed $number): null|string
{
    if (empty($number)) {
        return null;
    }

    $number = number_format($number, 2, ',', '.');

    return $number;
}

function formatDateHourUser(string $dateHour): null|string
{
    if (empty($dateHour)) {
        return null;
    }

    $date = substr($dateHour, 0, 10);
    $hour = substr($dateHour, 11);

    $dateFormated = formatDateUser($date);
    return "$dateFormated $hour";
}

function formatDateHourDB(string $dateHour): null|string
{
    if (empty($dateHour)) {
        return null;
    }

    $date = substr($dateHour, 0, 10);
    $hour = substr($dateHour, 11);

    $dateFormated = formatDateDB($date);
    return "$dateFormated $hour";
}

function jsonResponse(array $arrData, int $idStatusCode = 200): void
{
    http_response_code($idStatusCode);
    header('Content-Type: application/json');
    echo json_encode($arrData);
}

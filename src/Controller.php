<?php

namespace App;

use GuzzleHttp\Client;

abstract class Controller
{
    protected function getJsonInput(): array
    {
        $arrInput = json_decode(file_get_contents('php://input'), true);
        return is_array($arrInput) ? $arrInput : [];
    }

    protected function requireFields(array $arrInput, array $arrFields): void
    {
        $arrErroField = [];
        foreach ($arrFields as $field) {
            if (empty($arrInput[$field])) {
                $arrErroField[] = $field;
            }
        }

        if ($arrErroField || empty($arrFields)) {
            $strErrorFields = !empty($arrErroField) ? implode(', ', $arrErroField) : implode(', ', $arrFields);
            throw new \Exception("Os seguintes campos são obrigatórios $strErrorFields", 400);
        }
    }
}

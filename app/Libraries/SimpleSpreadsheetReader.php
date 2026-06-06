<?php

namespace App\Libraries;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class SimpleSpreadsheetReader
{
    public function read(string $path): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'csv' => $this->readCsv($path),
            'xlsx' => $this->readXlsx($path),
            default => throw new RuntimeException('Unsupported file type. Upload a CSV or XLSX file.'),
        };
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Unable to open CSV file.');
        }

        $rows = [];

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = array_map(static fn($value): string => trim((string) $value), $row);
            }
        } finally {
            fclose($handle);
        }

        return $rows;
    }

    private function readXlsx(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZIP support is required to import XLSX files.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Unable to open XLSX file.');
        }

        try {
            $sharedStrings = $this->readSharedStrings($zip);
            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');

            if ($sheetXml === false) {
                throw new RuntimeException('The XLSX file does not contain the first worksheet.');
            }

            return $this->extractRowsFromSheet($sheetXml, $sharedStrings);
        } finally {
            $zip->close();
        }
    }

    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $document = simplexml_load_string($xml);
        if (! $document instanceof SimpleXMLElement) {
            return [];
        }

        $strings = [];

        foreach ($document->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;
                continue;
            }

            $value = '';
            foreach ($item->r as $run) {
                $value .= (string) $run->t;
            }
            $strings[] = $value;
        }

        return $strings;
    }

    private function extractRowsFromSheet(string $sheetXml, array $sharedStrings): array
    {
        $document = simplexml_load_string($sheetXml);
        if (! $document instanceof SimpleXMLElement) {
            throw new RuntimeException('Unable to parse XLSX worksheet.');
        }

        $document->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rowNodes = $document->xpath('//a:sheetData/a:row') ?: [];

        $rows = [];
        $maxColumnIndex = 0;

        foreach ($rowNodes as $rowNode) {
            $rowData = [];

            foreach ($rowNode->c as $cell) {
                $reference = (string) $cell['r'];
                $columnIndex = $this->columnReferenceToIndex(preg_replace('/\d+/', '', $reference));
                $maxColumnIndex = max($maxColumnIndex, $columnIndex);
                $rowData[$columnIndex] = $this->cellValue($cell, $sharedStrings);
            }

            $rows[] = $rowData;
        }

        $normalized = [];
        foreach ($rows as $row) {
            $normalizedRow = [];
            for ($index = 0; $index <= $maxColumnIndex; $index++) {
                $normalizedRow[] = trim((string) ($row[$index] ?? ''));
            }
            $normalized[] = $normalizedRow;
        }

        return $normalized;
    }

    private function cellValue(SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];
        $value = isset($cell->v) ? (string) $cell->v : '';

        if ($type === 's') {
            $index = (int) $value;
            return $sharedStrings[$index] ?? '';
        }

        if ($type === 'inlineStr' && isset($cell->is->t)) {
            return (string) $cell->is->t;
        }

        return $value;
    }

    private function columnReferenceToIndex(string $reference): int
    {
        $index = 0;

        foreach (str_split($reference) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }
}

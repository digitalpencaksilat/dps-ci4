<?php

namespace App\Services;

use RuntimeException;
use setasign\Fpdi\Fpdi;

class PdfMergeService
{
    /**
     * Merge multiple PDF files into one
     * 
     * @param array $filePaths Array of PDF file paths to merge
     * @return string Path to merged PDF file
     * @throws \Exception
     */
    public function mergePdfFiles(array $filePaths): string
    {
        if (empty($filePaths)) {
            throw new \Exception('No PDF files to merge');
        }

        if (!class_exists('FPDF')) {
            throw new RuntimeException('Library FPDF belum terpasang. Install dependency FPDF/FPDI terlebih dahulu agar fitur merge PDF bisa digunakan.');
        }

        $pdf = new Fpdi();
        
        foreach ($filePaths as $file) {
            if (!file_exists($file)) {
                throw new \Exception('File not found: ' . $file);
            }

            try {
                $pageCount = $pdf->setSourceFile($file);
                for ($i = 1; $i <= $pageCount; $i++) {
                    $templateId = $pdf->importPage($i);
                    $size = $pdf->getTemplateSize($templateId);
                    
                    $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);
                }
            } catch (\Exception $e) {
                throw new \Exception('Error processing file ' . $file . ': ' . $e->getMessage());
            }
        }

        // Save merged PDF to writable directory
        $writable_path = WRITEPATH . 'uploads/merged-pdf/';
        if (!is_dir($writable_path)) {
            mkdir($writable_path, 0755, true);
        }

        $merged_filename = 'merged_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
        $output_path = $writable_path . $merged_filename;

        $pdf->Output($output_path, 'F');

        if (!file_exists($output_path)) {
            throw new \Exception('Failed to create merged PDF file');
        }

        return $output_path;
    }
}

<?php

namespace Bank\Parser;


use Bank\Activity;
use Bank\Transaction;
use FilesystemIterator;
use PHPExcel_IOFactory;

class Ing implements \Bank\Parser
{
    /**
     * @param $path string
     * @return Activity
     */
    public function parseTransactions($path)
    {
         $callback = function(\SplFileInfo $file){
            return $file->getExtension() == 'xls';
         };

        $activity = [];
        foreach (new \CallbackFilterIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)), $callback) as $fileInfo) {
            $activity = array_merge(
                $activity,
                $this->getActivity(PHPExcel_IOFactory::load($fileInfo->getPathname()))
            );
        }

        return new Activity($activity);
    }

    private function getActivity(\PHPExcel $excel)
    {
        $excel->getActiveSheet()->getStyle()->getNumberFormat()->setFormatCode('#.###,##');
        return array_map(function ($line) {
            return new Transaction(
                ($line[5] <0) ? Transaction::EXPENSE : Transaction::REVENUE,
                \DateTime::createFromFormat('d/m/Y', $line[0]),
                $line[1],
                str_replace(',', '', abs($line[5]))
            );
        }, $excel->getActiveSheet()->removeRow(1, 4)->toArray());
    }
}
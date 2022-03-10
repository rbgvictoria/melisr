<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('str_putcsv')) {
    /**
     * function str_putcsv
     * 
     * Converts multidimensional array into CSV with header row
     * 
     * @param type $data
     * @return type string
     */
    function str_putcsv($data)
    {
        # Generate CSV data from array
        $fh = fopen('php://temp', 'rw'); # don't create a file, attempt
                                         # to use memory instead

        # write out the headers
        fputcsv($fh, array_keys(current($data)));

        # write out the data
        foreach ( $data as $row ) {
                fputcsv($fh, $row);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $csv;
    }
}
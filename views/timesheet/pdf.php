
<?php
    $file = $path;
    $filename = 'Custom file name for the.pdf'; /* Note: Always use .pdf at the end. */

    header('Content-type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($file));
    header('Accept-Ranges: bytes');

    @readfile($file);
?>

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


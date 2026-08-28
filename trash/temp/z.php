<?php




    $zip = new ZipArchive();
    $zip->open('example.zip',  ZipArchive::CREATE);
    $srcDir = "/home/arabyos4/public_html/arabyos.com/";
    $files= scandir($srcDir);
    //var_dump($files);
    unset($files[0],$files[1]);
    foreach ($files as $file) {
        $zip->addFile("{$file}");    
    }
    $zip->close();
	
?>
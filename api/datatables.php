<?php
function directoryToArray($directory, $recursive) {
        $array_items = array();
        if ($handle = opendir($directory)) {
                while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                                if (is_dir($directory. "/" . $file)) {
                                        if($recursive) {
                                                $array_items = array_merge($array_items, directoryToArray($directory. "/" . $file, $recursive));
                                        }
                                        ;
                                } else {
                                        $file = $directory . "/" . $file;
                                        $array_items[] = preg_replace("/\/\//si", "/", $file);
                                }
                        }
                }
                closedir($handle);
        }
        return $array_items;
}

$files = directoryToArray("../opentable",true);
$json = json_encode($files);
$json = str_replace('\\/', '/', $json);
echo $json;
?>

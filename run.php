<?php

$basePath = 'C:\Program Files (x86)\World of Warcraft\_classic_';
$files = [
    'RXPGuides'
];
$s = DIRECTORY_SEPARATOR;

//
foreach ($files as $file) {
    try {
        backup($basePath, $file);
    } catch (Exception $e) {
        var_dump($e->getTraceAsString(), $e->getMessage());
    }
}


/**
 * 备份文件
 * 保存最近三个版本
 * @return void
 * @throws Exception
 */
function backup($basePath, $file)
{
    if (is_dir('"' . $basePath . '"')) {
        throw new Exception('基础目录不存在，path：' . $basePath);
    }
    $s = DIRECTORY_SEPARATOR;
    $target = $basePath . $s . 'addons-backup' . $s . $file;
    if (!is_dir($target)) {
        mkdir($target, 755, true);
    }
    $from = $basePath . $s . 'Interface' . $s . 'AddOns' . $s . $file;
    if (!is_dir($from)) {
        throw new Exception('不存在要备份的目录：' . $file);
    }
    $target = $target . $s . $file;
    $v1 = $target . '-v1';
    $v2 = $target . '-v2';

    $backup = !is_dir($target);
    if (!$backup) {
        $versionFrom = getVersion($from . $s . $file . '.toc');
        $versionTo = getVersion($target . $s . $file . '.toc');
        if ($versionFrom == $versionTo) {
            $backup = false;
        }
    }
    if (!$backup) {
        return;
    }
    // 如果存在 v2，则删掉
    if (is_dir($v2)) {
        delDir($v2);
    }
    // 如果存在v1，则改名称为v2
    if (is_dir($v1)) {
        rename($v1, $v2);
    }
    // 如果存在，则改名称为v1
    if (is_dir($target)) {
        rename($target, $v1);
    }
    if (!is_dir($target)) {
        recurse_copy($from, $target);
    }
}


function recurse_copy($src, $dst)
{
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function delDir($dir)
{
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
        if ($file != "." && $file != "..") {
            $fullPath = $dir . "/" . $file;
            if (!is_dir($fullPath)) {
                unlink($fullPath);
            } else {
                delDir($fullPath);
            }
        }
    }
    closedir($dh);
    return rmdir($dir);
}

function getVersion($file)
{
    if (!is_file($file)) {
        throw new Exception('找不到文件');
    }
    $handler = fopen($file, 'r');
    while (!feof($handler)) {
        $row = fgets($handler);
        if (strpos($row, '## Version:') !== false) {
            return trim(substr($row, 11));
        }
    }
    throw new Exception('找不到版本号');
}
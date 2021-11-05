<?php
require __DIR__ . '/../bootstrap.php';
$config = include __DIR__ . '/config/config.php';
$request = $argv[1] ?? '';
$info = 'ERROR';
switch ($request) {
    case 'html_dir' :
        $info = HTML_DIR;
        break;
    case 'backup_dir' :
        $info = $config['SUPER']['backup_dir'] ?? '';
        break;
    case 'db_name' :
        $info = $config['STORAGE']['db_name'] ?? '';
        break;
    case 'db_cmd' :
        $name = $config['STORAGE']['db_name'] ?? '';
        $user = $config['STORAGE']['db_user'] ?? '';
        $pwd  = $config['STORAGE']['db_pwd'] ?? '';
        $cmd  = $config['STORAGE']['db_cmd'] ?? '';
        $info = str_replace(['%%REPL_DB_USER%%','%%REPL_DB_PWD%%','%%REPL_DB_NAME%%'],
                            [$user, $pwd, $name],
                            $cmd);
        break;
    case 'db_backup_enabled' :
        $info = $config['STORAGE']['db_backup_enabled'] ?? '';
        break;
    default :
        $info = '';
}
echo $info;

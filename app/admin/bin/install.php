<?php
use Hook\Db\{PdoConnect, OrmConnect};
use Hook\Sql\{Install};

require __DIR__.'/../init.php';

$database = APP_CONFIG['mysql']['default']['dbname'];

$pdo = PdoConnect::getInstance();
$pdo->query('DROP DATABASE IF EXISTS `'.$database.'`');
echo "删除数据库 \e[32m 成功 \e[0m\n", PHP_EOL;

$pdo->query('CREATE DATABASE `'.$database.'`');
echo "创建数据库 \e[32m 成功 \e[0m\n", PHP_EOL;

$pdo->query('USE '.$database.';SET FOREIGN_KEY_CHECKS = 0;');
$pdo->query(Install::CREATE_ADMIN_STRUCT);
$pdo->query(Install::CREATE_ADMIN_DATA);

echo "初始化\e[32m ".APP_NAME." \e[0m平台数据完毕\n", PHP_EOL;

$app->execute('main', $pdo);

function main($pdo) {
    $data = '';
    foreach ($pdo->fetchAll(\Hook\Sql\Table::GET_ALL, ['hp_'.APP_NAME.'_%'], PDO::FETCH_NUM) as list($tableName)) {
        $orm = OrmConnect::getInstance($tableName);
        $orm->synData();
        $data .= '['.$tableName.']'.PHP_EOL;
        foreach ($pdo->fetchAll('DESC `'.$tableName.'`') as $field) {
            $data .= $field['Field'].'.type='.substr($field['Type'], 0, strpos($field['Type'], '(')).PHP_EOL;
            $data .= $field['Field'].'.null='.$field['Null'].PHP_EOL;
            $data .= $field['Field'].'.key='.$field['Key'].PHP_EOL;
            $data .= $field['Field'].'.default='.$field['Default'].PHP_EOL;
            $data .= $field['Field'].'.extra='.$field['Extra'].PHP_EOL;
            $validate = $orm->validate($field['Type']);
            $data .= $field['Field'].'.min='.$validate['min'].PHP_EOL;
            $data .= $field['Field'].'.max='.$validate['max'].PHP_EOL;
        }
        $data .= PHP_EOL;
    }
    file_put_contents(getcwd().'/conf/'.APP_NAME.'_table.ini', $data);
}
shell_exec('sudo service php7.3-fpm restart');
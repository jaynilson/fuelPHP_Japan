<?php
namespace Acl;
use Hook\Db\PdoConnect;
use Hook\Sql\Acl;

class GroupModel extends \AbstractModel
{
    public $table = 'hp_acl_group';
    public $foreign = 'group_id';

    public function __construct()
    {
        $this->validate = [];
        parent::__construct();
    }

    public function read(int $id = 0, int $langId = 0): array
    {
        return PdoConnect::getInstance()->fetchAll(Acl::GET_GROUP, [$_SESSION[APP_NAME]['lang_id'], 1]);
    }

    public function create(): int
    {
        return parent::create();
    }

    public function update(int $id): bool
    {
        return parent::update($id);
    }

    public function delete(int $id): int
    {
        return parent::delete($id);
    }
}
<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class Group.
 */
class Group extends Element
{
    /**
     * @throws \Exception
     *
     * @return array
     */
    public function getUsers(): array
    {
        $cube_user_group = $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_USER_GROUP')
        ;

        $user_groups = $cube_user_group->arrayExport([
            'area' => $cube_user_group->createArea(['#_GROUP_' => [$this->getName()]]),
        ], false);

        $ret_users = \array_map(static function ($v) {
            return $v[0];
        }, $user_groups);

        return $ret_users;
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function getRoles(): array
    {
        $cube_group_role = $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_GROUP_ROLE')
        ;

        $group_roles = $cube_group_role->arrayExport([
            'area' => $cube_group_role->createArea(['#_GROUP_' => [$this->getName()]]),
        ], false);

        $ret_roles = \array_map(static function ($v) {
            return $v[1];
        }, $group_roles);

        return $ret_roles;
    }
}

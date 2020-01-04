<?php

declare(strict_types=1);

namespace Xodej\Olapi;

use Xodej\Olapi\ApiRequestParams\ApiCellExportParams;

/**
 * Class Group.
 */
class Group extends Element
{
    /**
     * Returns array of user names attached to the group.
     *
     * @throws \Exception
     *
     * @return string[]
     */
    public function getUsers(): array
    {
        $cube_user_group = $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_USER_GROUP')
        ;

        $params = new ApiCellExportParams();
        $params->area = $cube_user_group->createArea(['#_GROUP_' => [$this->getName()]]);

        $user_groups = $cube_user_group->arrayExport($params, false);

        return \array_map(
            static function (array $v) {
                return $v[0];
            },
            $user_groups
        );
    }

    /**
     * Returns an array of roles assigned to the group.
     *
     * @throws \Exception
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        $cube_group_role = $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_GROUP_ROLE')
        ;

        $params = new ApiCellExportParams();
        $params->area = $cube_group_role->createArea(['#_GROUP_' => [$this->getName()]]);

        $group_roles = $cube_group_role->arrayExport($params, false);

        return \array_map(
            static function (array $v) {
                return $v[1];
            },
            $group_roles
        );
    }

    /**
     * Sets users for the group and removes existing users from the group.
     *
     * @param string[] $user_names array of user names
     *
     * @return bool
     */
    public function setUser(array $user_names): bool
    {
        // @todo Group::setUser()
        return false;
    }

    /**
     * Adds a user to the group // adds this group to a user.
     *
     * @param string $user_name user name
     *
     * @return bool
     */
    public function addUser(string $user_name): bool
    {
        // @todo Group::addUser()
        return false;
    }

    /**
     * Returns true/false if group is assigned to a given user.
     *
     * @param string $user_name user name
     *
     * @return bool
     */
    public function hasUser(string $user_name): bool
    {
        // @todo Group::hasUser()
        return false;
    }

    /**
     * Removes group from user // removes user from group.
     *
     * @param string $user_name user name
     *
     * @return bool
     */
    public function removeUser(string $user_name): bool
    {
        // @todo Group::removeUser()
        return false;
    }

    /**
     * Removes all users from group // removes group from all users.
     *
     * @return bool
     */
    public function clearUsers(): bool
    {
        // @todo Group::clearUsers()
        return false;
    }

    /**
     * Sets roles for a group.
     *
     * @param string[] $role_name array of role names
     *
     * @return bool
     */
    public function setRole(array $role_name): bool
    {
        // @todo Group::setRole()
        return false;
    }

    /**
     * Adds a role to the group // adds this group to a role.
     *
     * @param string $role_name role name
     *
     * @return bool
     */
    public function addRole(string $role_name): bool
    {
        // @todo Group::addRole()
        return false;
    }

    /**
     * Returns true/false if group is assigned to a given role.
     *
     * @param string $role_name role name
     *
     * @return bool
     */
    public function hasRole(string $role_name): bool
    {
        // @todo Group::hasRole()
        return false;
    }

    /**
     * Removes role from group // removes group from role.
     *
     * @param string $role_name role name
     *
     * @return bool
     */
    public function removeRole(string $role_name): bool
    {
        // @todo Group::removeRole()
        return false;
    }

    /**
     * Removes all roles from group // removes group from all roles.
     *
     * @return bool
     */
    public function clearRoles(): bool
    {
        // @todo Group::clearRoles()
        return false;
    }

    /**
     * Sets description for group.
     *
     * @param string $description Description text
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function setDescription(string $description): bool
    {
        return $this->getConnection()
            ->getSystemDatabase()
            ->getCube('#_GROUP_GROUP_PROPERTIES')
            ->setValue($description, [$this->getName(), 'description'])
        ;
    }

    /**
     * @param null|bool $invert default false
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function setInactive(?bool $invert = null): bool
    {
        $invert = $invert ?? false;
        $value = $invert ? null : 1;

        return $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_GROUP_GROUP_PROPERTIES')
            ->setValue($value, [$this->getName(), 'inactive'])
        ;
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function setActive(): bool
    {
        return $this->setInactive(true);
    }
}

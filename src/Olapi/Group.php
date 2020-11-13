<?php

declare(strict_types=1);

namespace Xodej\Olapi;

use Xodej\Olapi\ApiRequestParams\ApiCellExport;

/**
 * Class Group.
 */
class Group extends Element
{
    /**
     * Returns array of user names attached to the group.
     *
     * @param null|bool $include_technical_user
     *
     * @throws \Exception
     *
     * @return string[]
     */
    public function getUsers(?bool $include_technical_user = null): array
    {
        $include_technical_user ??= false;

        $cube_user_group = $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_USER_GROUP')
        ;

        $request = new ApiCellExport();
        $request->area = $cube_user_group->createArea(['#_GROUP_' => [$this->getName()]]);

        $user_groups = $cube_user_group->arrayExport($request, false);

        $user_names = \array_map(
            static function (array $v) {
                return $v[0];
            },
            $user_groups
        );

        // return all users including technical ones
        if ($include_technical_user) {
            return $user_names;
        }

        // return only non-technical user names
        $technical_user_names = [
            'admin' => true,
            'etl' => true,
            '_internal_suite' => true,
        ];

        return \array_filter($user_names, static function ($v) use ($technical_user_names): bool {
            return !isset($technical_user_names[$v]);
        });
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

        $request = new ApiCellExport();
        $request->area = $cube_group_role->createArea(['#_GROUP_' => [$this->getName()]]);

        $group_roles = $cube_group_role->arrayExport($request, false);

        return \array_map(
            static function (array $v) {
                return $v[1]; // @todo check if $v[0] instead of $v[1]
            },
            $group_roles
        );
    }

    /**
     * Sets users for the group and removes existing users from the group.
     *
     * @param string[] $user_names array of user names
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function setUser(array $user_names): bool
    {
        // @todo Group::setUser() clearUsers() check for success --> throw Exception on fail?
        $this->clearUsers();

        $valid_users = \array_flip($this->getConnection()->getSystemDatabase()->getUsers());

        $paths = [];
        $values = [];

        foreach ($user_names as $user_name) {
            if (\is_string($user_name)) {
                throw new \InvalidArgumentException('Group::setUser() expects array of strings as parameter.');
            }

            if (!isset($valid_users[$user_name])) {
                throw new \InvalidArgumentException(\sprintf('Group::setUser() received unknown user name %s.', $user_name));
            }

            $values[] = 1;
            $paths[] = [$user_name, $this->getName()];
        }

        // nothing to write to cube
        if (0 === \count($values)) {
            return false;
        }

        return $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_USER_GROUP')
            ->setBulk($values, $paths)
            ;
    }

    /**
     * Adds a user to the group // adds this group to a user.
     *
     * @param string $user_name user name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function addUser(string $user_name): bool
    {
        $user = $this->getConnection()->getSystemDatabase()->getUser($user_name);

        return $user->addGroups([$this->getName()]);
    }

    /**
     * Returns true/false if group is assigned to a given user.
     *
     * @param string $user_name user name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function hasUser(string $user_name): bool
    {
        $user_list = \array_flip($this->getUsers());

        return isset($user_list[$user_name]);
    }

    /**
     * Removes group from user // removes user from group.
     *
     * @param string $user_name user name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function removeUser(string $user_name): bool
    {
        $user = $this->getConnection()->getSystemDatabase()->getUser($user_name);

        return $user->removeGroups([$this->getName()]);
    }

    /**
     * Removes all users from group // removes group from all users.
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function clearUsers(): bool
    {
        $user_names = $this->getUsers();

        $paths = [];
        $values = [];

        foreach ($user_names as $user_name) {
            $values[] = null;
            $paths[] = [$user_name, $this->getName()];
        }

        // nothing to write to cube
        if (0 === \count($values)) {
            return false;
        }

        return $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_USER_GROUP')
            ->setBulk($values, $paths)
            ;
    }

    /**
     * Sets roles for a group.
     *
     * @param string[] $role_names array of role names
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function setRole(array $role_names): bool
    {
        // @todo Group::setRole() clearRoles() check for success --> throw Exception on fail?
        $this->clearRoles();

        $valid_roles = \array_flip($this->getConnection()->getSystemDatabase()->getRoles());

        $paths = [];
        $values = [];

        foreach ($role_names as $role_name) {
            if (\is_string($role_name)) {
                throw new \InvalidArgumentException('Group::setRole() expects array of strings as parameter.');
            }

            if (!isset($valid_roles[$role_name])) {
                throw new \InvalidArgumentException(\sprintf('Group::setRole() received unknown role name %s.', $role_name));
            }

            $values[] = 1;
            $paths[] = [$this->getName(), $role_name];
        }

        // nothing to write to cube
        if (0 === \count($values)) {
            return false;
        }

        return $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_GROUP_ROLE')
            ->setBulk($values, $paths)
            ;
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
     * @throws \Exception
     *
     * @return bool
     */
    public function hasRole(string $role_name): bool
    {
        $role_list = \array_flip($this->getRoles());

        return isset($role_list[$role_name]);
    }

    /**
     * Removes role from group // removes group from role.
     *
     * @param string $role_name role name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function removeRole(string $role_name): bool
    {
        $role = $this->getConnection()->getSystemDatabase()->getRole($role_name);

        return $role->removeGroups([$this->getName()]);
    }

    /**
     * Removes all roles from group // removes group from all roles.
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function clearRoles(): bool
    {
        $role_names = $this->getRoles();

        $paths = [];
        $values = [];

        foreach ($role_names as $role_name) {
            $values[] = null;
            $paths[] = [$this->getName(), $role_name];
        }

        // nothing to write to cube
        if (0 === \count($values)) {
            return false;
        }

        return $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_GROUP_ROLE')
            ->setBulk($values, $paths)
            ;
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

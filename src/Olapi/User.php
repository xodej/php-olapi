<?php

declare(strict_types=1);

namespace Xodej\Olapi;

/**
 * Class User.
 */
class User extends Element
{
    /**
     * @param string $password
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function setPassword(string $password): bool
    {
        return $this->getConnection()
            ->setUserPassword($this->getName(), $password)
        ;
    }

    /**
     * @param string $password
     * @param string $secret   (see config.php --> CFG_SECRET)
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getPasswordHashFromString(string $password, string $secret): string
    {
        $rand_bytes = \random_bytes(16);
        $password_hash = \base64_encode($rand_bytes.
            \openssl_encrypt($password, 'AES-128-CFB8', $secret, OPENSSL_RAW_DATA, $rand_bytes));

        return "\ta\t".$password_hash;
    }

    /**
     * @param string $jdx_b64_hash
     * @param string $secret       (see config.php --> CFG_SECRET)
     * @return string
     * @throws \ErrorException
     */
    public static function getPasswordFromHash(string $jdx_b64_hash, string $secret): string
    {
        $jdx_hash = \base64_decode(\substr($jdx_b64_hash, 3));

        // not a new AES-128-CFB8 hash
        if (\strlen($jdx_hash) < 17) {
            return '';
        }

        $rand_bytes = \substr($jdx_hash, 0, 16);
        $hash = \substr($jdx_hash, 16);

        $return = \openssl_decrypt($hash, 'AES-128-CFB8', $secret, OPENSSL_RAW_DATA, $rand_bytes);

        if (false === $return) {
            throw new \ErrorException('openssl_decrypt() failed in User::getPasswordFromHash()');
        }

        return $return;
    }

    /**
     * @throws \Exception
     *
     * @return string
     *
     * @deprecated
     */
    public function getPasswordHash(): string
    {
        return (string) $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_USER_USER_PROPERTIES')
            ->getValue([$this->getName(), 'password'])
        ;
    }

    /**
     * @throws \Exception
     *
     * @return string[]
     */
    public function getGroups(): array
    {
        $cube_user_group = $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_USER_GROUP')
        ;

        $user_groups = $cube_user_group->arrayExport([
            'area' => $cube_user_group->createArea(['#_USER_' => [$this->getName()]]),
        ], false);

        return \array_map(static function (array $v) {
            return $v[1];
        }, $user_groups);
    }

    /**
     * @param string[] $group_names group names
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function setGroups(array $group_names): bool
    {
        // do not allow to set groups that do not exist
        foreach ($group_names as $group_name) {
            if (!$this->getConnection()->getSystemDatabase()->hasGroup($group_name)) {
                throw new \InvalidArgumentException('given group name '.$group_name.' does not exist');
            }
        }

        $active_groups = $this->getGroups();

        $values = [];
        $paths = [];

        // groups to remove
        $remove_groups = \array_diff($active_groups, $group_names);
        foreach ($remove_groups as $remove_group) {
            $values[] = null;
            $paths[] = [$this->getName(), $remove_group];
        }

        // groups to add
        $new_groups = \array_diff($group_names, $active_groups);
        foreach ($new_groups as $new_group) {
            $values[] = 1;
            $paths[] = [$this->getName(), $new_group];
        }

        return $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_USER_GROUP')
            ->setBulk($values, $paths)
        ;
    }

    /**
     * @param string[] $group_names
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function addGroups(array $group_names): bool
    {
        // do not allow to set groups that do not exist
        foreach ($group_names as $group_name) {
            if (!$this->getConnection()->getSystemDatabase()->hasGroup($group_name)) {
                throw new \InvalidArgumentException('given group name '.$group_name.' does not exist');
            }
        }

        $active_groups = $this->getGroups();

        $values = [];
        $paths = [];

        // groups to add
        $new_groups = \array_diff($group_names, $active_groups);
        foreach ($new_groups as $new_group) {
            $values[] = 1;
            $paths[] = [$this->getName(), $new_group];
        }

        return $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_USER_GROUP')
            ->setBulk($values, $paths)
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
            ->getCubeByName('#_USER_USER_PROPERTIES')
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

    /**
     * @param string $full_name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function setFullName(string $full_name): bool
    {
        return $this->getConnection()
            ->getCube('System/#_USER_USER_PROPERTIES')
            ->setValue($full_name, [$this->getName(), 'fullName'])
        ;
    }

    /**
     * @param string $email
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function setEmail(string $email): bool
    {
        return $this->getConnection()
            ->getCube('System/#_USER_USER_PROPERTIES')
            ->setValue($email, [$this->getName(), 'email'])
        ;
    }

    /**
     * @param string $description
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function setDescription(string $description): bool
    {
        return $this->getConnection()
            ->getCube('System/#_USER_USER_PROPERTIES')
            ->setValue($description, [$this->getName(), 'description'])
        ;
    }

    /**
     * @param null|string[] $group_names
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function removeGroups(array $group_names = null): bool
    {
        if (null === $group_names) {
            $group_names = $this->getGroups();
        }

        $active_groups = $this->getGroups();

        $remove_groups = \array_intersect($group_names, $active_groups);

        $values = [];
        $paths = [];
        foreach ($remove_groups as $remove_group) {
            $values[] = null;
            $paths[] = [$this->getName(), $remove_group];
        }

        return $this->getConnection()
            ->getSystemDatabase()
            ->getCubeByName('#_USER_GROUP')
            ->setBulk($values, $paths)
        ;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: Bojidar
 * Date: 8/6/2020
 * Time: 3:46 PM
 */

namespace MicroweberPackages\Role\Repositories;


class Permission
{
    /**
     * Get the permissions from all the enabled modules.
     *
     * @return array
     */
    public static function all()
    {
        //return static::getEnabledModulePermissions() + static::getActiveThemePermissions();
        return static::getEnabledModulePermissions();
    }

    /*
     *         $adminPermissions =  [
            'admin.users' => [
                'index' => 'user::permissions.users.index',
                'create' => 'user::permissions.users.create',
                'edit' => 'user::permissions.users.edit',
                'destroy' => 'user::permissions.users.destroy',
            ],
            'admin.roles' => [
                'index' => 'user::permissions.roles.index',
                'create' => 'user::permissions.roles.create',
                'edit' => 'user::permissions.roles.edit',
                'destroy' => 'user::permissions.roles.destroy',
            ],
        ];
     */

    /**
     * Get enabled module permissions.
     *
     * @return array
     */
    private static function getEnabledModulePermissions()
    {
        $groups = [];
        $modules = get_modules();

        foreach ($modules as $module) {
            if (!isset($module['categories']) || empty($module['categories'])) {
                $module['categories'] = 'admin';
            }

            $groups[$module['categories']][] = $module;
        }


        return $groups;
    }
}
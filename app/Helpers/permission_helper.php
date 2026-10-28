<?php

if (!function_exists('current_role')) {
    function current_role(): string
    {
        return (string)session()->get('role');
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return current_role() === 'Administrador General';
    }
}

if (!function_exists('is_supervisor')) {
    function is_supervisor(): bool
    {
        return current_role() === 'Supervisor';
    }
}

if (!function_exists('is_operator')) {
    function is_operator(): bool
    {
        return current_role() === 'Operador Electoral';
    }
}

if (!function_exists('is_supervisor_or_admin')) {
    function is_supervisor_or_admin(): bool
    {
        return is_admin() || is_supervisor();
    }
}

if (!function_exists('has_permission')) {
    function has_permission(string $name): bool
    {
        if (is_admin()) {
            return true;
        }

        static $permissions = null;
        if ($permissions === null) {
            $permissions = [];
            $roleId = (int)session()->get('role_id');
            if ($roleId) {
                $rows = db_connect()->table('role_permissions rp')
                    ->select('p.name')
                    ->join('permissions p', 'p.id = rp.permission_id')
                    ->where('rp.role_id', $roleId)
                    ->get()
                    ->getResultArray();
                $permissions = array_column($rows, 'name');
            }
        }

        return in_array($name, $permissions, true);
    }
}

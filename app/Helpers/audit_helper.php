<?php

function logAudit($userId, $action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null)
{
    $auditModel = new \App\Models\AuditModel();
    return $auditModel->log($userId, $action, $tableName, $recordId, $oldValues, $newValues);
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class AssignmentModel extends Model
{
    protected $table = 'assignments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = ['user_id', 'polling_station_id', 'election_id', 'assignment_type', 'is_active', 'created_by'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getUserAssignments($userId, $electionId = null)
    {
        $builder = $this->select('
                assignments.id as assignment_id, assignments.user_id, assignments.polling_station_id,
                assignments.election_id, assignments.assignment_type, assignments.created_by,
                polling_stations.id as ps_id, polling_stations.precinct_id,
                polling_stations.code_province, polling_stations.name_province,
                polling_stations.code_canton, polling_stations.name_canton,
                polling_stations.code_parish, polling_stations.name_parish,
                polling_stations.code_precinct, polling_stations.name_precinct,
                polling_stations.genero, polling_stations.numero_junta,
                polling_stations.numero_electores, polling_stations.status,
                users.first_name, users.last_name
            ')
            ->join('polling_stations', 'polling_stations.id = assignments.polling_station_id')
            ->join('users', 'users.id = assignments.user_id')
            ->where('assignments.user_id', $userId)
            ->where('assignments.is_active', 1);
        if ($electionId) {
            $builder->where('assignments.election_id', $electionId);
        }
        return $builder->findAll();
    }

    public function getPollingStationOperators($pollingStationId)
    {
        return $this->select('users.*')
            ->join('users', 'users.id = assignments.user_id')
            ->where('assignments.polling_station_id', $pollingStationId)
            ->where('assignments.is_active', 1)
            ->findAll();
    }
}

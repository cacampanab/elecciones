<?php

namespace App\Models;

use CodeIgniter\Model;

class PollingStationModel extends Model
{
    protected $table = 'polling_stations';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = [
        'precinct_id', 'election_id', 'code_province', 'name_province', 'code_canton', 'name_canton',
        'code_parish', 'name_parish', 'code_precinct', 'name_precinct', 'genero', 'numero_junta',
        'numero_electores', 'status', 'is_active'
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getByElection($electionId)
    {
        return $this->where('election_id', $electionId)->findAll();
    }

    public function getStats()
    {
        $total = $this->countAll();
        $reported = $this->where('status', 'reportada')->countAllResults();
        $pending = $this->where('status', 'pendiente')->countAllResults();
        $inProcess = $this->where('status', 'en_proceso')->countAllResults();
        $reviewed = $this->where('status', 'revisada')->countAllResults();
        $approved = $this->where('status', 'aprobada')->countAllResults();

        return compact('total', 'reported', 'pending', 'inProcess', 'reviewed', 'approved');
    }

    public function getByStatus($status)
    {
        return $this->where('status', $status)->findAll();
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class UploadedMinuteModel extends Model
{
    protected $table = 'uploaded_minutes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $protectFields = false;
    protected $allowedFields = ['polling_station_id', 'election_id', 'dignity_id', 'filename', 'original_name', 'file_path', 'file_type', 'file_size', 'version', 'is_current', 'status', 'notes', 'verified_by', 'verified_at', 'consistency_status', 'consistency_detail', 'uploaded_by', 'latitude', 'longitude'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}

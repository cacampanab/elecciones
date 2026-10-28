<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class MinuteController extends BaseController
{
    public function index()
    {
        $pollingStationId = $this->request->getVar('polling_station_id');
        $model = model('App\Models\UploadedMinuteModel');

        if ($pollingStationId) {
            $minutes = $model->where('polling_station_id', $pollingStationId)
                ->orderBy('version', 'DESC')
                ->findAll();
        } else {
            $minutes = $model
                ->select('uploaded_minutes.*, users.first_name, users.last_name, polling_stations.numero_junta')
                ->join('users', 'users.id = uploaded_minutes.uploaded_by')
                ->join('polling_stations', 'polling_stations.id = uploaded_minutes.polling_station_id')
                ->orderBy('uploaded_minutes.created_at', 'DESC')
                ->findAll();
        }

        return jsonResponse($minutes);
    }

    public function show($id = null)
    {
        $minute = model('App\Models\UploadedMinuteModel')
            ->select('uploaded_minutes.*, users.first_name, users.last_name, polling_stations.numero_junta')
            ->join('users', 'users.id = uploaded_minutes.uploaded_by')
            ->join('polling_stations', 'polling_stations.id = uploaded_minutes.polling_station_id')
            ->where('uploaded_minutes.id', $id)
            ->first();
        if (!$minute) return errorResponse('Acta no encontrada', 404);
        return jsonResponse($minute);
    }

    public function upload()
    {
        $rules = [
            'polling_station_id' => 'required|numeric',
            'election_id' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return errorResponse('Datos inválidos', 400, $this->validator->getErrors());
        }

        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return errorResponse('Archivo no válido', 400);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return errorResponse('Tipo de archivo no permitido. Solo JPG, PNG o PDF', 400);
        }

        $maxSize = 10 * 1024 * 1024;
        if ($file->getSize() > $maxSize) {
            return errorResponse('El archivo excede el tamaño máximo de 10MB', 400);
        }

        $pollingStationId = $this->request->getVar('polling_station_id');
        $electionId = $this->request->getVar('election_id');

        $model = model('App\Models\UploadedMinuteModel');
        $prevVersion = $model->where('polling_station_id', $pollingStationId)
            ->where('election_id', $electionId)
            ->where('is_current', 1)
            ->first();

        $version = $prevVersion ? $prevVersion->version + 1 : 1;

        if ($prevVersion) {
            $model->where('polling_station_id', $pollingStationId)
                ->where('election_id', $electionId)
                ->set(['is_current' => 0])
                ->update();
        }

        $newName = $file->getRandomName();
        $folder = 'uploads/minutes/' . $electionId . '/' . $pollingStationId;
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $file->move($folder, $newName);

        $data = [
            'polling_station_id' => $pollingStationId,
            'election_id' => $electionId,
            'filename' => $newName,
            'original_name' => $file->getClientName(),
            'file_path' => $folder . '/' . $newName,
            'file_type' => $file->getExtension(),
            'file_size' => $file->getSize(),
            'version' => $version,
            'is_current' => 1,
            'uploaded_by' => $this->getUserId(),
        ];

        $id = $model->insert($data);
        $this->logAudit('upload_minute', 'uploaded_minutes', $id, null, $data);

        return jsonResponse([
            'id' => $id,
            'filename' => $newName,
            'version' => $version,
        ], 'Acta cargada exitosamente', 201);
    }

    public function download($id = null)
    {
        $minute = model('App\Models\UploadedMinuteModel')->find($id);
        if (!$minute) return errorResponse('Acta no encontrada', 404);

        $path = $minute->file_path;
        if (!file_exists($path)) return errorResponse('Archivo no encontrado en el servidor', 404);

        return $this->response->download($path, null)->setFileName($minute->original_name);
    }

    public function preview($id = null)
    {
        $minute = model('App\Models\UploadedMinuteModel')->find($id);
        if (!$minute) return errorResponse('Acta no encontrada', 404);

        $path = $minute->file_path;
        if (!file_exists($path)) return errorResponse('Archivo no encontrado', 404);

        $mime = mime_content_type($path);
        return $this->response
            ->setContentType($mime)
            ->setBody(file_get_contents($path));
    }

    public function delete($id = null)
    {
        $model = model('App\Models\UploadedMinuteModel');
        $minute = $model->find($id);
        if (!$minute) return errorResponse('Acta no encontrada', 404);

        if (file_exists($minute->file_path)) {
            unlink($minute->file_path);
        }

        $model->delete($id);
        $this->logAudit('delete_minute', 'uploaded_minutes', $id);

        return jsonResponse(null, 'Acta eliminada');
    }
}

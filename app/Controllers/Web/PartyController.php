<?php

namespace App\Controllers\Web;

class PartyController extends BaseWebController
{
    public function index()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;
        $redirect = $this->requireRole('Administrador General');
        if ($redirect) return $redirect;

        $parties = model('App\Models\PoliticalPartyModel')->where('is_active', 1)->findAll();
        return $this->render('parties/index', compact('parties'), 'Partidos');
    }

    public function save()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;
        $redirect = $this->requireRole('Administrador General');
        if ($redirect) return $redirect;

        $id = (int)$this->request->getPost('id');
        $model = model('App\Models\PoliticalPartyModel');

        $data = [
            'name' => trim((string)$this->request->getPost('name')),
            'acronym' => trim((string)$this->request->getPost('acronym')),
            'list_number' => (int)$this->request->getPost('list_number'),
            'color' => $this->request->getPost('color') ?: null,
        ];
        if ($data['name'] === '' || $data['acronym'] === '') {
            return redirect()->back()->with('error', 'El nombre y las siglas son obligatorios.');
        }

        $existing = $id ? $model->find($id) : null;
        if ($id && !$existing) {
            return redirect()->back()->with('error', 'El partido no existe.');
        }

        try {
            $newLogo = $this->uploadLogo();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        if ($newLogo !== null) {
            if ($existing && !empty($existing->logo)) {
                $this->deleteLogoFile($existing->logo);
            }
            $data['logo'] = $newLogo;
        } elseif ($this->request->getPost('remove_logo')) {
            if ($existing && !empty($existing->logo)) {
                $this->deleteLogoFile($existing->logo);
            }
            $data['logo'] = null;
        }

        if ($id) {
            $model->update($id, $data);
            $this->logAudit('update_party', 'political_parties', $id);
        } else {
            $id = $model->insert($data);
            $this->logAudit('create_party', 'political_parties', $id);
        }

        return redirect()->to(base_url('parties'))->with('success', 'Partido guardado');
    }

    public function delete($id)
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;
        $redirect = $this->requireRole('Administrador General');
        if ($redirect) return $redirect;

        $model = model('App\Models\PoliticalPartyModel');
        $party = $model->find((int)$id);
        if ($party && !empty($party->logo)) {
            $this->deleteLogoFile($party->logo);
        }
        $model->delete((int)$id);
        $this->logAudit('delete_party', 'political_parties', (int)$id);

        return redirect()->to(base_url('parties'))->with('success', 'Partido eliminado');
    }

    private function uploadLogo(): ?string
    {
        $file = $this->request->getFile('logo_file');
        if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (!$file->isValid()) {
            if (in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                throw new \RuntimeException('El logo supera el tamaño máximo permitido por el servidor (' . ini_get('upload_max_filesize') . ').');
            }
            throw new \RuntimeException('No se pudo subir el logo.');
        }

        $ext = strtolower($file->getExtension());
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new \RuntimeException('El logo debe ser JPG, PNG o WEBP.');
        }
        if ((int)$file->getSize() > 2 * 1024 * 1024) {
            throw new \RuntimeException('El logo no debe superar 2 MB.');
        }

        $folder = FCPATH . 'uploads/parties';
        if (!is_dir($folder) && !mkdir($folder, 0777, true) && !is_dir($folder)) {
            throw new \RuntimeException('No se pudo crear la carpeta de logos.');
        }

        $newName = $file->getRandomName();
        if (!$file->move($folder, $newName)) {
            throw new \RuntimeException('No se pudo guardar el logo.');
        }

        return 'uploads/parties/' . $newName;
    }

    private function deleteLogoFile(?string $logo): void
    {
        if (!$logo) {
            return;
        }

        $path = FCPATH . str_replace('\\', '/', $logo);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

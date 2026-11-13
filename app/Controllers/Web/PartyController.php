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

        $folder = $this->resolveLogoFolder();
        $newName = $file->getRandomName();

        try {
            if (!$file->move($folder, $newName)) {
                throw new \RuntimeException('No se pudo guardar el logo.');
            }
        } catch (\Throwable $e) {
            log_message('error', 'Error al mover logo de partido: ' . $e->getMessage());
            throw new \RuntimeException('No se pudo guardar el logo. Verifique permisos de escritura en ' . $folder . '.');
        }

        $relative = str_replace('\\', '/', str_replace(FCPATH, '', $folder));
        $relative = trim($relative, '/');

        return $relative . '/' . $newName;
    }

    /**
     * Usa public/uploads/parties; si el servidor no permite escribir ahí,
     * cae a public/uploads/minutes/parties, que ya se usa para las actas.
     */
    private function resolveLogoFolder(): string
    {
        $folders = [
            FCPATH . 'uploads/parties',
            FCPATH . 'uploads/minutes/parties',
        ];

        foreach ($folders as $folder) {
            if (!is_dir($folder)) {
                @mkdir($folder, 0775, true);
            }
            if (!is_dir($folder)) {
                continue;
            }
            if (!is_writable($folder)) {
                @chmod($folder, 0775);
            }
            if (is_writable($folder)) {
                return $folder;
            }
        }

        throw new \RuntimeException('La carpeta de logos no tiene permisos de escritura. Cree public/uploads/parties y asígnele permisos al usuario del servidor web.');
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

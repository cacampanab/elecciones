<?php

namespace App\Controllers\Web;

class CircumscriptionController extends BaseWebController
{
    private const DIGNITY_SLUGS = ['concejal-urbano', 'concejal-rural'];

    public function index()
    {
        if ($redirect = $this->requireAuth()) return $redirect;
        if ($redirect = $this->requireRole('Administrador General')) return $redirect;

        $electionModel = model('App\Models\ElectionModel');
        $elections = $electionModel->getActive();

        $electionId = (int)($this->request->getGet('election_id') ?? 0);
        $validElectionIds = array_map(static fn($election) => (int)$election->id, $elections);
        if (!$electionId || !in_array($electionId, $validElectionIds, true)) {
            $current = $electionModel->getCurrent();
            $electionId = $current ? (int)$current->id : (int)($validElectionIds[0] ?? 0);
        }

        $provinces = model('App\Models\ProvinceModel')
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();

        $provinceId = (int)($this->request->getGet('province_id') ?? 0);
        $validProvinceIds = array_map(static fn($province) => (int)$province->id, $provinces);
        if (!$provinceId || !in_array($provinceId, $validProvinceIds, true)) {
            $provinceId = (int)($validProvinceIds[0] ?? 0);
        }

        $cantons = $provinceId
            ? model('App\Models\CantonModel')
                ->where('province_id', $provinceId)
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll()
            : [];

        $cantonId = (int)($this->request->getGet('canton_id') ?? 0);
        $validCantonIds = array_map(static fn($canton) => (int)$canton->id, $cantons);
        if (!$cantonId || !in_array($cantonId, $validCantonIds, true)) {
            $cantonId = (int)($validCantonIds[0] ?? 0);
        }
        $canton = null;
        foreach ($cantons as $item) {
            if ((int)$item->id === $cantonId) {
                $canton = $item;
                break;
            }
        }

        $dignities = model('App\Models\DignityModel')
            ->whereIn('slug', self::DIGNITY_SLUGS)
            ->where('is_active', 1)
            ->orderBy('id', 'ASC')
            ->findAll();

        $circumscriptions = ($electionId && $cantonId)
            ? model('App\Models\ElectionCircumscriptionModel')->forCanton($electionId, $cantonId)
            : [];

        $parishes = $cantonId
            ? model('App\Models\ParishModel')
                ->where('canton_id', $cantonId)
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll()
            : [];

        $parishModel = model('App\Models\ElectionCircumscriptionParishModel');
        $parishMap = [];
        $assignedParishIds = [];
        foreach ($circumscriptions as $circumscription) {
            $ids = $parishModel->forCircumscription((int)$circumscription->id);
            $parishMap[(int)$circumscription->id] = $ids;
            $assignedParishIds = array_merge($assignedParishIds, $ids);
        }

        $unassignedParishes = array_values(array_filter(
            $parishes,
            static fn($parish) => !in_array((int)$parish->id, $assignedParishIds, true)
        ));

        $seatMap = $electionId
            ? model('App\Models\ElectionSeatModel')->mapForCircumscriptions($electionId)
            : [];

        return $this->render('circumscriptions/index', compact(
            'elections',
            'electionId',
            'provinces',
            'provinceId',
            'cantons',
            'cantonId',
            'canton',
            'dignities',
            'parishes',
            'circumscriptions',
            'parishMap',
            'unassignedParishes',
            'seatMap'
        ), 'Circunscripciones');
    }

    public function save()
    {
        if ($redirect = $this->requireAuth()) return $redirect;
        if ($redirect = $this->requireRole('Administrador General')) return $redirect;

        $electionId = (int)$this->request->getPost('election_id');
        $cantonId = (int)$this->request->getPost('canton_id');
        $provinceId = (int)$this->request->getPost('province_id');
        $backUrl = base_url('circumscriptions?election_id=' . $electionId . '&province_id=' . $provinceId . '&canton_id=' . $cantonId);

        $election = $electionId ? model('App\Models\ElectionModel')->find($electionId) : null;
        $canton = $cantonId ? model('App\Models\CantonModel')->find($cantonId) : null;
        if (!$election || !$canton || (int)$canton->province_id !== $provinceId) {
            return redirect()->back()->with('error', 'La elección o el cantón seleccionado no es válido.');
        }

        $dignities = model('App\Models\DignityModel')
            ->whereIn('slug', self::DIGNITY_SLUGS)
            ->where('is_active', 1)
            ->findAll();
        $validDignityIds = array_map(static fn($dignity) => (int)$dignity->id, $dignities);

        $validParishIds = array_map(
            static fn($parish) => (int)$parish->id,
            model('App\Models\ParishModel')->where('canton_id', $cantonId)->where('is_active', 1)->findAll()
        );

        $rows = $this->request->getPost('rows');
        if (!is_array($rows)) {
            $rows = [];
        }

        $existing = model('App\Models\ElectionCircumscriptionModel')->forCanton($electionId, $cantonId);
        $existingIds = array_map(static fn($item) => (int)$item->id, $existing);

        $clean = [];
        $codes = [];
        $usedParishes = [];

        foreach ($rows as $row) {
            $code = strtoupper(trim((string)($row['code'] ?? '')));
            $name = trim((string)($row['name'] ?? ''));
            $dignityId = (int)($row['dignity_id'] ?? 0);
            $id = (int)($row['id'] ?? 0);

            if ($code === '' && $name === '') {
                continue;
            }
            if ($code === '' || $name === '') {
                return redirect()->to($backUrl)->with('error', 'Cada circunscripción debe tener código y nombre.');
            }
            if (!in_array($dignityId, $validDignityIds, true)) {
                return redirect()->to($backUrl)->with('error', 'Seleccione una dignidad válida para la circunscripción ' . $code . '.');
            }
            if (isset($codes[$code])) {
                return redirect()->to($backUrl)->with('error', 'El código ' . $code . ' está repetido.');
            }
            $codes[$code] = true;

            if ($id && !in_array($id, $existingIds, true)) {
                return redirect()->to($backUrl)->with('error', 'La circunscripción a editar no pertenece al cantón seleccionado.');
            }

            $parishIds = array_map('intval', (array)($row['parishes'] ?? []));
            $parishIds = array_values(array_unique(array_filter(
                $parishIds,
                static fn($parishId) => in_array($parishId, $validParishIds, true)
            )));

            foreach ($parishIds as $parishId) {
                if (isset($usedParishes[$parishId])) {
                    return redirect()->to($backUrl)->with('error', 'Una parroquia no puede estar en dos circunscripciones.');
                }
                $usedParishes[$parishId] = true;
            }

            $clean[] = [
                'id' => $id,
                'code' => $code,
                'name' => $name,
                'dignity_id' => $dignityId,
                'parishes' => $parishIds,
            ];
        }

        $this->db->transBegin();
        try {
            // Se resincronizan las parroquias del cantón para evitar choques
            // con la clave única (elección + parroquia).
            $this->db->table('election_circumscription_parishes')
                ->where('election_id', $electionId)
                ->where('canton_id', $cantonId)
                ->delete();

            // Se eliminan primero las circunscripciones que ya no vienen en el
            // formulario, para no chocar con la clave única del código.
            $submittedIds = [];
            foreach ($clean as $row) {
                if ($row['id'] > 0) {
                    $submittedIds[] = (int)$row['id'];
                }
            }
            foreach ($existing as $item) {
                if (in_array((int)$item->id, $submittedIds, true)) {
                    continue;
                }
                if ($this->circumscriptionHasData((int)$item->id)) {
                    throw new \RuntimeException('No se puede eliminar la circunscripción ' . $item->code . ': tiene listas o resultados asociados.');
                }
                model('App\Models\ElectionCircumscriptionModel')->delete((int)$item->id);
            }

            foreach ($clean as $row) {
                $data = [
                    'election_id' => $electionId,
                    'canton_id' => $cantonId,
                    'dignity_id' => $row['dignity_id'],
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'is_active' => 1,
                ];

                if ($row['id'] > 0) {
                    model('App\Models\ElectionCircumscriptionModel')->update($row['id'], $data);
                    $circumscriptionId = $row['id'];

                    // Mantener sincronizada la fila de escaños si cambió la
                    // dignidad o el cantón de la circunscripción.
                    $this->db->table('election_seats')
                        ->where('circumscription_id', $circumscriptionId)
                        ->update([
                            'canton_id' => $cantonId,
                            'dignity_id' => $row['dignity_id'],
                        ]);
                } else {
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $data['updated_at'] = date('Y-m-d H:i:s');
                    model('App\Models\ElectionCircumscriptionModel')->insert($data);
                    $circumscriptionId = (int)$this->db->insertID();
                }

                foreach ($row['parishes'] as $parishId) {
                    $this->db->table('election_circumscription_parishes')->insert([
                        'election_id' => $electionId,
                        'canton_id' => $cantonId,
                        'circumscription_id' => $circumscriptionId,
                        'parish_id' => $parishId,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Error al guardar circunscripciones: ' . $e->getMessage());
            return redirect()->to($backUrl)->with('error', 'No se pudieron guardar las circunscripciones: ' . $e->getMessage());
        }

        $this->logAudit('save_circumscriptions', 'election_circumscriptions', $cantonId, null, [
            'election_id' => $electionId,
            'canton_id' => $cantonId,
            'rows' => count($clean),
        ]);

        return redirect()->to($backUrl)->with('success', 'Circunscripciones guardadas correctamente.');
    }

    public function delete($id)
    {
        if ($redirect = $this->requireAuth()) return $redirect;
        if ($redirect = $this->requireRole('Administrador General')) return $redirect;

        $circumscription = model('App\Models\ElectionCircumscriptionModel')->find((int)$id);
        if (!$circumscription) {
            return redirect()->to(base_url('circumscriptions'))->with('error', 'La circunscripción no existe.');
        }

        $canton = $circumscription->canton_id
            ? model('App\Models\CantonModel')->find((int)$circumscription->canton_id)
            : null;
        $backUrl = base_url('circumscriptions?election_id=' . (int)$circumscription->election_id
            . '&province_id=' . (int)($canton->province_id ?? 0)
            . '&canton_id=' . (int)$circumscription->canton_id);

        if ($this->circumscriptionHasData((int)$id)) {
            return redirect()->to($backUrl)->with('error', 'No se puede eliminar la circunscripción: tiene listas o resultados asociados.');
        }

        // Liberar primero las referencias de escaños; el resto cae por cascada.
        $this->db->table('election_seats')->where('circumscription_id', (int)$id)->delete();
        model('App\Models\ElectionCircumscriptionModel')->delete((int)$id);
        $this->logAudit('delete_circumscription', 'election_circumscriptions', (int)$id);

        return redirect()->to($backUrl)->with('success', 'Circunscripción eliminada.');
    }

    private function circumscriptionHasData(int $circumscriptionId): bool
    {
        $lists = $this->db->table('candidate_lists')
            ->where('circumscription_id', $circumscriptionId)
            ->countAllResults();
        if ($lists > 0) {
            return true;
        }

        return $this->db->table('election_list_results')
            ->where('circumscription_id', $circumscriptionId)
            ->countAllResults() > 0;
    }
}

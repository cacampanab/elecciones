<?php

namespace App\Controllers\Web;

class ElectionSeatController extends BaseWebController
{
    public function index()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;
        $redirect = $this->requireRole('Administrador General');
        if ($redirect) return $redirect;

        $electionModel = model('App\Models\ElectionModel');
        $elections = $electionModel->getActive();

        $electionId = (int)($this->request->getGet('election_id') ?? 0);
        $validIds = array_map(static fn($election) => (int)$election->id, $elections);
        if (!$electionId || !in_array($electionId, $validIds, true)) {
            $current = $electionModel->getCurrent();
            $electionId = $current ? (int)$current->id : (int)($elections[0]->id ?? 0);
        }

        $selectedElection = null;
        foreach ($elections as $election) {
            if ((int)$election->id === $electionId) {
                $selectedElection = $election;
                break;
            }
        }

        $cantons = [];
        if ($electionId) {
            $cantons = $this->db->table('cantons c')
                ->select('c.id, c.code, c.name, c.province_id, p.code AS province_code, p.name AS province_name')
                ->join('provinces p', 'p.id = c.province_id')
                ->where('c.is_active', 1)
                ->orderBy('p.name', 'ASC')
                ->orderBy('c.name', 'ASC')
                ->get()
                ->getResult();
        }

        $dignities = model('App\Models\DignityModel')
            ->whereIn('slug', ['concejal-urbano', 'concejal-rural'])
            ->where('is_active', 1)
            ->orderBy('id', 'ASC')
            ->findAll();

        $seatMap = $electionId
            ? model('App\Models\ElectionSeatModel')->mapForElection($electionId)
            : [];

        $circumscriptions = $electionId
            ? model('App\Models\ElectionCircumscriptionModel')->forElection($electionId)
            : [];
        $circSeatMap = $electionId
            ? model('App\Models\ElectionSeatModel')->mapForCircumscriptions($electionId)
            : [];
        $cantonsWithCircumscriptions = [];
        foreach ($circumscriptions as $circumscription) {
            $cantonsWithCircumscriptions[(int)$circumscription->canton_id] = true;
        }

        return $this->render('election_seats/index', compact(
            'elections',
            'selectedElection',
            'electionId',
            'cantons',
            'dignities',
            'seatMap',
            'circumscriptions',
            'circSeatMap',
            'cantonsWithCircumscriptions'
        ), 'Escaños de Concejales');
    }

    public function save()
    {
        $redirect = $this->requireAuth();
        if ($redirect) return $redirect;
        $redirect = $this->requireRole('Administrador General');
        if ($redirect) return $redirect;

        $electionId = (int)$this->request->getPost('election_id');
        $election = $electionId ? model('App\Models\ElectionModel')->find($electionId) : null;
        if (!$election) {
            return redirect()->back()->with('error', 'La elección seleccionada no existe.');
        }

        $dignities = model('App\Models\DignityModel')
            ->whereIn('slug', ['concejal-urbano', 'concejal-rural'])
            ->where('is_active', 1)
            ->findAll();
        $dignityIds = array_map(static fn($dignity) => (int)$dignity->id, $dignities);

        $cantons = $this->db->table('cantons')
            ->select('id')
            ->where('is_active', 1)
            ->get()
            ->getResult();
        $cantonIds = array_map(static fn($canton) => (int)$canton->id, $cantons);

        $rows = $this->request->getPost('seats');
        if (!is_array($rows)) {
            $rows = [];
        }

        $model = model('App\Models\ElectionSeatModel');
        $before = $model->mapForElection($electionId);

        $this->db->transBegin();
        try {
            foreach ($cantonIds as $cantonId) {
                foreach ($dignityIds as $dignityId) {
                    $value = $rows[$cantonId][$dignityId] ?? null;
                    if ($value === null || $value === '') {
                        continue;
                    }

                    $seats = filter_var($value, FILTER_VALIDATE_INT);
                    if ($seats === false || $seats < 0 || $seats > 100) {
                        throw new \RuntimeException('La cantidad de escaños debe ser un número entre 0 y 100.');
                    }

                    $existing = $model->where('election_id', $electionId)
                        ->where('canton_id', $cantonId)
                        ->where('dignity_id', $dignityId)
                        ->where('circumscription_id', null)
                        ->first();

                    if ($seats === 0) {
                        if ($existing) {
                            $model->delete($existing->id);
                        }
                        continue;
                    }

                    if ($existing) {
                        $model->update($existing->id, ['seats' => $seats]);
                    } else {
                        $model->insert([
                            'election_id' => $electionId,
                            'canton_id' => $cantonId,
                            'dignity_id' => $dignityId,
                            'seats' => $seats,
                        ]);
                    }
                }
            }

            // Escaños por circunscripción.
            $circSeats = $this->request->getPost('circ_seats');
            if (!is_array($circSeats)) {
                $circSeats = [];
            }
            $circumscriptionMap = [];
            foreach (model('App\Models\ElectionCircumscriptionModel')->forElection($electionId) as $item) {
                $circumscriptionMap[(int)$item->id] = $item;
            }
            foreach ($circSeats as $circumscriptionId => $value) {
                $circumscriptionId = (int)$circumscriptionId;
                if (!isset($circumscriptionMap[$circumscriptionId])) {
                    continue;
                }
                if ($value === null || $value === '') {
                    continue;
                }

                $seats = filter_var($value, FILTER_VALIDATE_INT);
                if ($seats === false || $seats < 0 || $seats > 100) {
                    throw new \RuntimeException('La cantidad de escaños por circunscripción debe ser un número entre 0 y 100.');
                }

                $existing = $model->where('election_id', $electionId)
                    ->where('circumscription_id', $circumscriptionId)
                    ->first();

                if ($seats === 0) {
                    if ($existing) {
                        $model->delete($existing->id);
                    }
                    continue;
                }

                if ($existing) {
                    $model->update($existing->id, ['seats' => $seats]);
                } else {
                    $model->insert([
                        'election_id' => $electionId,
                        'canton_id' => (int)$circumscriptionMap[$circumscriptionId]->canton_id,
                        'dignity_id' => (int)$circumscriptionMap[$circumscriptionId]->dignity_id,
                        'circumscription_id' => $circumscriptionId,
                        'seats' => $seats,
                    ]);
                }
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Error al guardar escaños: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }

        $after = $model->mapForElection($electionId);
        $this->logAudit('save_election_seats', 'election_seats', $electionId, $before, $after);

        return redirect()
            ->to(base_url('election-seats?election_id=' . $electionId))
            ->with('success', 'Configuración de escaños guardada correctamente.');
    }
}

<?php

namespace App\Controllers\Web;

use App\Libraries\DHondtService;

class ConcejalController extends BaseWebController
{
    private const DIGNITY_SLUGS = ['concejal-urbano', 'concejal-rural'];

    public function index()
    {
        if ($redirect = $this->requireAuth()) return $redirect;
        if ($redirect = $this->requireRole('Administrador General')) return $redirect;

        $context = $this->resolveContext($this->request->getGet());
        if ($context['error']) {
            return redirect()->to(base_url('dashboard'))->with('error', $context['error']);
        }

        $this->ensureConcejalDignities($context['electionId']);

        $lists = $this->listsForContext(
            $context['electionId'],
            $context['cantonId'],
            $context['dignityId'],
            $context['circumscriptionId'],
            $context['votesMap']
        );

        $calculation = null;
        $hasVotes = false;
        foreach ($lists as $list) {
            if ((int)$list->votes > 0) {
                $hasVotes = true;
                break;
            }
        }

        if ($hasVotes || $this->request->getGet('calcular')) {
            $calculation = (new DHondtService())->allocate(
                $this->listsForCalculation($lists),
                $context['seats']
            );
        }

        return $this->render('concejales/index', array_merge($context, [
            'lists' => $lists,
            'parties' => model('App\Models\PoliticalPartyModel')->where('is_active', 1)->orderBy('name', 'ASC')->findAll(),
            'calculation' => $calculation,
        ]), 'Concejales');
    }

    public function saveCandidate()
    {
        if ($redirect = $this->requireAuth()) return $redirect;
        if ($redirect = $this->requireRole('Administrador General')) return $redirect;

        $context = $this->resolveContext($this->request->getPost());
        if ($context['error']) {
            return redirect()->to(base_url('concejales'))->with('error', $context['error']);
        }

        $this->ensureDignityEnabled($context['electionId'], $context['dignityId']);

        $partyId = (int)$this->request->getPost('political_party_id');
        $firstName = trim((string)$this->request->getPost('first_name'));
        $lastName = trim((string)$this->request->getPost('last_name'));
        $cedula = trim((string)$this->request->getPost('cedula'));
        $position = $this->request->getPost('position');

        $party = model('App\Models\PoliticalPartyModel')->where('is_active', 1)->find($partyId);
        if (!$party) {
            return redirect()->back()->with('error', 'Seleccione un partido válido.');
        }
        if ($firstName === '' || $lastName === '') {
            return redirect()->back()->with('error', 'Nombres y apellidos son obligatorios.');
        }

        $listQuery = model('App\Models\CandidateListModel')
            ->where('election_id', $context['electionId'])
            ->where('canton_id', $context['cantonId'])
            ->where('dignity_id', $context['dignityId'])
            ->where('political_party_id', $partyId);
        if ($context['circumscriptionId']) {
            $listQuery->where('circumscription_id', $context['circumscriptionId']);
        } else {
            $listQuery->where('circumscription_id', null);
        }
        $list = $listQuery->first();

        if (!$list) {
            $listName = $party->acronym . ' - ' . $context['dignity']->name . ' - ' . $context['canton']->name;
            if ($context['circumscription']) {
                $listName .= ' - ' . $context['circumscription']->name;
            }
            $listId = model('App\Models\CandidateListModel')->insert([
                'election_id' => $context['electionId'],
                'canton_id' => $context['cantonId'],
                'circumscription_id' => $context['circumscriptionId'] ?: null,
                'political_party_id' => $partyId,
                'dignity_id' => $context['dignityId'],
                'name' => $listName,
                'is_active' => 1,
            ]);
            if (!$listId) {
                return redirect()->back()->with('error', 'No se pudo crear la lista del partido.');
            }
            $list = model('App\Models\CandidateListModel')->find($listId);
        }

        if ($position === null || $position === '') {
            $max = $this->db->table('candidates')
                ->selectMax('position')
                ->where('candidate_list_id', $list->id)
                ->get()
                ->getRow();
            $position = ((int)($max->position ?? 0)) + 1;
        }

        $position = filter_var($position, FILTER_VALIDATE_INT);
        if ($position === false || $position < 1) {
            return redirect()->back()->with('error', 'La posición en la lista debe ser un número mayor a cero.');
        }

        if ($cedula !== '' && model('App\Models\CandidateModel')->where('cedula', $cedula)->first()) {
            return redirect()->back()->with('error', 'Ya existe un candidato con esa cédula.');
        }

        $duplicate = model('App\Models\CandidateModel')
            ->where('candidate_list_id', $list->id)
            ->where('position', $position)
            ->first();
        if ($duplicate) {
            return redirect()->back()->with('error', 'Esa posición ya está ocupada en la lista. Use otra posición u ordene nuevamente.');
        }

        $candidateId = model('App\Models\CandidateModel')->insert([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'cedula' => $cedula !== '' ? $cedula : null,
            'dignity_id' => $context['dignityId'],
            'political_party_id' => $partyId,
            'candidate_list_id' => $list->id,
            'position' => $position,
            'election_id' => $context['electionId'],
            'canton_id' => $context['cantonId'],
            'is_active' => 1,
        ]);

        if (!$candidateId) {
            return redirect()->back()->with('error', 'No se pudo guardar el candidato.');
        }

        $this->logAudit('create_concejal_candidate', 'candidates', $candidateId, null, [
            'election_id' => $context['electionId'],
            'canton_id' => $context['cantonId'],
            'dignity_id' => $context['dignityId'],
            'candidate_list_id' => $list->id,
            'position' => $position,
        ]);

        return redirect()->to($this->contextUrl($context))->with('success', 'Candidato agregado a la lista.');
    }

    public function updateCandidates()
    {
        if ($redirect = $this->requireAuth()) return $redirect;
        if ($redirect = $this->requireRole('Administrador General')) return $redirect;

        $context = $this->resolveContext($this->request->getPost());
        if ($context['error']) {
            return redirect()->to(base_url('concejales'))->with('error', $context['error']);
        }

        $posted = $this->request->getPost('candidates');
        if (!is_array($posted) || empty($posted)) {
            return redirect()->to($this->contextUrl($context))->with('error', 'No hay candidatos para actualizar.');
        }

        $validListIds = array_map(
            static fn($list) => (int)$list->id,
            $this->db->table('candidate_lists')
                ->select('id')
                ->where('election_id', $context['electionId'])
                ->where('canton_id', $context['cantonId'])
                ->where('dignity_id', $context['dignityId'])
                ->where('circumscription_id', $context['circumscriptionId'] ?: null)
                ->get()
                ->getResult()
        );

        $positions = [];
        $updates = [];
        foreach ($posted as $candidateId => $row) {
            $candidateId = (int)$candidateId;
            $candidate = model('App\Models\CandidateModel')->find($candidateId);
            if (!$candidate || !in_array((int)$candidate->candidate_list_id, $validListIds, true)) {
                continue;
            }

            $firstName = trim((string)($row['first_name'] ?? ''));
            $lastName = trim((string)($row['last_name'] ?? ''));
            $cedula = trim((string)($row['cedula'] ?? ''));
            $position = filter_var($row['position'] ?? null, FILTER_VALIDATE_INT);

            if ($firstName === '' || $lastName === '' || $position === false || $position < 1) {
                return redirect()->to($this->contextUrl($context))->with('error', 'Revise nombres, apellidos y posiciones. Todos deben ser válidos.');
            }

            $key = (int)$candidate->candidate_list_id . ':' . $position;
            if (isset($positions[$key])) {
                return redirect()->to($this->contextUrl($context))->with('error', 'Hay posiciones repetidas dentro de una misma lista.');
            }
            $positions[$key] = true;

            if ($cedula !== '') {
                $duplicate = model('App\Models\CandidateModel')
                    ->where('cedula', $cedula)
                    ->where('id !=', $candidateId)
                    ->first();
                if ($duplicate) {
                    return redirect()->to($this->contextUrl($context))->with('error', 'La cédula ' . $cedula . ' ya pertenece a otro candidato.');
                }
            }

            $updates[] = [
                'id' => $candidateId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'cedula' => $cedula !== '' ? $cedula : null,
                'position' => $position,
            ];
        }

        $this->db->transBegin();
        try {
            // Liberar posiciones actuales para permitir intercambios sin chocar con la clave única.
            foreach ($updates as $update) {
                $this->db->table('candidates')
                    ->where('id', (int)$update['id'])
                    ->update(['position' => null]);
            }

            foreach ($updates as $update) {
                $id = $update['id'];
                unset($update['id']);
                model('App\Models\CandidateModel')->update($id, $update);
            }
            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Error al actualizar candidatos de concejales: ' . $e->getMessage());
            return redirect()->to($this->contextUrl($context))->with('error', 'No se pudieron actualizar los candidatos: ' . $e->getMessage());
        }

        $this->logAudit('update_concejal_candidates', 'candidates', null, null, [
            'election_id' => $context['electionId'],
            'canton_id' => $context['cantonId'],
            'dignity_id' => $context['dignityId'],
            'updated' => count($updates),
        ]);

        return redirect()->to($this->contextUrl($context))->with('success', 'Listas actualizadas correctamente.');
    }

    public function deleteCandidate($id)
    {
        if ($redirect = $this->requireAuth()) return $redirect;
        if ($redirect = $this->requireRole('Administrador General')) return $redirect;

        $context = $this->resolveContext($this->request->getGet());
        if ($context['error']) {
            return redirect()->to(base_url('concejales'))->with('error', $context['error']);
        }

        $candidate = model('App\Models\CandidateModel')->find((int)$id);
        if (!$candidate) {
            return redirect()->to($this->contextUrl($context))->with('error', 'Candidato no encontrado.');
        }

        $list = model('App\Models\CandidateListModel')->find((int)$candidate->candidate_list_id);
        if (!$list
            || (int)$list->election_id !== $context['electionId']
            || (int)$list->canton_id !== $context['cantonId']
            || (int)$list->dignity_id !== $context['dignityId']
            || (int)($list->circumscription_id ?? 0) !== (int)($context['circumscriptionId'] ?? 0)) {
            return redirect()->to($this->contextUrl($context))->with('error', 'El candidato no pertenece a la lista seleccionada.');
        }

        model('App\Models\CandidateModel')->delete((int)$id);
        $this->logAudit('delete_concejal_candidate', 'candidates', (int)$id);

        return redirect()->to($this->contextUrl($context))->with('success', 'Candidato eliminado de la lista.');
    }

    public function saveVotes()
    {
        if ($redirect = $this->requireAuth()) return $redirect;
        if ($redirect = $this->requireRole('Administrador General')) return $redirect;

        $context = $this->resolveContext($this->request->getPost());
        if ($context['error']) {
            return redirect()->to(base_url('concejales'))->with('error', $context['error']);
        }

        $previousVotes = $this->request->getPost('previous_votes');
        $previousVotes = is_array($previousVotes) ? $previousVotes : [];

        $validListIds = array_map(
            static fn($list) => (int)$list->id,
            $this->db->table('candidate_lists')
                ->select('id')
                ->where('election_id', $context['electionId'])
                ->where('canton_id', $context['cantonId'])
                ->where('dignity_id', $context['dignityId'])
                ->where('circumscription_id', $context['circumscriptionId'] ?: null)
                ->get()
                ->getResult()
        );

        $this->db->transBegin();
        try {
            foreach ($validListIds as $listId) {
                $rawPrevious = $previousVotes[$listId] ?? 0;
                $previous = filter_var($rawPrevious === '' ? 0 : $rawPrevious, FILTER_VALIDATE_INT);
                if ($previous === false || $previous < 0) {
                    throw new \RuntimeException('Los votos de la elección anterior deben ser números enteros mayores o iguales a cero.');
                }

                $existing = model('App\Models\ElectionListVoteModel')
                    ->where('election_id', $context['electionId'])
                    ->where('canton_id', $context['cantonId'])
                    ->where('dignity_id', $context['dignityId'])
                    ->where('circumscription_id', $context['circumscriptionId'] ?: null)
                    ->where('candidate_list_id', $listId)
                    ->first();

                if ($existing) {
                    model('App\Models\ElectionListVoteModel')->update($existing->id, [
                        'previous_votes' => $previous,
                    ]);
                } else {
                    model('App\Models\ElectionListVoteModel')->insert([
                        'election_id' => $context['electionId'],
                        'canton_id' => $context['cantonId'],
                        'circumscription_id' => $context['circumscriptionId'] ?: null,
                        'dignity_id' => $context['dignityId'],
                        'candidate_list_id' => $listId,
                        'votes' => 0,
                        'previous_votes' => $previous,
                        'created_by' => (int)session()->get('user_id'),
                    ]);
                }
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Error al guardar votos de concejales: ' . $e->getMessage());
            return redirect()->to($this->contextUrl($context))->with('error', 'No se pudieron guardar los votos: ' . $e->getMessage());
        }

        $this->logAudit('save_concejal_votes', 'election_list_votes', null, null, [
            'election_id' => $context['electionId'],
            'canton_id' => $context['cantonId'],
            'dignity_id' => $context['dignityId'],
        ]);

        return redirect()->to($this->contextUrl($context) . '&calcular=1')
            ->with('success', 'Votos de desempate guardados y escaños calculados.');
    }

    private function resolveContext(array $source): array
    {
        $electionModel = model('App\Models\ElectionModel');
        $elections = $electionModel->getActive();
        $validElectionIds = array_map(static fn($election) => (int)$election->id, $elections);

        $electionId = (int)($source['election_id'] ?? 0);
        if (!$electionId || !in_array($electionId, $validElectionIds, true)) {
            $current = $electionModel->getCurrent();
            $electionId = $current ? (int)$current->id : (int)($elections[0]->id ?? 0);
        }
        $election = $electionId ? $electionModel->find($electionId) : null;

        $dignities = model('App\Models\DignityModel')
            ->whereIn('slug', self::DIGNITY_SLUGS)
            ->where('is_active', 1)
            ->orderBy('id', 'ASC')
            ->findAll();
        $validDignityIds = array_map(static fn($dignity) => (int)$dignity->id, $dignities);

        $dignityId = (int)($source['dignity_id'] ?? 0);
        if (!$dignityId || !in_array($dignityId, $validDignityIds, true)) {
            $dignityId = (int)($validDignityIds[0] ?? 0);
        }

        $cantons = $this->db->table('cantons c')
            ->select('c.id, c.code, c.name, c.province_id, p.name AS province_name, p.code AS province_code')
            ->join('provinces p', 'p.id = c.province_id')
            ->where('c.is_active', 1)
            ->orderBy('p.name', 'ASC')
            ->orderBy('c.name', 'ASC')
            ->get()
            ->getResult();
        $validCantonIds = array_map(static fn($canton) => (int)$canton->id, $cantons);

        $seatMap = $electionId ? model('App\Models\ElectionSeatModel')->mapForElection($electionId) : [];
        $circSeatMap = $electionId ? model('App\Models\ElectionSeatModel')->mapForCircumscriptions($electionId) : [];

        $circumscriptionsByCanton = [];
        if ($electionId) {
            foreach (model('App\Models\ElectionCircumscriptionModel')->forElection($electionId) as $item) {
                $circumscriptionsByCanton[(int)$item->canton_id][] = $item;
            }
        }

        $cantonId = (int)($source['canton_id'] ?? 0);
        if (!$cantonId || !in_array($cantonId, $validCantonIds, true)) {
            $cantonId = 0;
            foreach ($cantons as $item) {
                $itemId = (int)$item->id;
                if ((int)($seatMap[$itemId][$dignityId] ?? 0) > 0) {
                    $cantonId = $itemId;
                    break;
                }
                foreach ($circumscriptionsByCanton[$itemId] ?? [] as $circumscription) {
                    if ((int)$circumscription->dignity_id === $dignityId
                        && (int)($circSeatMap[(int)$circumscription->id] ?? 0) > 0) {
                        $cantonId = $itemId;
                        break 2;
                    }
                }
            }
            if (!$cantonId) {
                $cantonId = (int)($validCantonIds[0] ?? 0);
            }
        }
        $canton = null;
        foreach ($cantons as $item) {
            if ((int)$item->id === $cantonId) {
                $canton = $item;
                break;
            }
        }

        if (!$election) {
            return ['error' => 'No hay una elección válida seleccionada.'];
        }
        if (!$dignities) {
            return ['error' => 'No están activas las dignidades de concejales.'];
        }
        if (!$canton) {
            return ['error' => 'No hay un cantón válido seleccionado.'];
        }

        $cantonCircumscriptions = $circumscriptionsByCanton[$cantonId] ?? [];
        $usesCircumscriptions = !empty($cantonCircumscriptions);
        $circumscription = null;
        $circumscriptionId = null;

        if ($usesCircumscriptions) {
            $requestedId = (int)($source['circumscription_id'] ?? 0);
            foreach ($cantonCircumscriptions as $item) {
                if ((int)$item->id === $requestedId) {
                    $circumscription = $item;
                    break;
                }
            }
            if (!$circumscription) {
                foreach ($cantonCircumscriptions as $item) {
                    if ((int)$item->dignity_id === $dignityId) {
                        $circumscription = $item;
                        break;
                    }
                }
            }
            if (!$circumscription) {
                $circumscription = $cantonCircumscriptions[0];
            }

            $circumscriptionId = (int)$circumscription->id;
            $dignityId = (int)$circumscription->dignity_id;
        }

        $dignity = null;
        foreach ($dignities as $item) {
            if ((int)$item->id === $dignityId) {
                $dignity = $item;
                break;
            }
        }
        if (!$dignity) {
            return ['error' => 'No están activas las dignidades de concejales.'];
        }

        if ($usesCircumscriptions) {
            $seats = (int)($circSeatMap[$circumscriptionId] ?? 0);
            $votesMap = model('App\Models\ElectionListVoteModel')->mapForContext(
                $electionId,
                $cantonId,
                $dignityId,
                $circumscriptionId
            );
        } else {
            $seats = (int)($seatMap[$cantonId][$dignityId] ?? 0);
            $votesMap = model('App\Models\ElectionListVoteModel')->mapForContext($electionId, $cantonId, $dignityId);
        }

        return [
            'error' => null,
            'elections' => $elections,
            'electionId' => $electionId,
            'election' => $election,
            'cantons' => $cantons,
            'cantonId' => $cantonId,
            'canton' => $canton,
            'dignities' => $dignities,
            'dignityId' => $dignityId,
            'dignity' => $dignity,
            'circumscriptionId' => $circumscriptionId,
            'circumscription' => $circumscription,
            'circumscriptions' => $cantonCircumscriptions,
            'usesCircumscriptions' => $usesCircumscriptions,
            'seats' => $seats,
            'votesMap' => $votesMap,
        ];
    }

    private function listsForContext(int $electionId, int $cantonId, int $dignityId, ?int $circumscriptionId, array $votesMap): array
    {
        $builder = $this->db->table('candidate_lists cl')
            ->select('cl.*, p.name AS party_name, p.acronym AS party_acronym, p.list_number, p.color')
            ->join('political_parties p', 'p.id = cl.political_party_id')
            ->where('cl.election_id', $electionId)
            ->where('cl.canton_id', $cantonId)
            ->where('cl.dignity_id', $dignityId)
            ->where('cl.is_active', 1);

        if ($circumscriptionId !== null) {
            $builder->where('cl.circumscription_id', $circumscriptionId);
        } else {
            $builder->where('cl.circumscription_id', null);
        }

        $lists = $builder
            ->orderBy('p.list_number', 'ASC')
            ->orderBy('p.name', 'ASC')
            ->get()
            ->getResult();

        $resultTotals = model('App\Models\ElectionListResultModel')
            ->totalsForContext($electionId, $cantonId, $dignityId, $circumscriptionId);

        foreach ($lists as $list) {
            $list->candidates = $this->db->table('candidates')
                ->where('candidate_list_id', $list->id)
                ->orderBy('position', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResult();
            $list->votes = (int)($resultTotals[(int)$list->id] ?? 0);
            $list->previous_votes = 0;
        }

        return $lists;
    }

    private function listsForCalculation(array $lists): array
    {
        $result = [];
        foreach ($lists as $list) {
            $candidates = [];
            foreach ($list->candidates as $candidate) {
                $candidates[] = [
                    'id' => (int)$candidate->id,
                    'first_name' => $candidate->first_name,
                    'last_name' => $candidate->last_name,
                    'position' => $candidate->position,
                ];
            }

            $result[] = [
                'id' => (int)$list->id,
                'name' => trim(($list->party_acronym ?? '') . ' - ' . ($list->party_name ?? '')),
                'list_number' => (int)($list->list_number ?? 0),
                'votes' => (int)$list->votes,
                'previous_votes' => (int)$list->previous_votes,
                'candidates' => $candidates,
            ];
        }

        return $result;
    }

    private function ensureConcejalDignities(int $electionId): void
    {
        $rows = $this->db->table('candidate_lists')
            ->select('DISTINCT dignity_id', false)
            ->where('election_id', $electionId)
            ->whereIn('dignity_id', array_map(
                static fn($dignity) => (int)$dignity->id,
                model('App\Models\DignityModel')->whereIn('slug', self::DIGNITY_SLUGS)->findAll()
            ))
            ->get()
            ->getResult();

        foreach ($rows as $row) {
            $this->ensureDignityEnabled($electionId, (int)$row->dignity_id);
        }
    }

    private function ensureDignityEnabled(int $electionId, int $dignityId): void
    {
        $exists = $this->db->table('election_dignities')
            ->where('election_id', $electionId)
            ->where('dignity_id', $dignityId)
            ->countAllResults();
        if ($exists) {
            return;
        }

        $this->db->table('election_dignities')->insert([
            'election_id' => $electionId,
            'dignity_id' => $dignityId,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function contextUrl(array $context): string
    {
        $url = base_url('concejales') . '?election_id=' . $context['electionId']
            . '&canton_id=' . $context['cantonId']
            . '&dignity_id=' . $context['dignityId'];
        if (!empty($context['circumscriptionId'])) {
            $url .= '&circumscription_id=' . (int)$context['circumscriptionId'];
        }

        return $url;
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedEsmeraldasCircumscriptions extends Migration
{
    public function up()
    {
        $canton = $this->db->table('cantons')
            ->where('code', '560')
            ->where('name', 'Esmeraldas')
            ->get()
            ->getRow();
        if (!$canton) {
            return;
        }

        $urban = $this->db->table('dignities')->where('slug', 'concejal-urbano')->get()->getRow();
        $rural = $this->db->table('dignities')->where('slug', 'concejal-rural')->get()->getRow();
        if (!$urban || !$rural) {
            return;
        }

        $parishes = $this->db->table('parishes')
            ->where('canton_id', (int)$canton->id)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();
        if (!$parishes) {
            return;
        }

        $u1ParishIds = array_values(array_filter([
            $this->findParishId($parishes, 'ESMERALDAS'),
            $this->findParishId($parishes, 'LUIS TELLO'),
            $this->findParishId($parishes, 'BARTOLOME RUIZ'),
        ]));
        $u2ParishIds = array_values(array_filter([
            $this->findParishId($parishes, '5 DE AGOSTO'),
            $this->findParishId($parishes, 'SIMON PLATA TORRES'),
        ]));

        $assigned = array_merge($u1ParishIds, $u2ParishIds);
        $ruralParishIds = [];
        foreach ($parishes as $parish) {
            if (!in_array((int)$parish->id, $assigned, true)) {
                $ruralParishIds[] = (int)$parish->id;
            }
        }

        // Elecciones donde Esmeraldas ya tiene escaños o listas configuradas.
        $electionIds = [];
        $rows = $this->db->table('election_seats')
            ->select('DISTINCT election_id', false)
            ->where('canton_id', (int)$canton->id)
            ->get()
            ->getResult();
        foreach ($rows as $row) {
            $electionIds[(int)$row->election_id] = true;
        }
        $rows = $this->db->table('candidate_lists')
            ->select('DISTINCT election_id', false)
            ->where('canton_id', (int)$canton->id)
            ->get()
            ->getResult();
        foreach ($rows as $row) {
            $electionIds[(int)$row->election_id] = true;
        }

        $now = date('Y-m-d H:i:s');
        foreach (array_keys($electionIds) as $electionId) {
            $exists = $this->db->table('election_circumscriptions')
                ->where('election_id', $electionId)
                ->where('canton_id', (int)$canton->id)
                ->countAllResults();
            if ($exists > 0) {
                continue;
            }

            $u1 = $this->createCircumscription($electionId, (int)$canton->id, (int)$urban->id, 'U1', 'Circunscripción Urbana 1', $u1ParishIds, 5, $now);
            $u2 = $this->createCircumscription($electionId, (int)$canton->id, (int)$urban->id, 'U2', 'Circunscripción Urbana 2', $u2ParishIds, 4, $now);
            if ($ruralParishIds) {
                $this->createCircumscription($electionId, (int)$canton->id, (int)$rural->id, 'R', 'Circunscripción Rural', $ruralParishIds, 2, $now);
            }
        }
    }

    public function down()
    {
        // No se elimina la configuración creada para evitar pérdida de datos.
    }

    private function findParishId(array $parishes, string $needle): ?int
    {
        foreach ($parishes as $parish) {
            if (mb_stripos($parish->name, $needle) !== false) {
                return (int)$parish->id;
            }
        }

        return null;
    }

    private function createCircumscription(
        int $electionId,
        int $cantonId,
        int $dignityId,
        string $code,
        string $name,
        array $parishIds,
        int $seats,
        string $now
    ): int {
        $this->db->table('election_circumscriptions')->insert([
            'election_id' => $electionId,
            'canton_id' => $cantonId,
            'dignity_id' => $dignityId,
            'code' => $code,
            'name' => $name,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $circumscriptionId = (int)$this->db->insertID();

        foreach ($parishIds as $parishId) {
            $this->db->table('election_circumscription_parishes')->insert([
                'election_id' => $electionId,
                'canton_id' => $cantonId,
                'circumscription_id' => $circumscriptionId,
                'parish_id' => $parishId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $exists = $this->db->table('election_seats')
            ->where('election_id', $electionId)
            ->where('canton_id', $cantonId)
            ->where('dignity_id', $dignityId)
            ->where('circumscription_id', $circumscriptionId)
            ->countAllResults();
        if ($exists === 0 && $seats > 0) {
            $this->db->table('election_seats')->insert([
                'election_id' => $electionId,
                'canton_id' => $cantonId,
                'dignity_id' => $dignityId,
                'circumscription_id' => $circumscriptionId,
                'seats' => $seats,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $circumscriptionId;
    }
}
